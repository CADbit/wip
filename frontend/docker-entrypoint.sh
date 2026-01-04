#!/bin/sh
set -e

# Sprawdź czy node_modules istnieje, jeśli nie to zainstaluj
if [ ! -d "node_modules" ] || [ ! "$(ls -A node_modules)" ]; then
  echo "Instalowanie zależności..."
  npm install
fi

# Wykonaj oryginalne polecenie
exec "$@"

