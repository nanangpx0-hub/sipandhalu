-- SIPANDHALU tahap 6: Lembar Kerja (LK) Pengolahan Sampel & Kendali Mutu
USE sipandhalu;

-- 1. Tambah kolom status dokumen fisik tingkat ruta dan status transfer pengolahan
ALTER TABLE sampel_ruta
  ADD COLUMN status_dokumen ENUM('BELUM', 'ADA') NOT NULL DEFAULT 'BELUM' AFTER no_urut_ruta,
  ADD COLUMN status_transfer_k TINYINT(1) NOT NULL DEFAULT 0 AFTER status_selesai,
  ADD COLUMN status_transfer_kp TINYINT(1) NOT NULL DEFAULT 0 AFTER status_transfer_k,
  ADD COLUMN status_transfer_seruti TINYINT(1) NOT NULL DEFAULT 0 AFTER status_transfer_kp;

-- 2. Tambah kolom feedback kolaboratif error kuesioner KP (Konsumsi-Pengeluaran)
ALTER TABLE sampel_ruta
  ADD COLUMN ket_kp_pengolah TEXT NULL AFTER catatan_kp,
  ADD COLUMN ket_kp_lapangan TEXT NULL AFTER ket_kp_pengolah,
  ADD COLUMN ket_kp_sosial TEXT NULL AFTER ket_kp_lapangan;

-- 3. Tambah kolom feedback kolaboratif error kuesioner Modul (Kesehatan/Pendidikan/Perumahan)
ALTER TABLE sampel_ruta
  ADD COLUMN ket_m_pengolah TEXT NULL AFTER catatan_modul,
  ADD COLUMN ket_m_lapangan TEXT NULL AFTER ket_m_pengolah,
  ADD COLUMN ket_m_sosial TEXT NULL AFTER ket_m_lapangan;

-- 4. Tambah kolom hasil uji petik Pengawas Pengolahan
ALTER TABLE sampel_ruta
  ADD COLUMN uji_petik_pengawas TEXT NULL AFTER ket_m_sosial;

-- 5. Index pendukung
ALTER TABLE sampel_ruta
  ADD KEY ix_ruta_transfer (status_transfer_k, status_transfer_kp, status_transfer_seruti),
  ADD KEY ix_ruta_dokumen (status_dokumen);
