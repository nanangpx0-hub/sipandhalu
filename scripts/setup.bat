@echo off
setlocal enabledelayedexpansion

cd /d "%~dp0\.."

if "%DB_ROOT_USER%"=="" set DB_ROOT_USER=root
if "%DB_ROOT_PASS%"=="" set DB_ROOT_PASS=

echo == Install dependensi Composer ==
composer install --no-interaction --optimize-autoloader

echo == Buat database ==
if "%DB_ROOT_PASS%"=="" (
  mysql -u"%DB_ROOT_USER%" -e "CREATE DATABASE IF NOT EXISTS sipandhalu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE IF NOT EXISTS sipandhalu_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
) else (
  mysql -u"%DB_ROOT_USER%" -p"%DB_ROOT_PASS%" -e "CREATE DATABASE IF NOT EXISTS sipandhalu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE IF NOT EXISTS sipandhalu_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
)

echo == Migrasi database ==
php database\migrate.php %*

echo == Seed data dummy ==
php database\seeds\seed_tahap1.php
php database\seeds\002_wilayah_seed.php
php database\seeds\003_sls_pdf_seed.php
php database\seeds\004_dummy_seed.php

echo == Setup SIPANDHALU selesai ==
