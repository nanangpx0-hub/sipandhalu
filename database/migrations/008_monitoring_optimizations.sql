-- 008_monitoring_optimizations.sql
-- Optimasi performa modul monitoring operasional: indeks dan kelengkapan tanggal survei

ALTER TABLE `sampel_ruta` ADD INDEX `ix_ruta_updated_at` (`updated_at`);

UPDATE `periode`
SET `tgl_mulai` = '2026-09-01', `tgl_selesai` = '2026-09-30'
WHERE `id` = 1;

UPDATE `periode`
SET `tgl_mulai` = '2026-01-01', `tgl_selesai` = '2026-03-31'
WHERE `id` = 3;
