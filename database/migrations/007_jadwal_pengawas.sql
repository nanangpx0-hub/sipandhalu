-- SIPANDHALU tahap 7: Jadwal Pengawas Pengolahan Susenas S2 2026
USE sipandhalu;

CREATE TABLE IF NOT EXISTS jadwal_pengawas_pengolahan (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  periode_id INT UNSIGNED NOT NULL COMMENT 'FK periode (Susenas S2 2026)',
  tanggal DATE NOT NULL,
  orang_id INT UNSIGNED NULL COMMENT 'FK orang (NULL saat LIBUR)',
  nama_pengawas VARCHAR(100) NOT NULL COMMENT 'snapshot nama saat dijadwalkan',
  hari VARCHAR(20) NOT NULL COMMENT 'Senin..Minggu (id)',
  status ENUM('TUGAS','LIBUR') NOT NULL DEFAULT 'TUGAS',
  keterangan VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_jadwal_periode FOREIGN KEY (periode_id) REFERENCES periode (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_jadwal_orang FOREIGN KEY (orang_id) REFERENCES orang (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  UNIQUE KEY uq_jadwal_periode_tanggal (periode_id, tanggal),
  KEY ix_jadwal_tanggal (tanggal),
  KEY ix_jadwal_orang (orang_id),
  KEY ix_jadwal_status (status)
) ENGINE=InnoDB;
