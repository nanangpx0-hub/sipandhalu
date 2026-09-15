-- Seed SIPANDHALU tahap 1
USE sipandhalu;
INSERT INTO roles (code, label) VALUES
 ('ADMIN','Administrator'),
 ('OPERATOR','Operator Pengolahan'),
 ('PML','Pengawas Lapangan'),
 ('PCL','Pencacah Lapangan'),
 ('PENGOLAH','Pengolah Data'),
 ('PENGAWAS_OLAH','Pengawas Pengolahan'),
 ('SM_SOSIAL','SM Tim Statistik Sosial'),
 ('SM_PLS','SM Tim Pengolahan & Layanan Statistik'),
 ('VIEWER','Viewer')
ON DUPLICATE KEY UPDATE label=VALUES(label);
