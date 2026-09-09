-- SIPANDHALU tahap 2a: HANYA kecamatan (tanpa desa/sls; keduanya di 002d final tanpa FK CHAR)
USE sipandhalu;

CREATE TABLE IF NOT EXISTS kecamatan (
  kode CHAR(3) PRIMARY KEY COMMENT 'kode kecamatan 3 digit, cth 170',
  nama VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_kec_nama (nama),
  CONSTRAINT ck_kec_kode CHECK (kode REGEXP '^[0-9]{3}$')
) ENGINE=InnoDB;
