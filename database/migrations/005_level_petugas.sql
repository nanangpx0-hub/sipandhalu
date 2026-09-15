-- SIPANDHALU tahap 5: Level Petugas & Master Roles Lengkap (9 Level)
USE sipandhalu;

-- 1. Pastikan 9 level / peran lengkap pada tabel roles
INSERT INTO roles (code, label) VALUES
  ('ADMIN', 'Administrator'),
  ('OPERATOR', 'Operator Pengolahan'),
  ('PML', 'Pengawas Lapangan'),
  ('PCL', 'Pencacah Lapangan'),
  ('PENGOLAH', 'Pengolah Data'),
  ('PENGAWAS_OLAH', 'Pengawas Pengolahan'),
  ('SM_SOSIAL', 'SM Tim Statistik Sosial'),
  ('SM_PLS', 'SM Tim Pengolahan & Layanan Statistik'),
  ('VIEWER', 'Viewer')
ON DUPLICATE KEY UPDATE label=VALUES(label);

-- 2. Tambah kolom role_id pada tabel orang
ALTER TABLE orang
  ADD COLUMN role_id TINYINT UNSIGNED NULL AFTER email;

ALTER TABLE orang
  ADD CONSTRAINT fk_orang_role FOREIGN KEY (role_id) REFERENCES roles (id)
    ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE orang
  ADD KEY ix_orang_role (role_id);

-- 3. Backfill data level orang yang sudah ada:
-- Sinkronisasi dari users yang tertaut ke orang
UPDATE orang o
JOIN users u ON u.orang_id = o.id
SET o.role_id = u.role_id
WHERE o.role_id IS NULL;

-- Sinkronisasi dari penugasan (PCL, PML, Pengolah)
UPDATE orang o
JOIN penugasan pg ON pg.pcl_id = o.id
JOIN roles r ON r.code = 'PCL'
SET o.role_id = r.id
WHERE o.role_id IS NULL;

UPDATE orang o
JOIN penugasan pg ON pg.pml_id = o.id
JOIN roles r ON r.code = 'PML'
SET o.role_id = r.id
WHERE o.role_id IS NULL;

UPDATE orang o
JOIN penugasan pg ON pg.pengolah_id = o.id
JOIN roles r ON r.code = 'PENGOLAH'
SET o.role_id = r.id
WHERE o.role_id IS NULL;
