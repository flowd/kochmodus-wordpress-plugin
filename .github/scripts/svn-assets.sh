#!/usr/bin/env bash
#
# Syncs the WordPress.org page assets (banners, icons, screenshots, blueprints)
# from the working tree into the SVN "assets" directory:
#
#   1. validate the assets against the wp.org import rules
#   2. sync <ASSETS_DIR>/ into assets/                          -> commit
#
# This is separate from svn-deploy.sh on purpose: page assets are not part of a
# plugin release. They live only in SVN /assets, never in trunk or a tag, and
# wp.org re-imports them on any commit to the plugin's SVN repository. Changing
# a banner therefore needs no version bump.
#
# The validation mirrors the wp.org importer
# (plugin-directory/cli/class-import.php), which skips assets silently:
#
#     $asset_limits = array(
#         'screenshot' => 10 * MB_IN_BYTES,
#         'banner'     =>  4 * MB_IN_BYTES,
#         'icon'       =>  1 * MB_IN_BYTES,
#         'blueprint'  => 100 * KB_IN_BYTES,
#     );
#     // Don't import zero-byte or oversize assets.
#     if ( ! $asset['filesize'] || $asset['filesize'] > $asset_limits[ $type ] ) {
#         continue;
#     }
#
# A skipped asset stays reachable over its ps.w.org URL, so the only visible
# symptom is a banner or icon missing from the plugin page. Version 1.0.2
# shipped a 5.64 MB banner-1544x500.gif that way: wp.org dropped it, built no
# srcset and served the 772x250 banner to desktop visitors as well. Oversize or
# zero-byte files are a hard error here so that never reaches SVN again.
#
# Environment:
#   SLUG           wp.org plugin slug                                    (required)
#   SVN_USERNAME   wp.org username                     (required unless DRY_RUN=1)
#   SVN_PASSWORD   wp.org SVN password                 (required unless DRY_RUN=1)
#   DRY_RUN        1: validate, sync, show the diff, commit nothing  (default 0)
#   WORKSPACE      plugin source directory         (default: $GITHUB_WORKSPACE or cwd)
#   ASSETS_DIR     wp.org assets directory inside WORKSPACE       (default: assets)
#   SVN_URL        (default: https://plugins.svn.wordpress.org/<SLUG>)
#   SVN_DIR        SVN working copy location, outside WORKSPACE (default: temp directory)
#   MESSAGE        commit message subject     (default: "Update plugin page assets")
#
# Dry run against the live repository (read-only, no credentials needed):
#   SLUG=flowd-kochmodus DRY_RUN=1 bash .github/scripts/svn-assets.sh
set -euo pipefail

SLUG="${SLUG:?SLUG is required}"
SVN_USERNAME="${SVN_USERNAME:-}"
SVN_PASSWORD="${SVN_PASSWORD:-}"
DRY_RUN="${DRY_RUN:-0}"
WORKSPACE="${WORKSPACE:-${GITHUB_WORKSPACE:-$PWD}}"
ASSETS_DIR="${ASSETS_DIR:-assets}"
SVN_URL="${SVN_URL:-https://plugins.svn.wordpress.org/${SLUG}}"
SVN_DIR="${SVN_DIR:-$(mktemp -d)}"
MESSAGE="${MESSAGE:-Update plugin page assets}"

# ---------------------------------------------------------------------------
# wp.org import rules (see the class-import.php excerpt above)
# ---------------------------------------------------------------------------

# Maximum file size per asset type, in bytes.
limit_for() {
    case "$1" in
        screenshot) echo $((10 * 1024 * 1024)) ;;
        banner)     echo $(( 4 * 1024 * 1024)) ;;
        icon)       echo $(( 1 * 1024 * 1024)) ;;
        blueprint)  echo $((     100 * 1024)) ;;
    esac
}

# Filenames wp.org recognises, e.g. banner-1544x500.gif, icon.svg,
# screenshot-2.png, banner-772x250-rtl-de_DE.jpg.
ASSET_PATTERN='^(screenshot|banner|icon)(-[0-9]+([^0-9][0-9]+)?(-rtl)?(-[a-z]{2,3}(_[A-Z]{2})?(_[a-z0-9]+)?)?\.(png|jpg|jpeg|gif)|\.svg)$'

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

log() { printf '\n==> %s\n' "$*"; }
die() { printf 'ERROR: %s\n' "$*" >&2; exit 1; }

# svn_cmd <subcommand> [args...]: svn without credentials (read-only operations).
svn_cmd() {
    svn "$@" --non-interactive --no-auth-cache
}

# svn_auth <subcommand> [args...]: svn with wp.org credentials (writes).
svn_auth() {
    svn "$@" --non-interactive --no-auth-cache --username "${SVN_USERNAME}" --password "${SVN_PASSWORD}"
}

# committed_revision <svn output>: extracts N from "Committed revision N."
committed_revision() {
    sed -nE 's/^Committed revision ([0-9]+)\.$/\1/p' <<< "$1"
}

# file_size <path>: size in bytes, portable between GNU and BSD stat.
file_size() {
    stat -c%s "$1" 2> /dev/null || stat -f%z "$1"
}

# human <bytes>
human() {
    awk -v b="$1" 'BEGIN { printf (b < 1048576 ? "%.0f KB" : "%.2f MB"), (b < 1048576 ? b / 1024 : b / 1048576) }'
}

# ---------------------------------------------------------------------------
# Preflight
# ---------------------------------------------------------------------------

log "Preflight: ${SLUG}$([[ "${DRY_RUN}" == 1 ]] && echo ' (dry run)')"

