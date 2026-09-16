#!/usr/bin/env bash
#
# Deploys the built plugin from the working tree to the WordPress.org SVN
# repository, following the WordPress.org SVN guide:
# https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
#
#   1. sync the working tree (minus .distignore) into trunk/    -> commit
#   2. sync the wp.org assets (icons, screenshots) into assets/ -> commit
#   3. build an installable zip from trunk/
#   4. copy trunk to tags/<VERSION>                             -> commit
#
# Everything before the tag is idempotent, so a failed run can be retried.
# The tag is created last and marks the version as released. If tags/<VERSION>
# already exists, the script resumes instead of deploying again: it verifies
# that the tag matches the working tree and only rebuilds the zip, so the steps
# after SVN (GitHub release) can be retried. A tag whose content differs from
# the working tree is an error: bump the version and release again.
#
# Git is the development repository, SVN only receives releases. The SVN
# commit messages are defined in the "Commit messages" block below and are
# independent of the Conventional Commits used in git (.gitmessage).
#
# Environment:
#   SLUG           wp.org plugin slug                                    (required)
#   VERSION        MAJOR.MINOR.PATCH, becomes tags/<VERSION>             (required)
#   SVN_USERNAME   wp.org username                     (required unless DRY_RUN=1)
#   SVN_PASSWORD   wp.org SVN password                 (required unless DRY_RUN=1)
#   DRY_RUN        1: sync, show what would be committed, commit nothing (default 0)
#   WORKSPACE      plugin source directory         (default: $GITHUB_WORKSPACE or cwd)
#   ASSETS_DIR     wp.org assets directory inside WORKSPACE       (default: assets)
#   SVN_URL        (default: https://plugins.svn.wordpress.org/<SLUG>)
#   SVN_DIR        SVN working copy location, outside WORKSPACE (default: temp directory)
#   ZIP_PATH       output zip, empty disables   (default: <WORKSPACE>/dist/<SLUG>.zip)
#   SOURCE_URL     GitHub release URL, appended to the trunk commit message (optional)
#
# Dry run against the live repository (read-only, no credentials needed):
#   SLUG=flowd-kochmodus VERSION=1.0.0 DRY_RUN=1 bash .github/scripts/svn-deploy.sh
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

SLUG="${SLUG:?SLUG is required}"
VERSION="${VERSION:?VERSION is required}"
SVN_USERNAME="${SVN_USERNAME:-}"
SVN_PASSWORD="${SVN_PASSWORD:-}"
DRY_RUN="${DRY_RUN:-0}"
WORKSPACE="${WORKSPACE:-${GITHUB_WORKSPACE:-$PWD}}"
ASSETS_DIR="${ASSETS_DIR:-assets}"
SVN_URL="${SVN_URL:-https://plugins.svn.wordpress.org/${SLUG}}"
SVN_DIR="${SVN_DIR:-$(mktemp -d)}"
ZIP_PATH="${ZIP_PATH-${WORKSPACE}/dist/${SLUG}.zip}"
SOURCE_URL="${SOURCE_URL:-}"

# ---------------------------------------------------------------------------
# Commit messages (WordPress.org SVN only; git uses .gitmessage)
# ---------------------------------------------------------------------------

# Trunk update: subject, the readme.txt changelog entry, link to the release.
trunk_message() {
    echo "Update trunk to version ${VERSION}"
    echo
    echo "${CHANGELOG}"
    if [[ -n "${SOURCE_URL}" ]]; then
        echo
        echo "Source: ${SOURCE_URL}"
    fi
}

# wp.org assets (icons, screenshots, banners).
assets_message() {
    echo "Update assets for version ${VERSION}"
}

# Tag creation (svn copy trunk -> tags/<VERSION>).
tag_message() {
    echo "Tagging version ${VERSION}"
}

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
    local revision
    revision="$(sed -nE 's/^Committed revision ([0-9]+)\.$/\1/p' <<< "$1")"
    echo "${revision:-?}"
}

# sync_plugin <wc-path> [rsync options...]: mirrors the working tree minus
# .distignore into a working copy directory, keeping its .svn metadata.
sync_plugin() {
    local path="$1"
    shift
    rsync -rc --delete --delete-excluded \
        --exclude='/.svn' \
        --exclude-from="${WORKSPACE}/.distignore" \
        "$@" "${WORKSPACE}/" "${path}/"
}

# register_changes <wc-path>: schedule new files for addition and missing files
# for deletion so that "svn status" reflects the synced tree.
register_changes() {
    local path="$1" missing

    svn_cmd add -q --force --no-ignore "${path}"

    svn_cmd status "${path}" | awk '/^!/ { sub(/^![[:space:]]+/, ""); print }' | while IFS= read -r missing; do
        svn_cmd rm -q "${missing}@"
    done
}

# set_mime_types <wc-path>: without svn:mime-type wp.org serves images as
# downloads instead of displaying them.
set_mime_types() {
    local path="$1" pair extension type

    for pair in png:image/png jpg:image/jpeg jpeg:image/jpeg gif:image/gif svg:image/svg+xml; do
        extension="${pair%%:*}"
        type="${pair#*:}"
        find "${path}" -type f -iname "*.${extension}" -print0 \
            | xargs -0 -r svn propset -q svn:mime-type "${type}"
    done
}

