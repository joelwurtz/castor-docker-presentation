#!/usr/bin/env bash
# Usage: ./step.sh <n>   shows what step n changes, then applies it
#        ./step.sh list
set -euo pipefail
cd "$(dirname "$0")"

if [[ "${1:-list}" == list ]]; then
    ls steps
    exit 0
fi

step=$(ls -d steps/"$(printf '%02d' "$1")"-* 2>/dev/null | head -1)
[[ -n "$step" ]] || { echo "Pas d'étape $1"; exit 1; }

if [[ "$1" == 0 ]]; then
    rm -f Dockerfile
fi

echo -e "\033[1;33m▶ $(basename "$step")\033[0m"
for file in "$step"/*; do
    target=$(basename "$file")
    if [[ -f "$target" ]]; then
        diff -u --color=always --label "$target" --label "$target" "$target" "$file" || true
    else
        echo -e "\033[1;32m+ nouveau fichier $target\033[0m"
        sed 's/^/  /' "$file"
    fi
    cp "$file" "$target"
done
