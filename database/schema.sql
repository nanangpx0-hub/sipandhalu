-- SIPANDHALU tahap 1: manajemen user + petugas (orang pool + alias + users + audit)
-- MySQL 8, InnoDB, utf8mb4_unicode_ci
-- Konvensi diskusi: 1 SLS=1 RT, NKS beda dgn kode SLS, histori per periode, 1 orang 1 peran/periode
-- Tahap ini baru: roles, orang, orang_alias, users, audit_logs. Tabel SLS/periode menyusul tahap 2.

CREATE DATABASE IF NOT EXISTS sipandhalu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sipandhalu;

-- Peran login. Pool orang TIDAK terikat peran tetap; peran ditentukan per penugasan periode.
CREATE TABLE IF NOT EXISTS roles (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) NOT NULL,
  label VARCHAR(60) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_roles_code (code)
) ENGINE=InnoDB;

-- Master orang: SATU daftar untuk semua manusia (calon PCL/PML/Pengolah/Admin).
-- Kanonik: Title Case, TRIM. Dedup via nama_normalized.
CREATE TABLE IF NOT EXISTS orang (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL COMMENT 'kanonik Title Case, TRIM',
  nama_normalized VARCHAR(100) GENERATED ALWAYS AS (LOWER(TRIM(nama))) STORED,
  no_hp VARCHAR(20) NULL,
  email VARCHAR(190) NULL,
  alamat VARCHAR(255) NULL,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_orang_nama (nama_normalized),
  KEY ix_orang_aktif (is_aktif),
  KEY ix_orang_nama (nama),
  FULLTEXT KEY ft_orang_nama (nama)
) ENGINE=InnoDB;

-- Alias tulisan nama (Junaidi vs Junaidi Ari Siswanto, Rozy vs Rozi, Ike Noor vs Ike Noorhayati, dst).
CREATE TABLE IF NOT EXISTS orang_alias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orang_id INT UNSIGNED NOT NULL,
  alias_normalized VARCHAR(100) NOT NULL COMMENT 'LOWER(TRIM(varian))',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_alias_orang FOREIGN KEY (orang_id) REFERENCES orang (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uq_alias (alias_normalized),
  KEY ix_alias_orang (orang_id)
) ENGINE=InnoDB;

-- User login. Hanya subset orang yang punya akun (8 pengolah + admin).
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orang_id INT UNSIGNED NULL,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL COMMENT 'Argon2id',
  role_id TINYINT UNSIGNED NOT NULL,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  must_reset TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=wajib ganti password saat login pertama (migrasi Jember3509)',
  last_login_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_orang FOREIGN KEY (orang_id) REFERENCES orang (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_users_email (email),
  KEY ix_users_role (role_id, is_aktif),
  KEY ix_users_orang (orang_id)
) ENGINE=InnoDB;

-- Audit generik JSON (MySQL 8). Tahap 1 dipakai untuk CREATE/UPDATE/DELETE orang+users+login.
CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  aksi VARCHAR(20) NOT NULL COMMENT 'CREATE,UPDATE,DELETE,LOGIN,LOGOUT,RESET_PW,IMPORT',
  tabel_target VARCHAR(50) NOT NULL,
  id_target VARCHAR(50) NULL,
  before_json JSON NULL,
  after_json JSON NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY ix_audit_user (user_id, created_at),
  KEY ix_audit_tabel (tabel_target, id_target),
  KEY ix_audit_waktu (created_at)
) ENGINE=InnoDB;
