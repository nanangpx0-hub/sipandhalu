-- SIPANDHALU tahap 2a FINAL: tanpa FK kecamatan->desa (kunci diskusi tetap: kode 16 digit 4+2)
-- Alasan: FK CHAR(3) gagal 1215 di MySQL 8.0.30 Laragon walau definisi identik (bug/collation turunan).
-- Integritas kecamatan-desa ditegakkan di Service (cek eksistensi + transaksi), bukan FK.
USE sipandhalu;

DROP TABLE IF EXISTS sls;
DROP TABLE IF EXISTS desa;

CREATE TABLE IF NOT EXISTS desa (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kecamatan_kode CHAR(3) NOT NULL COMMENT 'kode kecamatan 3 digit, cth 170 (cek eksistensi di Service)',
  kode CHAR(3) NOT NULL COMMENT 'kode desa 3 digit',
  nama VARCHAR(100) NOT NULL,
  kode_full CHAR(10) GENERATED ALWAYS AS (CONCAT('3509', kecamatan_kode, kode)) STORED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_desa (kecamatan_kode, kode),
  KEY ix_desa_kec (kecamatan_kode),
  KEY ix_desa_nama (nama),
  CONSTRAINT ck_desa_kode CHECK (kode REGEXP '^[0-9]{3}$'),
  CONSTRAINT ck_desa_kec CHECK (kecamatan_kode REGEXP '^[0-9]{3}$')
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sls (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_full CHAR(16) NULL UNIQUE COMMENT 'prov2+kab2+kec3+desa3+sls4+sub2',
  prov CHAR(2) NOT NULL DEFAULT '35',
  kab CHAR(2) NOT NULL DEFAULT '09',
  kec CHAR(3) NOT NULL,
  desa CHAR(3) NOT NULL,
  sls CHAR(4) NULL COMMENT '4 digit, cth 0042',
  sub CHAR(2) NULL COMMENT '2 digit, cth 00',
  nks CHAR(5) NOT NULL COMMENT 'NKS BPS 5 digit, stabil per SLS',
  desa_id INT UNSIGNED NULL,
  dusun VARCHAR(100) NULL,
  rw VARCHAR(5) NULL,
  rt VARCHAR(5) NULL,
  nama_sls VARCHAR(150) NULL,
  ketua VARCHAR(100) NULL,
  klasifikasi TINYINT NULL COMMENT '1=Perkotaan, 2=Perdesaan',
  jml_kk INT UNSIGNED NULL,
  jml_rt INT UNSIGNED NULL,
  is_aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sls_desa FOREIGN KEY (desa_id) REFERENCES desa (id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  UNIQUE KEY uq_sls_nks (nks),
  KEY ix_sls_kec_desa (kec, desa),
  KEY ix_sls_full (kode_full),
  CONSTRAINT ck_sls_full CHECK (kode_full IS NULL OR kode_full REGEXP '^[0-9]{16}$'),
  CONSTRAINT ck_sls_nks CHECK (nks REGEXP '^[0-9]{5}$')
) ENGINE=InnoDB;
