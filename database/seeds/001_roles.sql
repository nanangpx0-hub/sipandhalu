-- Seed SIPANDHALU tahap 1
USE sipandhalu;
INSERT INTO roles (code, label) VALUES
 ('ADMIN','Administrator'),
 ('OPERATOR','Operator Pengolahan'),
 ('PML','Pengawas Lapangan'),
 ('PCL','Pencacah Lapangan'),
 ('PENGOLAH','Pengolah'),
 ('VIEWER','Viewer')
ON DUPLICATE KEY UPDATE label=VALUES(label);
