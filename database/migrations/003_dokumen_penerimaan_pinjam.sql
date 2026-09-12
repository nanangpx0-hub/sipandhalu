-- SIPANDHALU tahap 3: Penerimaan & Peminjaman Dokumen Lapangan (Susenas & Peta WS/WSS)
USE sipandhalu;

-- 1. Atribut fisik penerimaan dokumen pada tabel sampel
ALTER TABLE sampel
  ADD COLUMN dok_pemutakhiran_status ENUM('BELUM','DITERIMA','DIPINJAM') NOT NULL DEFAULT 'BELUM',
  ADD COLUMN dok_pemutakhiran_waktu DATETIME NULL,
  ADD COLUMN dok_pemutakhiran_oleh INT UNSIGNED NULL,
  ADD COLUMN dok_pemutakhiran_penyerah INT UNSIGNED NULL,
  ADD COLUMN peta_status ENUM('BELUM','DITERIMA','DIPINJAM') NOT NULL DEFAULT 'BELUM',
  ADD COLUMN peta_waktu DATETIME NULL,
  ADD COLUMN peta_oleh INT UNSIGNED NULL,
  ADD COLUMN peta_penyerah INT UNSIGNED NULL,
  ADD COLUMN catatan_dokumen VARCHAR(255) NULL;

-- Index pendukung filter status dokumen
ALTER TABLE sampel
  ADD KEY ix_sampel_pemutakhiran_status (dok_pemutakhiran_status),
  ADD KEY ix_sampel_peta_status (peta_status);

-- 2. Tabel transaksi peminjaman dokumen (sirkulasi berkas fisik)
CREATE TABLE IF NOT EXISTS peminjaman_dokumen (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sampel_id INT UNSIGNED NOT NULL,
  jenis_dok ENUM('SEMUA','PEMUTAKHIRAN','PETA') NOT NULL DEFAULT 'SEMUA',
  peminjam_id INT UNSIGNED NOT NULL COMMENT 'FK orang',
  peminjam_peran ENUM('PCL','PML','PENGOLAH','LAINNYA') NOT NULL,
  waktu_pinjam DATETIME NOT NULL,
  alasan VARCHAR(255) NOT NULL,
  operator_pinjam_id INT UNSIGNED NOT NULL COMMENT 'FK users',
  waktu_kembali DATETIME NULL,
  operator_kembali_id INT UNSIGNED NULL COMMENT 'FK users',
  catatan_kembali VARCHAR(255) NULL,
  status ENUM('DIPINJAM','DIKEMBALIKAN') NOT NULL DEFAULT 'DIPINJAM',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pinjam_sampel FOREIGN KEY (sampel_id) REFERENCES sampel (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_pinjam_orang FOREIGN KEY (peminjam_id) REFERENCES orang (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_pinjam_op FOREIGN KEY (operator_pinjam_id) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  KEY ix_pinjam_sampel (sampel_id),
  KEY ix_pinjam_status (status),
  KEY ix_pinjam_waktu (waktu_pinjam)
) ENGINE=InnoDB;
