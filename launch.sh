#!/usr/bin/env bash
# DevCoffee - demarrage local (macOS / MAMP)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
MYSQL_BIN="/Applications/MAMP/Library/bin/mysql80/bin/mysql"

echo
echo "========================================"
echo "      DEVCOFFEE - DEMARRAGE LOCAL"
echo "========================================"
echo

command -v php >/dev/null || { echo "ERREUR : PHP introuvable dans le PATH."; exit 1; }

if [ ! -f "$ROOT/backend/.env" ]; then
  echo "ERREUR : backend/.env absent (copie backend/.env.example)."
  exit 1
fi

if [ ! -f "$ROOT/backend/vendor/autoload.php" ]; then
  echo "Installation des dependances PHP..."
  (cd "$ROOT/backend" && composer install)
fi

if [ ! -f "$ROOT/frontend/node_modules/vite/bin/vite.js" ]; then
  echo "Installation des dependances frontend..."
  if command -v bun >/dev/null; then (cd "$ROOT/frontend" && bun install)
  else (cd "$ROOT/frontend" && npm install); fi
fi

# Import du schema (idempotent : CREATE IF NOT EXISTS + seed seulement si vide)
if [ -x "$MYSQL_BIN" ]; then
  set -a; source "$ROOT/backend/.env"; set +a
  if MYSQL_PWD="$DB_PASSWORD" "$MYSQL_BIN" -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" < "$ROOT/backend/schema.sql"; then
    echo "Base $DB_NAME prete."
  else
    echo "ATTENTION : MySQL injoignable sur $DB_HOST:$DB_PORT. Demarre MAMP."
  fi
fi

cleanup() {
  echo
  echo "Arret des serveurs..."
  kill "$API_PID" "$FRONT_PID" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

echo "[1/2] API PHP sur http://127.0.0.1:8000 ..."
(cd "$ROOT/backend" && php -S 127.0.0.1:8000 -t public public/index.php) &
API_PID=$!

echo "[2/2] Interface Vue sur http://127.0.0.1:5173 ..."
if command -v bun >/dev/null; then
  (cd "$ROOT/frontend" && bun run dev -- --host 127.0.0.1 --port 5173 --strictPort) &
else
  (cd "$ROOT/frontend" && node node_modules/vite/bin/vite.js --host 127.0.0.1 --port 5173 --strictPort) &
fi
FRONT_PID=$!

sleep 3
echo
echo "Interface : http://127.0.0.1:5173"
echo "API       : http://127.0.0.1:8000/api/drinks"
echo "Ctrl+C pour tout arreter."
open "http://127.0.0.1:5173" 2>/dev/null || true

wait
