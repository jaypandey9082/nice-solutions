#!/bin/bash
set -euo pipefail
script_dir="$(cd "$(dirname "$0")" && pwd)"
if [[ -n "${NICE_PHP_BIN:-}" ]]; then
  php_bin="$NICE_PHP_BIN"
else
  php_bin=""
  for candidate in "$HOME/Library/Application Support/Local/lightning-services"/php-*/bin/darwin-*/bin/php; do
    [[ -x "$candidate" ]] && php_bin="$candidate"
  done
fi
if [[ -z "$php_bin" || ! -x "$php_bin" ]]; then
  echo 'Local PHP not found. Set NICE_PHP_BIN to a PHP 8.2+ binary.' >&2
  exit 1
fi
exec "$php_bin" "$script_dir/runtime.php" "$@"
