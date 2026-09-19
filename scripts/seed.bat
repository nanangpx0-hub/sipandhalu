@echo off
cd /d "%~dp0\.."
php database\seeds\seed_tahap1.php
php database\seeds\002_wilayah_seed.php
php database\seeds\003_sls_pdf_seed.php
php database\seeds\004_dummy_seed.php
php database\seeds\005_jadwal_pengawas_seed.php
