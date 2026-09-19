#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."
PROJECT_ROOT="$(pwd)"

DB_ROOT_USER="${DB_ROOT_USER:-root}"
DB_ROOT_PASS="${DB_ROOT_PASS:-}"

if [ ! -f .env ]; then
  cp .env.example .env
  echo ".env dibuat dari .env.example"
fi

echo "== Install dependensi Composer =="
composer install --no-interaction --optimize-autoloader

echo "== Buat database =="
mysql -u"$DB_ROOT_USER" ${DB_ROOT_PASS:+-p"$DB_ROOT_PASS"} -e \
  "CREATE DATABASE IF NOT EXISTS sipandhalu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; \
   CREATE DATABASE IF NOT EXISTS sipandhalu_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "== Migrasi database =="
php database/migrate.php "${@:-}"

echo "== Seed data dummy =="
php database/seeds/seed_tahap1.php
php database/seeds/002_wilayah_seed.php
php database/seeds/003_sls_pdf_seed.php
php database/seeds/004_dummy_seed.php
php database/seeds/005_jadwal_pengawas_seed.php

echo "== Setup SIPANDHALU selesai =="
