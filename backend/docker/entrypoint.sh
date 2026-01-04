#!/bin/bash
set -e

# Ustawienie katalogu cache composer poza volume (w /tmp)
export COMPOSER_CACHE_DIR=/tmp/composer-cache
mkdir -p $COMPOSER_CACHE_DIR

# Instalacja zależności composer (jeśli vendor nie istnieje lub jest pusty)
if [ ! -d "vendor" ] || [ ! "$(ls -A vendor 2>/dev/null)" ]; then
  echo "Instalowanie zależności composer..."
  composer install --optimize-autoloader --no-interaction --prefer-dist
  # Ustaw uprawnienia po instalacji
  chown -R www-data:www-data vendor || true
  chmod -R 755 vendor || true
fi

# Funkcja sprawdzająca dostępność bazy danych
wait_for_db() {
  echo "Czekam na połączenie z bazą danych..."
  until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    sleep 2
  done
  echo "Baza danych jest dostępna!"
}

# Funkcja sprawdzająca dostępność RabbitMQ
wait_for_rabbitmq() {
  echo "Czekam na połączenie z RabbitMQ..."
  until nc -z rabbitmq 5672 2>/dev/null; do
    sleep 2
  done
  echo "RabbitMQ jest dostępny!"
}

# Wykonaj migracje tylko dla php-fpm (nie dla workera)
if [ "$1" = "php-fpm" ]; then
  wait_for_db
  php bin/console doctrine:migrations:migrate --no-interaction || true
fi

# Sprawdź czy to worker (messenger:consume)
if [ "$1" = "php" ] && [ "$2" = "bin/console" ] && [ "$3" = "messenger:consume" ]; then
  wait_for_rabbitmq
fi

# Wykonaj oryginalne polecenie
exec "$@"
