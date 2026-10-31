#!/usr/bin/env bash
# Thin wrapper kept for convenience: the real logic is in scripts/fetch-photos.php
# (PHP exists wherever Laravel runs, on macOS, Linux and Windows alike).
#   bash scripts/fetch-photos.sh [--only=homepage] [--dry-run] [--force]
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."
exec php scripts/fetch-photos.php "$@"
