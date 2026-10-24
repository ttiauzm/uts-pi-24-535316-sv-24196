#!/bin/sh
set -e
cd /app

# APP_KEY sebaiknya diberikan lewat environment (Docker Compose / GitHub Secrets).
# Kalau kosong, buat sekali untuk kemudahan demo dan beri peringatan.
if [ -z "$APP_KEY" ]; then
  echo "PERINGATAN: APP_KEY kosong. Membuat APP_KEY sementara; isi APP_KEY di .env untuk produksi." >&2
  APP_KEY="$(php artisan key:generate --show)"
  export APP_KEY
fi

# Tunggu database siap lalu jalankan migrasi (coba ulang maksimal 20x)
tries=0
until php artisan migrate --force; do
  tries=$((tries + 1))
  if [ "$tries" -ge 20 ]; then
    echo "Migrasi gagal setelah $tries percobaan, database tidak bisa dijangkau." >&2
    exit 1
  fi
  echo "Database belum siap, coba lagi dalam 3 detik... ($tries/20)"
  sleep 3
done

# Seed akun demo + contoh film (idempoten, aman dijalankan berulang)
if [ "${DB_SEED:-false}" = "true" ]; then
  php artisan db:seed --force
fi

exec "$@"
