-- SIPANDHALU tahap 2b: periode + sampel + penugasan (level SLS, tanpa Blok V)
-- 28 SLS per periode, tiap SLS target 10 KK/RT (28x10=280)
USE sipandhalu;

CREATE TABLE IF NOT EXISTS periode (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tahun SMALLINT UNSIGNED NOT NULL,
  jenis ENUM('SERUTI_Q1','SERUTI_Q2','SERUTI_Q3','SERUTI_Q4','SUSENAS_S1','SUSENAS_S2') NOT NULL,
  label VARCHAR(100) NOT NULL,
  tgl_mulai DATE NULL,
  tgl_selesai DATE NULL,
  status ENUM('DRAFT','AKTIF','TUTUP') NOT NULL DEFAULT 'DRAFT',
  catatan TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_periode (tahun, jenis),
  KEY ix_periode_status (status, tahun)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sampel (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  periode_id INT UNSIGNED NOT NULL,
  sls_id INT UNSIGNED NOT NULL,
  target_sampel SMALLINT UNSIGNED NOT NULL DEFAULT 10,
  muatan_awal INT UNSIGNED NULL COMMENT 'JRT Excel alokasi',
  hasil_kk INT UNSIGNED NULL COMMENT 'Blok II.1 pemutakhiran',
  hasil_rt INT UNSIGNED NULL COMMENT 'Blok II.2 pemutakhiran',
  dokumen_vsen TINYINT(1) NOT NULL DEFAULT 0,
  peta_ws TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sampel_periode FOREIGN KEY (periode_id) REFERENCES periode (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_sampel_sls FOREIGN KEY (sls_id) REFERENCES sls (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_sampel (periode_id, sls_id),
  KEY ix_sampel_periode (periode_id),
  KEY ix_sampel_sls (sls_id),
  KEY ix_sampel_dok (dokumen_vsen, peta_ws)
) ENGINE=InnoDB;

-- 1 sampel = 1 PCL + 1 PML + 1 Pengolah. Tiga peran wajib orang berbeda
-- (CHECK). Aturan 1 orang 1 peran per periode ditegakkan di Service.
CREATE TABLE IF NOT EXISTS penugasan (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sampel_id INT UNSIGNED NOT NULL,
  pcl_id INT UNSIGNED NOT NULL,
  pml_id INT UNSIGNED NOT NULL,
  pengolah_id INT UNSIGNED NOT NULL,
  status ENUM('DRAFT','AKTIF','SELESAI','BATAL') NOT NULL DEFAULT 'AKTIF',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_tugas_sampel FOREIGN KEY (sampel_id) REFERENCES sampel (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_tugas_pcl FOREIGN KEY (pcl_id) REFERENCES orang (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_tugas_pml FOREIGN KEY (pml_id) REFERENCES orang (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_tugas_pengolah FOREIGN KEY (pengolah_id) REFERENCES orang (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uq_tugas_sampel (sampel_id),
  KEY ix_tugas_pcl (pcl_id),
  KEY ix_tugas_pml (pml_id),
  KEY ix_tugas_pengolah (pengolah_id)
  -- CHECK beda peran tidak bisa di DDL MySQL 8 (3823: kolom dipakai FK CASCADE).
  -- Ditegakkan di PenugasanService::validate + diuji (3 peran wajib orang berbeda).
) ENGINE=InnoDB;
