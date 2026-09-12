# Dokumentasi Setup Domain & Cloudflare Tunnel SIPANDHALU

Dokumentasi ini menjelaskan konfigurasi dan langkah-langkah penghubung domain **sipandhalu.my.id** (dibeli di Domainesia) ke aplikasi lokal **SIPANDHALU** (berjalan di Laragon Windows) menggunakan **Cloudflare Zero Trust Tunnel**.

---

## 1. Informasi & Identitas Sistem

| Komponen | Detail |
| :--- | :--- |
| **Domain** | `https://sipandhalu.my.id` |
| **Registrar Domain** | [Domainesia](https://my.domainesia.com/) |
| **DNS & SSL / CDN** | [Cloudflare](https://dash.cloudflare.com/) |
| **Metode Penghubung** | Cloudflare Zero Trust Tunnel (`cloudflared`) |
| **Tunnel Name** | `sipandhalu-tunnel` |
| **Tunnel ID** | `36120c2f-09cc-48e4-95c0-f6754b719191` |
| **Local Web Server** | Laragon Apache (PHP 8.2) |
| **Origin URL** | `http://localhost:80` |

---

## 2. Pengaturan di Domainesia (Registrar)

Domain diarahkan menggunakan **Custom Nameserver** Cloudflare:
- **Nameserver 1**: `adi.ns.cloudflare.com`
- **Nameserver 2**: `elliott.ns.cloudflare.com`

*Langkah:*
1. Login ke [Domainesia](https://my.domainesia.com/) -> Menu **Domains** -> Pilih `sipandhalu.my.id`.
2. Masuk ke **Nameservers** -> Pilih **Use Custom Nameservers**.
3. Isi dengan 2 nameserver di atas dan simpan.

---

## 3. Pengaturan di Cloudflare Dashboard

### A. Cloudflare Zero Trust (Tunnels)
1. Buka [Cloudflare Dashboard](https://dash.cloudflare.com/) -> Menu **Zero Trust** -> **Networks** -> **Tunnels**.
2. Tunnel bernama **`sipandhalu-tunnel`** berstatus **Healthy**.
3. Pada tab **Hostname routes**, terdapat rute:
   - **Public hostname**: `sipandhalu.my.id`
   - **Service Type**: `HTTP`
   - **URL**: `localhost:80`
   - **HTTP Settings -> HTTP Host Header**: `sipandhalu.my.id`

### B. DNS Records
Di menu **DNS** -> **Records** pada domain `sipandhalu.my.id`:
- **Type**: `CNAME`
- **Name**: `@` (atau `sipandhalu.my.id`)
- **Target**: `36120c2f-09cc-48e4-95c0-f6754b719191.cfargotunnel.com`
- **Proxy status**: **Proxied** (Awan oranye aktif)

---

## 4. Pengaturan di Laptop/PC Lokal

### A. Virtual Host Apache Laragon
File: `C:\laragon\etc\apache2\sites-enabled\auto.sipandhalu.test.conf`
```apache
<VirtualHost *:80>
    DocumentRoot "C:/laragon/www/sipandhalu/sipandhalu/public"
    ServerName sipandhalu.test
    ServerAlias *.sipandhalu.test sipandhalu.my.id *.sipandhalu.my.id
    <Directory "C:/laragon/www/sipandhalu/sipandhalu/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:443>
    DocumentRoot "C:/laragon/www/sipandhalu/sipandhalu/public"
    ServerName sipandhalu.test
    ServerAlias *.sipandhalu.test sipandhalu.my.id *.sipandhalu.my.id
    SSLEngine on
    SSLCertificateFile "C:/laragon/etc/ssl/laragon.crt"
    SSLCertificateKeyFile "C:/laragon/etc/ssl/laragon.key"

    <Directory "C:/laragon/www/sipandhalu/sipandhalu/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### B. Konfigurasi Environment Aplikasi (.env)
File: `C:\laragon\www\sipandhalu\sipandhalu\.env`
```dotenv
APP_NAME="SIPANDHALU"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sipandhalu.my.id
...
```

### C. File Skrip Jalan Pintas (start-tunnel.bat)
File: `C:\laragon\www\sipandhalu\start-tunnel.bat`
```bat
@echo off
title Cloudflare Tunnel - SIPANDHALU
echo Starting Cloudflare Tunnel for sipandhalu.my.id...
"C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel run --token eyJhIjoiZTQyMGM4MTVmNmU0MTU1NmUxNmE4NjUzMWIzN2VjN2IiLCJ0IjoiMzYxMjBjMmYtMDljYy00OGU0LTk1YzAtZjY3NTRiNzE5MTkxIiwicyI6IllUYzJaR0poTldRdFltTXhPQzAwWW1WaExUZzBNREV0WW1aaFpEZG1aalkxTjJFdyJ9
pause
```

---

## 5. Cara Menjalankan Aplikasi Sehari-hari

Jika komputer baru dinyalakan atau di-restart:

1. **Buka Laragon**:
   - Klik tombol **Start All** (pastikan Apache dan MySQL menyala).
2. **Jalankan Tunnel**:
   - Dobel-klik file **`start-tunnel.bat`** yang ada di folder project `C:\laragon\www\sipandhalu\start-tunnel.bat`.
   - Biarkan jendela terminal tersebut tetap terbuka selama aplikasi ingin diakses publik.
3. **Akses Website**:
   - Buka browser dan kunjungi: **`https://sipandhalu.my.id/`**.

---

## 6. Panduan Pemecahan Masalah (Troubleshooting)

| Masalah / Kode Error | Kemungkinan Penyebab | Solusi |
| :--- | :--- | :--- |
| **Error 1033** (*Unable to resolve tunnel*) | 1. Program `cloudflared` belum dinyalakan.<br>2. Target CNAME DNS salah. | 1. Buka dan jalankan `start-tunnel.bat`.<br>2. Cek DNS Record CNAME di Cloudflare, pastikan targetnya `36120c2f-09cc-48e4-95c0-f6754b719191.cfargotunnel.com`. |
| **Error 502** (*Bad Gateway*) | Apache di Laragon belum menyala. | Buka Laragon dan klik tombol **Start All** (atau reload Apache). |
| **CSS/JS Tidak Rapi / Broken** | `APP_URL` di `.env` belum sesuai domain. | Pastikan file `.env` berisi `APP_URL=https://sipandhalu.my.id`. |
| **Error 521 / 522** | Port `localhost:80` terblokir firewall atau Apache mati. | Cek status Laragon dan pastikan port 80 tidak bentrok dengan aplikasi lain. |

---
*Dibuat pada: 13 September 2026*
