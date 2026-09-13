@echo off
setlocal enabledelayedexpansion

rem =====================================================
rem  Setup SIPANDHALU di PC baru (Laragon)
rem  Jalur B (default): restore data dari storage\backup\sipandhalu-backup.sql
rem  Jalur A:           kalau backup tidak ada -> migrate + seed dummy
rem =====================================================

cd /d "%~dp0\.."

set "MYSQL=mysql"
rem Urutan pencarian dump:
rem  1) backup privat dari PC lama (flashdisk)          - data asli
rem  2) dump dummy yang ikut di repo GitHub (database\backups)
set "BACKUP_SQL=storage\backup\sipandhalu-backup.sql"
set "BACKUP_SQL_GITHUB=database\backups\sipandhalu-dummy.sql"
if not exist "%BACKUP_SQL%" if exist "%BACKUP_SQL_GITHUB%" set "BACKUP_SQL=%BACKUP_SQL_GITHUB%"

echo ==========================================
echo  SETUP SIPANDHALU
echo  - restore : %BACKUP_SQL%
echo  - server  : http://localhost:8091
echo ==========================================
echo.

echo == 1/5 Cek .env ==
if not exist .env (
  copy .env.example .env >nul
  echo .env dibuat dari .env.example
) else (
  echo .env sudah ada
)

echo == 2/5 Cek dependensi Composer ==
if not exist vendor\autoload.php (
  composer install --no-interaction --optimize-autoloader
  if errorlevel 1 (
    echo GAGAL composer install. Pastikan composer sudah terinstall.
    pause
    exit /b 1
  )
) else (
  echo vendor sudah ada, dilewati
)

echo == 3/5 Cek MySQL menyala ==
!MYSQL! -u root -e "SELECT 1" >nul 2>nul
if errorlevel 1 (
  echo MySQL belum menyala. Buka Laragon lalu klik Start All,
  echo tunggu sampai MySQL hijau, lalu jalankan script ini lagi.
  pause
  exit /b 1
)

echo == 4/5 Database ==

:restore_try
set "PW="
set "PWPROMPT="
if not "%DB_ROOT_PASS%"=="" set "PW= -p"%DB_ROOT_PASS%""

if exist "%BACKUP_SQL%" (
  echo Restore database dari %BACKUP_SQL% ...
  !MYSQL! -u root %PW% < "%BACKUP_SQL%"
  if errorlevel 1 goto restore_failed
  echo Restore selesai. Data dari PC lama berhasil dipulihkan.
) else (
  echo Backup tidak ditemukan - lanjut setup baru (migrate + seed dummy)
  !MYSQL! -u root %PW% -e "CREATE DATABASE IF NOT EXISTS sipandhalu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE IF NOT EXISTS sipandhalu_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  if errorlevel 1 goto restore_failed
  echo -- Migrasi --
  php database\migrate.php
  if errorlevel 1 goto migrate_failed
  echo -- Seed data dummy --
  php database\seeds\seed_tahap1.php
  php database\seeds\002_wilayah_seed.php
  php database\seeds\003_sls_pdf_seed.php
  php database\seeds\004_dummy_seed.php
  if errorlevel 1 goto seed_failed
)
goto langkah5

:restore_failed
echo.
echo GAGAL akses MySQL. Kemungkinan root MySQL di PC ini punya password.
set /p DB_ROOT_PASS="Password root MySQL di PC ini (kosongkan utk tanpa password): "
set "PW= -p"%DB_ROOT_PASS%""
!MYSQL! -u root -p"%DB_ROOT_PASS%" -e "SELECT 1" >nul 2>nul
if errorlevel 1 (
  echo Password salah. Ulangi atau perbaiki manual di .env lalu jalankan ulang.
  pause
  exit /b 1
)
echo Password diterima, coba restore lagi ...
goto restore_try

:migrate_failed
echo GAGAL migrasi. Cek pesan error di atas.
pause
exit /b 1

:seed_failed
echo GAGAL seed. Cek pesan error di atas.
pause
exit /b 1

:langkah5
echo == 5/5 Jalan ==
echo.
echo Mulai server ...
echo Buka di browser : http://localhost:8091/login
echo Ctrl+C untuk menghentikan server.
echo.
php -S localhost:8091 -t public