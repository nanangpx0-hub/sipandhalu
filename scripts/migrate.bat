@echo off
cd /d "%~dp0\.."
php database\migrate.php %*
