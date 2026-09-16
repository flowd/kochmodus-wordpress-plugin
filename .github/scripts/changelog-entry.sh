#!/usr/bin/env bash
#
# Prints the "= VERSION =" entry of the "== Changelog ==" section of readme.txt
# without its heading and without surrounding blank lines. Prints nothing if
# there is no entry. Only the Changelog section is searched: "== Upgrade
# Notice ==" repeats the same version headings with different content.
# Used for the SVN trunk commit message, the GitHub release notes and the
# changelog check in check-version.sh.
#
# Usage: .github/scripts/changelog-entry.sh <version> [path/to/readme.txt]
set -euo pipefail

version="${1:?usage: changelog-entry.sh <version> [readme.txt]}"
readme="${2:-$(dirname "${BASH_SOURCE[0]}")/../../readme.txt}"

awk -v version="${version}" '
    {
        line = $0
        sub(/[[:space:]]+$/, "", line)
    }
    line ~ /^== / { if (found) exit; in_changelog = (line == "== Changelog ==") }
    !in_changelog { next }
    !found && line == "= " version " =" { found = 1; next }
    found && line ~ /^= .* =$/ { exit }
    found { lines[++count] = line }
    END {
        first = 1
        last = count
        while (first <= last && lines[first] == "") first++
        while (last >= first && lines[last] == "") last--
        for (i = first; i <= last; i++) print lines[i]
    }
' "${readme}"
