#!/usr/bin/env bash
#
# Prints the "= VERSION =" changelog entry of readme.txt without its heading
# and without surrounding blank lines. Prints nothing if there is no entry.
# Used for the SVN trunk commit message and the GitHub release notes.
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
    !found && line == "= " version " =" { found = 1; next }
    found && (line ~ /^= .* =$/ || line ~ /^== /) { exit }
    found { lines[++count] = line }
    END {
        first = 1
        last = count
        while (first <= last && lines[first] == "") first++
        while (last >= first && lines[last] == "") last--
        for (i = first; i <= last; i++) print lines[i]
    }
' "${readme}"
