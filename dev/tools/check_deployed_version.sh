#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
FILE="$ROOT_DIR/pub/static/deployed_version.txt"

if [[ ! -f "$FILE" ]]; then
  echo "[ERROR] Missing deployed_version.txt at pub/static/"
  exit 1
fi

if [[ ! -r "$FILE" ]]; then
  echo "[ERROR] deployed_version.txt is not readable"
  exit 1
fi

VALUE="$(cat "$FILE" | tr -d '\n' | sed 's/[^0-9]//g')"
if [[ -z "$VALUE" ]]; then
  echo "[ERROR] deployed_version.txt is empty or invalid"
  exit 1
fi

echo "[OK] deployed_version.txt present and readable (value=$VALUE)"
exit 0
