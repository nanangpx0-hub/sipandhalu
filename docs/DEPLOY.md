# Setup Cepat Setelah Clone/Pull

## Prasyarat

- PHP 8.2+
- MySQL 8+ (Laragon/Laragon Plus atau MySQL lokal)
- Composer tersedia di PATH
- Git

## 1. Ambil kode

```bash
git clone https://github.com/nanangpx0-hub/sipandhalu.git
cd sipandhalu
```

Atau jika sudah ada repositori lokal:

```bash
git pull origin main
```

## 2. Jalankan installer otomatis

### Linux/macOS

```bash
bash scripts/setup.sh
```

### Windows (Command Prompt)

```cmd
scripts\setup.bat
```

Installer akan:

1. Menjalankan `composer install`
2. Membuat database `sipandhalu` dan `sipandhalu_test`
3. Menjalankan `php database/migrate.php`
4. Menjalankan seed data dummy 001–004

### Variabel lingkungan opsional installer

```bash
DB_ROOT_USER=root DB_ROOT_PASS=secret bash scripts/setup.sh
```

## 3. Sesuaikan kredensial aplikasi

Salin dan edit file environment:

```bash
cp .env.example .env
```

Edit `.env` sesuai MySQL lokal, contoh untuk Laragon:

```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=sipandhalu
DB_USER=root
DB_PASS=
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8091
```

Pastikan `.env` tidak di-commit ke Git.

## 4. Jalankan aplikasi

Buka **http://sipandhalu.test**.

> Jika `http://sipandhalu.test` belum bisa dibuka, tambahkan baris berikut ke file `C:\Windows\System32\drivers\etc\hosts` (dibuka sebagai Administrator):
>
> ```text
> 127.0.0.1 sipandhalu.test
> ```
>
> Atau jalankan aplikasi sementara melalui:
>
> ```bash
> php -S localhost:8091 -t public
> ```

## Login Demo

| Email | Password | Peran |
|---|---|---|
| `admin@bpsjember.go.id` | `Jember3509` | ADMIN |
| `pcl.demo@bpsjember.go.id` | `Jember3509` | PCL |
| `pml.demo@bpsjember.go.id` | `Jember3509` | PML |
| `operator.demo@bpsjember.go.id` | `Jember3509` | OPERATOR |
| `viewer.demo@bpsjember.go.id` | `Jember3509` | VIEWER |

Pengolah asli menggunakan password `Jember3509` dan wajib ganti saat login pertama.

## Verifikasi Cepat

```bash
php vendor/phpunit/phpunit/phpunit --testdox
php tests/smoke_tahap1.php
```

Kedua perintah harusnya lulus setelah setup selesai.