# commit_if_changed <label> <message-function> <wc-path>
commit_if_changed() {
    local label="$1" message_fn="$2" path="$3"
    local changes message_file output revision

    changes="$(svn_cmd status -q "${path}")"
    if [[ -z "${changes}" ]]; then
        echo "${label}: unchanged, nothing to commit"
        SUMMARY+=("${label}: unchanged")
        return 0
    fi

    message_file="$(mktemp)"
    "${message_fn}" > "${message_file}"

    echo "${label}: changes"
    while IFS= read -r line; do echo "    ${line}"; done <<< "${changes}"
    echo "${label}: commit message"
    sed 's/^/    | /' "${message_file}"

    if [[ "${DRY_RUN}" == 1 ]]; then
        echo "${label}: dry run, not committing"
        SUMMARY+=("${label}: dry run, $(wc -l <<< "${changes}" | tr -d ' ') change(s) not committed")
        return 0
    fi

    output="$(svn_auth commit "${path}" -F "${message_file}")"
    revision="$(committed_revision "${output}")"
    echo "${label}: committed r${revision}"
    SUMMARY+=("${label}: committed r${revision}")
}

# build_zip <wc-path>: installable zip with the top-level folder <SLUG>/, as
# wp.org ships it. No-op when ZIP_PATH is empty.
build_zip() {
    local source="$1" stage

    [[ -n "${ZIP_PATH}" ]] || return 0

    log "Building ${ZIP_PATH} from ${source}/"
    stage="$(mktemp -d)"
    ln -s "${source}" "${stage}/${SLUG}"
    mkdir -p "$(dirname "${ZIP_PATH}")"
    (cd "${stage}" && zip -rq "${stage}/${SLUG}.zip" "${SLUG}" -x '*/.svn/*')
    mv -f "${stage}/${SLUG}.zip" "${ZIP_PATH}"
    echo "zip: $(du -h "${ZIP_PATH}" | cut -f1)"
    SUMMARY+=("zip: ${ZIP_PATH}")
}

# ---------------------------------------------------------------------------
# Preflight
# ---------------------------------------------------------------------------

log "Preflight: ${SLUG} ${VERSION}$([[ "${DRY_RUN}" == 1 ]] && echo ' (dry run)')"

