#!/usr/bin/env bash
#
# Verifies that every place the plugin version is declared agrees, and that
# readme.txt has a changelog entry for it. Optionally checks against an
# expected version (the release tag without the leading "v").
#
# Usage:
#   .github/scripts/check-version.sh          # all declarations must agree
#   .github/scripts/check-version.sh 1.2.3    # ...and must equal 1.2.3
#
# Prints the version on success. Exits 1 and lists every mismatch otherwise.
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/../.."

PLUGIN_FILE="flowd-kochmodus.php"
README_FILE="readme.txt"
PACKAGE_FILE="package.json"
BLOCK_FILE="blocks/kochmodus-button/block.json"

# first_line <text>: the first line of a multi-line string (empty if none).
first_line() {
    printf '%s\n' "${1%%$'\n'*}"
}

header="$(first_line "$(sed -nE 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]+([^[:space:]]+).*$/\1/p' "$PLUGIN_FILE")")"
constant="$(first_line "$(sed -nE "s/^define\('KOCHMODUS_VERSION',[[:space:]]*'([^']+)'\);.*$/\1/p" "$PLUGIN_FILE")")"
stable="$(first_line "$(sed -nE 's/^Stable tag:[[:space:]]*([^[:space:]]+).*$/\1/p' "$README_FILE")")"
package="$(first_line "$(sed -nE 's/^[[:space:]]*"version":[[:space:]]*"([^"]+)".*$/\1/p' "$PACKAGE_FILE")")"
block="$(first_line "$(sed -nE 's/^[[:space:]]*"version":[[:space:]]*"([^"]+)".*$/\1/p' "$BLOCK_FILE")")"

expected="${1:-$header}"
errors=()

if [[ ! "$expected" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    errors+=("'${expected}' is not a MAJOR.MINOR.PATCH version (see https://semver.org/)")
fi

# check <label> <actual>
check() {
    if [[ "$2" != "$expected" ]]; then
        errors+=("${1}: '${2:-<missing>}' (expected '${expected}')")
    fi
}

check "${PLUGIN_FILE} Version header"    "$header"
check "${PLUGIN_FILE} KOCHMODUS_VERSION" "$constant"
check "${README_FILE} Stable tag"        "$stable"
check "${PACKAGE_FILE} version"          "$package"
check "${BLOCK_FILE} version"            "$block"

if ! grep -qE "^= ${expected//./\\.} =[[:space:]]*$" "$README_FILE"; then
    errors+=("${README_FILE}: no changelog entry '= ${expected} ='")
fi

if (( ${#errors[@]} > 0 )); then
    echo "Version check failed:" >&2
    for error in ${errors[@]+"${errors[@]}"}; do
        echo "  - ${error}" >&2
    done
    exit 1
fi

echo "$expected"