[[ -d "${WORKSPACE}" ]] || die "WORKSPACE '${WORKSPACE}' is not a directory"
WORKSPACE="$(cd "${WORKSPACE}" && pwd -P)"
SOURCE="${WORKSPACE}/${ASSETS_DIR}"
[[ -d "${SOURCE}" ]] || die "'${SOURCE}' not found, nothing to sync"

for tool in svn rsync; do
    command -v "${tool}" > /dev/null || die "'${tool}' is not installed"
done

if [[ "${DRY_RUN}" != 1 && ( -z "${SVN_USERNAME}" || -z "${SVN_PASSWORD}" ) ]]; then
    die "SVN_USERNAME and SVN_PASSWORD are required (or set DRY_RUN=1)"
fi

# ---------------------------------------------------------------------------
# 1. Validate against the wp.org import rules
# ---------------------------------------------------------------------------

log "Validating ${ASSETS_DIR}/ against the wp.org import limits"

errors=0
# Hidden files are excluded from the sync below, so they are not validated.
while IFS= read -r file; do
    name="$(basename "${file}")"
    size="$(file_size "${file}")"

    if [[ "${file}" == "${SOURCE}/blueprints/"* ]]; then
        type=blueprint
    elif [[ "${name}" =~ ^(screenshot|banner|icon) ]]; then
        type="${BASH_REMATCH[1]}"
        if ! [[ "${name}" =~ ${ASSET_PATTERN} ]]; then
            echo "  ERROR   ${name}: filename does not match the pattern wp.org accepts, it would be ignored"
            errors=$((errors + 1))
            continue
        fi
    else
        echo "  ignored ${name}: not a wp.org asset name, wp.org will skip it"
        continue
    fi

    limit="$(limit_for "${type}")"
    if (( size == 0 )); then
        echo "  ERROR   ${name}: zero bytes, wp.org would skip it"
        errors=$((errors + 1))
    elif (( size > limit )); then
        echo "  ERROR   ${name}: $(human "${size}") exceeds the ${type} limit of $(human "${limit}"), wp.org would skip it"
        errors=$((errors + 1))
    else
        printf '  ok      %-24s %9s of %s (%s)\n' "${name}" "$(human "${size}")" "$(human "${limit}")" "${type}"
    fi
done < <(find "${SOURCE}" -type f -not -name '.*' | sort)

(( errors == 0 )) || die "${errors} asset(s) would be skipped by wp.org. Fix them before syncing; a skipped asset is still reachable by URL but never appears on the plugin page."

# ---------------------------------------------------------------------------
# 2. Sync and commit
# ---------------------------------------------------------------------------

SUMMARY=()

svn_cmd info "${SVN_URL}" > /dev/null 2>&1 || die "cannot reach ${SVN_URL}"

log "Checking out ${SVN_URL}/assets into ${SVN_DIR}"
mkdir -p "${SVN_DIR}"
SVN_DIR="$(cd "${SVN_DIR}" && pwd -P)"
case "${SVN_DIR}/" in
    "${WORKSPACE}"/*) die "SVN_DIR '${SVN_DIR}' must not be inside WORKSPACE '${WORKSPACE}'" ;;
esac
svn_cmd checkout -q --depth immediates "${SVN_URL}" "${SVN_DIR}"
svn_cmd update -q --set-depth infinity "${SVN_DIR}/assets"
cd "${SVN_DIR}"

log "Syncing ${ASSETS_DIR}/ into assets/"
# Hidden files are excluded: wp.org forbids them and they are not assets.
rsync -rc --delete --exclude='.*' "${SOURCE}/" assets/

svn_cmd add -q --force --no-ignore assets
svn_cmd status assets | awk '/^!/ { sub(/^![[:space:]]+/, ""); print }' | while IFS= read -r missing; do
    svn_cmd rm -q "${missing}@"
done

# Without svn:mime-type wp.org serves images as downloads instead of displaying them.
for pair in png:image/png jpg:image/jpeg jpeg:image/jpeg gif:image/gif svg:image/svg+xml; do
    find assets -type f -iname "*.${pair%%:*}" -print0 | xargs -0 -r svn propset -q svn:mime-type "${pair#*:}"
done

changes="$(svn_cmd status -q assets)"
if [[ -z "${changes}" ]]; then
    echo "assets: unchanged, nothing to commit"
    SUMMARY+=("assets: unchanged")
else
    echo "assets: changes"
    while IFS= read -r line; do echo "    ${line}"; done <<< "${changes}"
    echo "assets: commit message"
    echo "    | ${MESSAGE}"

    if [[ "${DRY_RUN}" == 1 ]]; then
        echo "assets: dry run, not committing"
        SUMMARY+=("assets: dry run, $(wc -l <<< "${changes}" | tr -d ' ') change(s) not committed")
    else
        revision="$(committed_revision "$(svn_auth commit assets -m "${MESSAGE}")")"
        echo "assets: committed r${revision:-?}"
        SUMMARY+=("assets: committed r${revision:-?}")
    fi
fi

# ---------------------------------------------------------------------------
# Summary
# ---------------------------------------------------------------------------

log "Summary"
for line in ${SUMMARY[@]+"${SUMMARY[@]}"}; do
    echo "  - ${line}"
done
echo "  - svn working copy: ${SVN_DIR}"

if [[ -n "${GITHUB_STEP_SUMMARY:-}" ]]; then
    {
        echo "## WordPress.org assets: ${SLUG}$([[ "${DRY_RUN}" == 1 ]] && echo ' (dry run)')"
        echo
        for line in ${SUMMARY[@]+"${SUMMARY[@]}"}; do
            echo "- ${line}"
        done
    } >> "${GITHUB_STEP_SUMMARY}"
fi
