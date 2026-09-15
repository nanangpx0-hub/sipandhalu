-- SIPANDHALU tahap 4: Pemantauan Pengiriman Kuesioner Tingkat Rumah Tangga (Dokumen Kirim Kab)
USE sipandhalu;

CREATE TABLE IF NOT EXISTS sampel_ruta (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sampel_id INT UNSIGNED NOT NULL,
  no_urut_ruta TINYINT UNSIGNED NOT NULL COMMENT '1 s.d. 10',
  status_selesai ENUM('BELUM', 'SUDAH') NOT NULL DEFAULT 'BELUM',
  catatan_modul TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Ya, 0=Tidak',
  catatan_kp TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=Ya, 0=Tidak',
  tgl_pengiriman DATE NULL,
  ttd_sos VARCHAR(100) NULL COMMENT 'Paraf/nama verifikator Tim Sosial',
  ttd_ipds VARCHAR(100) NULL COMMENT 'Paraf/nama verifikator Tim IPDS',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_sampel_ruta (sampel_id, no_urut_ruta),
  KEY ix_sampel_ruta_status (status_selesai, tgl_pengiriman),
  CONSTRAINT fk_ruta_sampel FOREIGN KEY (sampel_id) REFERENCES sampel (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT ck_no_urut_ruta CHECK (no_urut_ruta BETWEEN 1 AND 10)
) ENGINE=InnoDB;

-- Seeder idempoten: pastikan setiap sampel yang sudah ada memiliki 10 baris ruta default.
INSERT IGNORE INTO sampel_ruta (sampel_id, no_urut_ruta)
SELECT sp.id, n.no
FROM sampel sp
JOIN (
  SELECT 1 AS no UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
  UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
) n ON TRUE;