[[ "${VERSION}" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] || die "VERSION '${VERSION}' is not MAJOR.MINOR.PATCH"
[[ -d "${WORKSPACE}" ]] || die "WORKSPACE '${WORKSPACE}' is not a directory"
WORKSPACE="$(cd "${WORKSPACE}" && pwd -P)"
[[ -f "${WORKSPACE}/.distignore" ]] || die "${WORKSPACE}/.distignore not found"
[[ -f "${WORKSPACE}/readme.txt" ]] || die "${WORKSPACE}/readme.txt not found"

# Resolve both to physical absolute paths before comparing, otherwise a
# relative SVN_DIR (e.g. tmp/svn) inside the source tree would slip through
# and rsync would copy the checkout into its own trunk.
#
# physical_path <path>: absolute path with symlinks and ".." resolved as far
# as the path exists; components that do not exist yet are appended as given.
physical_path() {
    local path="$1" rest=""
    [[ "${path}" == /* ]] || path="$(pwd -P)/${path}"
    while [[ ! -d "${path}" ]]; do
        rest="/$(basename "${path}")${rest}"
        path="$(dirname "${path}")"
    done
    echo "$(cd "${path}" && pwd -P)${rest}"
}
check_svn_dir() {
    case "${SVN_DIR}/" in
        "${WORKSPACE}"/*) die "SVN_DIR '${SVN_DIR}' must not be inside WORKSPACE '${WORKSPACE}'" ;;
    esac
    case "${WORKSPACE}/" in
        "${SVN_DIR}"/*) die "WORKSPACE '${WORKSPACE}' must not be inside SVN_DIR '${SVN_DIR}'" ;;
    esac
}
SVN_DIR="$(physical_path "${SVN_DIR}")"
check_svn_dir
mkdir -p "${SVN_DIR}" || die "cannot create SVN_DIR '${SVN_DIR}'"
SVN_DIR="$(cd "${SVN_DIR}" && pwd -P)"
check_svn_dir
case "${ZIP_PATH}" in
    "" | /*) ;;
    *) ZIP_PATH="${PWD}/${ZIP_PATH}" ;;
esac

for tool in svn rsync; do
    command -v "${tool}" > /dev/null || die "'${tool}' is not installed"
done
[[ -z "${ZIP_PATH}" ]] || command -v zip > /dev/null || die "'zip' is not installed"

if [[ "${DRY_RUN}" != 1 && ( -z "${SVN_USERNAME}" || -z "${SVN_PASSWORD}" ) ]]; then
    die "SVN_USERNAME and SVN_PASSWORD are required (or set DRY_RUN=1)"
fi

CHANGELOG="$(bash "${SCRIPT_DIR}/changelog-entry.sh" "${VERSION}" "${WORKSPACE}/readme.txt")"
[[ -n "${CHANGELOG}" ]] || die "readme.txt has no changelog entry '= ${VERSION} ='"

svn_cmd info "${SVN_URL}" > /dev/null 2>&1 || die "cannot reach ${SVN_URL}"
RESUME=0
if svn_cmd info "${SVN_URL}/tags/${VERSION}" > /dev/null 2>&1; then
    RESUME=1
    echo "tags/${VERSION} already exists in ${SVN_URL}: resuming, SVN will not be changed"
fi

SUMMARY=()

# ---------------------------------------------------------------------------
# Checkout (root with immediate children only, the needed directories in full)
# ---------------------------------------------------------------------------

log "Checking out ${SVN_URL} into ${SVN_DIR}"
svn_cmd checkout -q --depth immediates "${SVN_URL}" "${SVN_DIR}"
if [[ "${RESUME}" == 1 ]]; then
    svn_cmd update -q --set-depth infinity "${SVN_DIR}/tags/${VERSION}"
else
    svn_cmd update -q --set-depth infinity "${SVN_DIR}/trunk" "${SVN_DIR}/assets"
fi
cd "${SVN_DIR}"

# ---------------------------------------------------------------------------
# Resume: tags/<VERSION> exists, verify it and rebuild the zip only
# ---------------------------------------------------------------------------

if [[ "${RESUME}" == 1 ]]; then
    log "Verifying tags/${VERSION} against the working tree"
    # Itemized lines starting with "." are files that would not be transferred
    # (only attributes such as the modification time differ); everything else
    # (">f" changed or new file, "cd" new directory, "*deleting") is a difference.
    differences="$(sync_plugin "tags/${VERSION}" --dry-run --itemize-changes | awk '!/^\./')"
    if [[ -n "${differences}" ]]; then
        echo "tags/${VERSION}: differs from the working tree"
        while IFS= read -r line; do echo "    ${line}"; done <<< "${differences}"
        die "tags/${VERSION} in ${SVN_URL} does not match this source: version ${VERSION} is already released with different content. Bump the version and push a new tag."
    fi
    echo "tags/${VERSION}: matches the working tree"
    SUMMARY+=("trunk: skipped, tags/${VERSION} already released")
    SUMMARY+=("assets: skipped, tags/${VERSION} already released")
    SUMMARY+=("tag: tags/${VERSION} exists and matches the working tree")

    build_zip "${SVN_DIR}/tags/${VERSION}"
fi

# ---------------------------------------------------------------------------
# 1. trunk
# ---------------------------------------------------------------------------

if [[ "${RESUME}" != 1 ]]; then
    log "Syncing working tree into trunk/"
    sync_plugin trunk
    register_changes trunk
    commit_if_changed trunk trunk_message trunk
fi

# ---------------------------------------------------------------------------
# 2. assets
# ---------------------------------------------------------------------------

if [[ "${RESUME}" != 1 ]]; then
    if [[ -d "${WORKSPACE}/${ASSETS_DIR}" ]]; then
        log "Syncing ${ASSETS_DIR}/ into assets/"
        rsync -rc --delete --exclude='.*' "${WORKSPACE}/${ASSETS_DIR}/" assets/
        register_changes assets
        set_mime_types assets
        commit_if_changed assets assets_message assets
    else
        log "No ${ASSETS_DIR}/ directory, skipping wp.org assets"
        SUMMARY+=("assets: skipped, no ${ASSETS_DIR}/ directory")
    fi
fi

# ---------------------------------------------------------------------------
# 3. installable zip (from trunk, before the irreversible tag)
# ---------------------------------------------------------------------------

if [[ "${RESUME}" != 1 ]]; then
    build_zip "${SVN_DIR}/trunk"
fi

# ---------------------------------------------------------------------------
# 4. tags/<VERSION> (server-side copy of trunk HEAD, marks the release as done)
# ---------------------------------------------------------------------------

if [[ "${RESUME}" != 1 ]]; then
    log "Tagging trunk as tags/${VERSION}"
    message_file="$(mktemp)"
    tag_message > "${message_file}"
    echo "tag: commit message"
    sed 's/^/    | /' "${message_file}"

    if [[ "${DRY_RUN}" == 1 ]]; then
        echo "tag: dry run, would run: svn copy ${SVN_URL}/trunk ${SVN_URL}/tags/${VERSION}"
        SUMMARY+=("tag: dry run, tags/${VERSION} not created")
    else
        output="$(svn_auth copy "${SVN_URL}/trunk" "${SVN_URL}/tags/${VERSION}" -F "${message_file}")"
        revision="$(committed_revision "${output}")"
        echo "tag: created tags/${VERSION} r${revision}"
        SUMMARY+=("tag: created tags/${VERSION} r${revision}")
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
        echo "## WordPress.org SVN: ${SLUG} ${VERSION}$([[ "${DRY_RUN}" == 1 ]] && echo ' (dry run)')"
        echo
        for line in ${SUMMARY[@]+"${SUMMARY[@]}"}; do
            echo "- ${line}"
        done
    } >> "${GITHUB_STEP_SUMMARY}"
fi
