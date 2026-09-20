-- 009_telaah_ipds.sql
-- Telaah & Keputusan Tim IPDS pada Lembar Kerja Pengolahan Sampel:
-- kolom keterangan KP/Modul versi Tim IPDS (Rerun aman: duplikat diabaikan migrate.php)

ALTER TABLE `sampel_ruta`
  ADD COLUMN `ket_kp_ipds` TEXT NULL AFTER `ket_kp_sosial`,
  ADD COLUMN `ket_m_ipds` TEXT NULL AFTER `ket_m_sosial`;
