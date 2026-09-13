-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: sipandhalu
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `sipandhalu`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sipandhalu` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `sipandhalu`;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL,
  `aksi` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'CREATE,UPDATE,DELETE,LOGIN,LOGOUT,RESET_PW,IMPORT',
  `tabel_target` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_target` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `before_json` json DEFAULT NULL,
  `after_json` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ix_audit_user` (`user_id`,`created_at`),
  KEY `ix_audit_tabel` (`tabel_target`,`id_target`),
  KEY `ix_audit_waktu` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'LOGIN','users','1',NULL,'{\"hasil\": \"sukses\"}','127.0.0.1','curl/8.21.0','2026-09-12 08:53:32'),(2,1,'LOGIN','users','1',NULL,'{\"hasil\": \"gagal\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-12 17:26:44'),(3,1,'LOGIN','users','1',NULL,'{\"hasil\": \"gagal\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-12 17:26:50'),(4,1,'LOGIN','users','1',NULL,'{\"hasil\": \"gagal\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-12 17:26:57'),(5,1,'LOGIN','users','1',NULL,'{\"hasil\": \"sukses\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-12 17:27:15'),(6,1,'LOGIN','users','1',NULL,'{\"hasil\": \"sukses\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-12 17:33:51'),(7,1,'LOGIN','users','1',NULL,'{\"hasil\": \"sukses\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','2026-09-13 07:38:40');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `desa`
--

DROP TABLE IF EXISTS `desa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `desa` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kecamatan_kode` char(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kode kecamatan 3 digit, cth 170 (cek eksistensi di Service)',
  `kode` char(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kode desa 3 digit',
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_full` char(10) COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (concat(_utf8mb4'3509',`kecamatan_kode`,`kode`)) STORED,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_desa` (`kecamatan_kode`,`kode`),
  KEY `ix_desa_kec` (`kecamatan_kode`),
  KEY `ix_desa_nama` (`nama`),
  CONSTRAINT `ck_desa_kec` CHECK (regexp_like(`kecamatan_kode`,_utf8mb4'^[0-9]{3}$')),
  CONSTRAINT `ck_desa_kode` CHECK (regexp_like(`kode`,_utf8mb4'^[0-9]{3}$'))
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `desa`
--

LOCK TABLES `desa` WRITE;
/*!40000 ALTER TABLE `desa` DISABLE KEYS */;
INSERT INTO `desa` (`id`, `kecamatan_kode`, `kode`, `nama`, `created_at`) VALUES (1,'020','003','MENAMPU','2026-09-12 08:45:39'),(2,'020','007','TEMBOKREJO','2026-09-12 08:45:39'),(3,'020','008','KARANGREJO','2026-09-12 08:45:39'),(4,'030','001','MOJOMULYO','2026-09-12 08:45:39'),(5,'040','005','DUKUH DEMPOK','2026-09-12 08:45:39'),(6,'050','001','SUMBERREJO','2026-09-12 08:45:39'),(7,'050','005','PONTANG','2026-09-12 08:45:39'),(8,'060','005','SIDODADI','2026-09-12 08:45:39'),(9,'070','002','PACE','2026-09-12 08:45:39'),(10,'070','009','SIDOMULYO','2026-09-12 08:45:39'),(11,'090','005','MUMBULSARI','2026-09-12 08:45:39'),(12,'120','003','ROWOTAMTU','2026-09-12 08:45:39'),(13,'130','004','BALUNG KULON','2026-09-12 08:45:39'),(14,'140','001','SUKORENO','2026-09-12 08:45:39'),(15,'170','002','ROWO TENGAH','2026-09-12 08:45:39'),(16,'180','004','SELODAKON','2026-09-12 08:45:39'),(17,'180','007','PATEMON','2026-09-12 08:45:39'),(18,'190','009','TUGUSARI','2026-09-12 08:45:39'),(19,'200','006','SUCI','2026-09-12 08:45:39'),(20,'250','001','SUREN','2026-09-12 08:45:39'),(21,'250','002','SUMBER SALAK','2026-09-12 08:45:39'),(22,'250','003','SUMBER BULUS','2026-09-12 08:45:40'),(23,'260','008','PRINGGONDANI','2026-09-12 08:45:40'),(24,'710','002','SEMPUSARI','2026-09-12 08:45:40'),(25,'710','007','KEBON AGUNG','2026-09-12 08:45:40'),(26,'720','001','KERANJINGAN','2026-09-12 08:45:40'),(27,'720','006','TEGAL GEDE','2026-09-12 08:45:40');
/*!40000 ALTER TABLE `desa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kecamatan`
--

DROP TABLE IF EXISTS `kecamatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kecamatan` (
  `kode` char(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kode kecamatan 3 digit, cth 170',
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `uq_kec_nama` (`nama`),
  CONSTRAINT `ck_kec_kode` CHECK (regexp_like(`kode`,_utf8mb4'^[0-9]{3}$'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kecamatan`
--

LOCK TABLES `kecamatan` WRITE;
/*!40000 ALTER TABLE `kecamatan` DISABLE KEYS */;
INSERT INTO `kecamatan` VALUES ('020','GUMUKMAS','2026-09-12 08:45:39'),('030','PUGER','2026-09-12 08:45:39'),('040','WULUHAN','2026-09-12 08:45:39'),('050','AMBULU','2026-09-12 08:45:39'),('060','TEMPUREJO','2026-09-12 08:45:39'),('070','SILO','2026-09-12 08:45:39'),('090','MUMBULSARI','2026-09-12 08:45:39'),('120','RAMBIPUJI','2026-09-12 08:45:39'),('130','BALUNG','2026-09-12 08:45:39'),('140','UMBULSARI','2026-09-12 08:45:39'),('170','SUMBERBARU','2026-09-12 08:45:39'),('180','TANGGUL','2026-09-12 08:45:39'),('190','BANGSALSARI','2026-09-12 08:45:39'),('200','PANTI','2026-09-12 08:45:39'),('250','LEDOKOMBO','2026-09-12 08:45:39'),('260','SUMBERJAMBE','2026-09-12 08:45:39'),('710','KALIWATES','2026-09-12 08:45:39'),('720','SUMBERSARI','2026-09-12 08:45:39');
/*!40000 ALTER TABLE `kecamatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orang`
--

DROP TABLE IF EXISTS `orang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orang` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kanonik Title Case, TRIM',
  `nama_normalized` varchar(100) COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (lower(trim(`nama`))) STORED,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orang_nama` (`nama_normalized`),
  KEY `ix_orang_aktif` (`is_aktif`),
  KEY `ix_orang_nama` (`nama`),
  FULLTEXT KEY `ft_orang_nama` (`nama`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orang`
--

LOCK TABLES `orang` WRITE;
/*!40000 ALTER TABLE `orang` DISABLE KEYS */;
INSERT INTO `orang` (`id`, `nama`, `no_hp`, `email`, `alamat`, `is_aktif`, `created_at`, `updated_at`) VALUES (1,'Petugas Dummy 01',NULL,'dummy01@contoh.go.id',NULL,1,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(2,'Petugas Dummy 02',NULL,'dummy02@contoh.go.id',NULL,1,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(3,'Petugas Dummy 03',NULL,'dummy03@contoh.go.id',NULL,1,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(4,'Petugas Dummy 04',NULL,'dummy04@contoh.go.id',NULL,1,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(5,'Petugas Dummy 05',NULL,'dummy05@contoh.go.id',NULL,1,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(6,'Petugas Dummy 06',NULL,'dummy06@contoh.go.id',NULL,1,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(7,'Petugas Dummy 07',NULL,'dummy07@contoh.go.id',NULL,1,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(8,'Petugas Dummy 08',NULL,'dummy08@contoh.go.id',NULL,1,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(9,'Pcl Dummy 01',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(10,'Pcl Dummy 02',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(11,'Pcl Dummy 03',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(12,'Pcl Dummy 04',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(13,'Pcl Dummy 05',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(14,'Pcl Dummy 06',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(15,'Pcl Dummy 07',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(16,'Pcl Dummy 08',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(17,'Pcl Dummy 09',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(18,'Pcl Dummy 10',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(19,'Pcl Dummy 11',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(20,'Pcl Dummy 12',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(21,'Pcl Dummy 13',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(22,'Pcl Dummy 14',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(23,'Pcl Dummy 15',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(24,'Pcl Dummy 16',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(25,'Pcl Dummy 17',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(26,'Pcl Dummy 18',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(27,'Pcl Dummy 19',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(28,'Pcl Dummy 20',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(29,'Pcl Dummy 21',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(30,'Pcl Dummy 22',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(31,'Pcl Dummy 23',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(32,'Pcl Dummy 24',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(33,'Pcl Dummy 25',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(34,'Pcl Dummy 26',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(35,'Pcl Dummy 27',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(36,'Pcl Dummy 28',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(37,'Pml Dummy 01',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(38,'Pml Dummy 02',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(39,'Pml Dummy 03',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(40,'Pml Dummy 04',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(41,'Pml Dummy 05',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(42,'Pml Dummy 06',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(43,'Pml Dummy 07',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(44,'Pml Dummy 08',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(45,'Pml Dummy 09',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(46,'Pml Dummy 10',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(47,'Pml Dummy 11',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(48,'Pml Dummy 12',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(49,'Pml Dummy 13',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(50,'Pml Dummy 14',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42'),(51,'Pml Dummy 15',NULL,NULL,NULL,1,'2026-09-12 08:45:42','2026-09-12 08:45:42');
/*!40000 ALTER TABLE `orang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orang_alias`
--

DROP TABLE IF EXISTS `orang_alias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orang_alias` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `orang_id` int unsigned NOT NULL,
  `alias_normalized` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'LOWER(TRIM(varian))',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_alias` (`alias_normalized`),
  KEY `ix_alias_orang` (`orang_id`),
  CONSTRAINT `fk_alias_orang` FOREIGN KEY (`orang_id`) REFERENCES `orang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orang_alias`
--

LOCK TABLES `orang_alias` WRITE;
/*!40000 ALTER TABLE `orang_alias` DISABLE KEYS */;
INSERT INTO `orang_alias` VALUES (1,2,'petugas dummy 02','2026-09-12 08:45:36'),(2,3,'petugas dummy 03','2026-09-12 08:45:36'),(3,4,'petugas dummy 04','2026-09-12 08:45:36'),(4,5,'petugas dummy 05','2026-09-12 08:45:36'),(5,8,'petugas dummy 08','2026-09-12 08:45:36');
/*!40000 ALTER TABLE `orang_alias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peminjaman_dokumen`
--

DROP TABLE IF EXISTS `peminjaman_dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `peminjaman_dokumen` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sampel_id` int unsigned NOT NULL,
  `jenis_dok` enum('SEMUA','PEMUTAKHIRAN','PETA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SEMUA',
  `peminjam_id` int unsigned NOT NULL COMMENT 'FK orang',
  `peminjam_peran` enum('PCL','PML','PENGOLAH','LAINNYA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_pinjam` datetime NOT NULL,
  `alasan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operator_pinjam_id` int unsigned NOT NULL COMMENT 'FK users',
  `waktu_kembali` datetime DEFAULT NULL,
  `operator_kembali_id` int unsigned DEFAULT NULL COMMENT 'FK users',
  `catatan_kembali` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('DIPINJAM','DIKEMBALIKAN') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DIPINJAM',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pinjam_orang` (`peminjam_id`),
  KEY `fk_pinjam_op` (`operator_pinjam_id`),
  KEY `ix_pinjam_sampel` (`sampel_id`),
  KEY `ix_pinjam_status` (`status`),
  KEY `ix_pinjam_waktu` (`waktu_pinjam`),
  CONSTRAINT `fk_pinjam_op` FOREIGN KEY (`operator_pinjam_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pinjam_orang` FOREIGN KEY (`peminjam_id`) REFERENCES `orang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pinjam_sampel` FOREIGN KEY (`sampel_id`) REFERENCES `sampel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peminjaman_dokumen`
--

LOCK TABLES `peminjaman_dokumen` WRITE;
/*!40000 ALTER TABLE `peminjaman_dokumen` DISABLE KEYS */;
/*!40000 ALTER TABLE `peminjaman_dokumen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penugasan`
--

DROP TABLE IF EXISTS `penugasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penugasan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sampel_id` int unsigned NOT NULL,
  `pcl_id` int unsigned NOT NULL,
  `pml_id` int unsigned NOT NULL,
  `pengolah_id` int unsigned NOT NULL,
  `status` enum('DRAFT','AKTIF','SELESAI','BATAL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AKTIF',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tugas_sampel` (`sampel_id`),
  KEY `ix_tugas_pcl` (`pcl_id`),
  KEY `ix_tugas_pml` (`pml_id`),
  KEY `ix_tugas_pengolah` (`pengolah_id`),
  CONSTRAINT `fk_tugas_pcl` FOREIGN KEY (`pcl_id`) REFERENCES `orang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tugas_pengolah` FOREIGN KEY (`pengolah_id`) REFERENCES `orang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tugas_pml` FOREIGN KEY (`pml_id`) REFERENCES `orang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tugas_sampel` FOREIGN KEY (`sampel_id`) REFERENCES `sampel` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penugasan`
--

LOCK TABLES `penugasan` WRITE;
/*!40000 ALTER TABLE `penugasan` DISABLE KEYS */;
INSERT INTO `penugasan` VALUES (1,1,9,37,1,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(2,2,10,38,2,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(3,3,11,39,3,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(4,4,12,40,4,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(5,5,13,41,5,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(6,6,14,42,6,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(7,7,15,43,7,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(8,8,16,44,8,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(9,9,17,45,1,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(10,10,18,46,2,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(11,11,19,47,3,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(12,12,20,48,4,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(13,13,21,49,5,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(14,14,22,50,6,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(15,15,23,51,7,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(16,16,24,37,8,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(17,17,25,38,1,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(18,18,26,39,2,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(19,19,27,40,3,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(20,20,28,41,4,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(21,21,29,42,5,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(22,22,30,43,6,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(23,23,31,44,7,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(24,24,32,45,8,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(25,25,33,46,1,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(26,26,34,47,2,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(27,27,35,48,3,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(28,28,36,49,4,'AKTIF','2026-09-12 08:45:43','2026-09-12 08:45:43'),(29,29,14,44,4,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(30,30,15,45,5,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(31,31,16,46,6,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(32,32,17,47,7,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(33,33,18,48,8,'SELESAI','2026-09-12 08:45:43','2026-09-12 08:45:43'),(34,34,19,49,1,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(35,35,20,50,2,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(36,36,21,51,3,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(37,37,22,37,4,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(38,38,23,38,5,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(39,39,24,39,6,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(40,40,25,40,7,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(41,41,26,41,8,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(42,42,27,42,1,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(43,43,28,43,2,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(44,44,29,44,3,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(45,45,30,45,4,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(46,46,31,46,5,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(47,47,32,47,6,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(48,48,33,48,7,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(49,49,34,49,8,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(50,50,35,50,1,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(51,51,36,51,2,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(52,52,9,37,3,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(53,53,10,38,4,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(54,54,11,39,5,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(55,55,12,40,6,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(56,56,13,41,7,'SELESAI','2026-09-12 08:45:44','2026-09-12 08:45:44'),(57,57,9,37,1,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44'),(58,58,10,38,2,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44'),(59,59,11,39,3,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44'),(60,60,12,40,4,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44'),(61,61,13,41,5,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44'),(62,62,14,42,6,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44'),(63,63,15,43,7,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44'),(64,64,16,44,8,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44'),(65,65,17,45,1,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44'),(66,66,18,46,2,'DRAFT','2026-09-12 08:45:44','2026-09-12 08:45:44');
/*!40000 ALTER TABLE `penugasan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periode`
--

DROP TABLE IF EXISTS `periode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periode` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tahun` smallint unsigned NOT NULL,
  `jenis` enum('SERUTI_Q1','SERUTI_Q2','SERUTI_Q3','SERUTI_Q4','SUSENAS_S1','SUSENAS_S2') COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl_mulai` date DEFAULT NULL,
  `tgl_selesai` date DEFAULT NULL,
  `status` enum('DRAFT','AKTIF','TUTUP') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_periode` (`tahun`,`jenis`),
  KEY `ix_periode_status` (`status`,`tahun`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periode`
--

LOCK TABLES `periode` WRITE;
/*!40000 ALTER TABLE `periode` DISABLE KEYS */;
INSERT INTO `periode` VALUES (1,2026,'SUSENAS_S2','2026-S2 Susenas September',NULL,NULL,'AKTIF',NULL,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(2,2026,'SUSENAS_S1','2026-S1 Susenas Maret','2026-03-01','2026-03-31','TUTUP','Periode DUMMY (TUTUP): demo histori + rotasi petugas.','2026-09-12 08:45:43','2026-09-12 08:45:43'),(3,2026,'SERUTI_Q1','2026-Q1 Seruti Triwulan I',NULL,NULL,'DRAFT','Periode DUMMY (DRAFT): demo SLS berulang + penugasan baru.','2026-09-12 08:45:43','2026-09-12 08:45:43');
/*!40000 ALTER TABLE `periode` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'ADMIN','Administrator','2026-09-12 08:45:35'),(2,'OPERATOR','Operator Pengolahan','2026-09-12 08:45:35'),(3,'PML','Pengawas Lapangan','2026-09-12 08:45:35'),(4,'PCL','Pencacah Lapangan','2026-09-12 08:45:35'),(5,'PENGOLAH','Pengolah','2026-09-12 08:45:35'),(6,'VIEWER','Viewer','2026-09-12 08:45:35');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sampel`
--

DROP TABLE IF EXISTS `sampel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sampel` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `periode_id` int unsigned NOT NULL,
  `sls_id` int unsigned NOT NULL,
  `target_sampel` smallint unsigned NOT NULL DEFAULT '10',
  `muatan_awal` int unsigned DEFAULT NULL COMMENT 'JRT Excel alokasi',
  `hasil_kk` int unsigned DEFAULT NULL COMMENT 'Blok II.1 pemutakhiran',
  `hasil_rt` int unsigned DEFAULT NULL COMMENT 'Blok II.2 pemutakhiran',
  `dokumen_vsen` tinyint(1) NOT NULL DEFAULT '0',
  `peta_ws` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dok_pemutakhiran_status` enum('BELUM','DITERIMA','DIPINJAM') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  `dok_pemutakhiran_waktu` datetime DEFAULT NULL,
  `dok_pemutakhiran_oleh` int unsigned DEFAULT NULL,
  `dok_pemutakhiran_penyerah` int unsigned DEFAULT NULL,
  `peta_status` enum('BELUM','DITERIMA','DIPINJAM') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  `peta_waktu` datetime DEFAULT NULL,
  `peta_oleh` int unsigned DEFAULT NULL,
  `peta_penyerah` int unsigned DEFAULT NULL,
  `catatan_dokumen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sampel` (`periode_id`,`sls_id`),
  KEY `ix_sampel_periode` (`periode_id`),
  KEY `ix_sampel_sls` (`sls_id`),
  KEY `ix_sampel_dok` (`dokumen_vsen`,`peta_ws`),
  KEY `ix_sampel_pemutakhiran_status` (`dok_pemutakhiran_status`),
  KEY `ix_sampel_peta_status` (`peta_status`),
  CONSTRAINT `fk_sampel_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_sampel_sls` FOREIGN KEY (`sls_id`) REFERENCES `sls` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sampel`
--

LOCK TABLES `sampel` WRITE;
/*!40000 ALTER TABLE `sampel` DISABLE KEYS */;
INSERT INTO `sampel` VALUES (1,1,2,10,58,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(2,1,3,10,89,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(3,1,4,10,50,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(4,1,9,10,47,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(5,1,10,10,51,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(6,1,14,10,45,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(7,1,17,10,62,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(8,1,19,10,62,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(9,1,21,10,46,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(10,1,22,10,72,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(11,1,24,10,50,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(12,1,1,10,83,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(13,1,5,10,39,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(14,1,6,10,59,NULL,NULL,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(15,1,7,10,92,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(16,1,8,10,54,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(17,1,11,10,49,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(18,1,12,10,26,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(19,1,13,10,74,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(20,1,15,10,47,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(21,1,16,10,101,100,97,1,1,'2026-09-12 08:45:40','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(22,1,18,10,47,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(23,1,20,10,30,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(24,1,23,10,29,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(25,1,25,10,110,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(26,1,26,10,64,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(27,1,27,10,45,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(28,1,28,10,73,NULL,NULL,0,0,'2026-09-12 08:45:40','2026-09-12 08:45:40','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(29,2,2,10,58,58,55,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(30,2,3,10,89,89,86,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(31,2,4,10,50,50,47,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(32,2,9,10,47,47,44,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(33,2,10,10,51,51,48,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(34,2,14,10,45,45,42,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(35,2,17,10,62,62,59,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(36,2,19,10,62,62,59,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(37,2,21,10,46,46,43,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(38,2,22,10,72,72,69,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(39,2,24,10,50,50,47,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(40,2,1,10,83,83,80,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(41,2,5,10,39,39,36,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(42,2,6,10,59,59,56,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(43,2,7,10,92,92,89,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(44,2,8,10,54,54,51,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(45,2,11,10,49,49,46,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(46,2,12,10,26,26,23,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(47,2,13,10,74,74,71,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(48,2,15,10,47,47,44,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(49,2,16,10,97,97,94,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(50,2,18,10,47,47,44,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(51,2,20,10,30,30,27,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(52,2,23,10,29,29,26,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(53,2,25,10,110,110,107,1,1,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(54,2,26,10,64,64,61,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(55,2,27,10,45,45,42,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(56,2,28,10,73,73,70,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(57,3,2,10,58,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(58,3,3,10,89,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(59,3,4,10,50,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(60,3,9,10,47,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(61,3,10,10,51,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(62,3,14,10,45,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(63,3,17,10,62,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(64,3,19,10,62,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(65,3,21,10,46,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL),(66,3,16,10,97,NULL,NULL,0,0,'2026-09-12 08:45:43','2026-09-12 08:45:43','BELUM',NULL,NULL,NULL,'BELUM',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `sampel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sls`
--

DROP TABLE IF EXISTS `sls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sls` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `kode_full` char(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'prov2+kab2+kec3+desa3+sls4+sub2',
  `prov` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '35',
  `kab` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '09',
  `kec` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desa` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sls` char(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '4 digit, cth 0042',
  `sub` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '2 digit, cth 00',
  `nks` char(5) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'NKS BPS 5 digit, stabil per SLS',
  `desa_id` int unsigned DEFAULT NULL,
  `dusun` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rw` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rt` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_sls` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ketua` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `klasifikasi` tinyint DEFAULT NULL COMMENT '1=Perkotaan, 2=Perdesaan',
  `jml_kk` int unsigned DEFAULT NULL,
  `jml_rt` int unsigned DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sls_nks` (`nks`),
  UNIQUE KEY `kode_full` (`kode_full`),
  KEY `fk_sls_desa` (`desa_id`),
  KEY `ix_sls_kec_desa` (`kec`,`desa`),
  KEY `ix_sls_full` (`kode_full`),
  CONSTRAINT `fk_sls_desa` FOREIGN KEY (`desa_id`) REFERENCES `desa` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ck_sls_full` CHECK (((`kode_full` is null) or regexp_like(`kode_full`,_utf8mb4'^[0-9]{16}$'))),
  CONSTRAINT `ck_sls_nks` CHECK (regexp_like(`nks`,_utf8mb4'^[0-9]{5}$'))
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sls`
--

LOCK TABLES `sls` WRITE;
/*!40000 ALTER TABLE `sls` DISABLE KEYS */;
INSERT INTO `sls` VALUES (1,NULL,'35','09','020','003',NULL,NULL,'50536',NULL,NULL,NULL,NULL,'MENAMPU',NULL,NULL,NULL,83,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(2,NULL,'35','09','020','007',NULL,NULL,'00327',NULL,NULL,NULL,NULL,'TEMBOKREJO',NULL,NULL,NULL,58,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(3,NULL,'35','09','020','008',NULL,NULL,'00407',NULL,NULL,NULL,NULL,'KARANGREJO',NULL,NULL,NULL,89,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(4,NULL,'35','09','030','001',NULL,NULL,'00445',NULL,NULL,NULL,NULL,'MOJOMULYO',NULL,NULL,NULL,50,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(5,NULL,'35','09','040','005',NULL,NULL,'51611',NULL,NULL,NULL,NULL,'DUKUH DEMPOK',NULL,NULL,NULL,39,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(6,NULL,'35','09','050','001',NULL,NULL,'51797',NULL,NULL,NULL,NULL,'SUMBERREJO',NULL,NULL,NULL,59,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(7,NULL,'35','09','050','005',NULL,NULL,'52097',NULL,NULL,NULL,NULL,'PONTANG',NULL,NULL,NULL,92,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(8,NULL,'35','09','060','005',NULL,NULL,'52471',NULL,NULL,NULL,NULL,'SIDODADI',NULL,NULL,NULL,54,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(9,NULL,'35','09','070','002',NULL,NULL,'01356',NULL,NULL,NULL,NULL,'PACE',NULL,NULL,NULL,47,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(10,NULL,'35','09','070','009',NULL,NULL,'01692',NULL,NULL,NULL,NULL,'SIDOMULYO',NULL,NULL,NULL,51,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(11,NULL,'35','09','090','005',NULL,NULL,'53400',NULL,NULL,NULL,NULL,'MUMBULSARI',NULL,NULL,NULL,49,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(12,NULL,'35','09','120','003',NULL,NULL,'54904',NULL,NULL,NULL,NULL,'ROWOTAMTU',NULL,NULL,NULL,26,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(13,NULL,'35','09','130','004',NULL,NULL,'55394',NULL,NULL,NULL,NULL,'BALUNG KULON',NULL,NULL,NULL,74,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(14,NULL,'35','09','140','001',NULL,NULL,'02185',NULL,NULL,NULL,NULL,'SUKORENO',NULL,NULL,NULL,45,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(15,NULL,'35','09','170','002',NULL,NULL,'56326',NULL,NULL,NULL,NULL,'ROWO TENGAH',NULL,NULL,NULL,47,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(16,'3509170002004200','35','09','170','002','0042','00','56361',15,'SADENGAN','014','001','ROWO TENGAH',NULL,1,100,97,1,'2026-09-12 08:45:40','2026-09-12 08:45:42'),(17,NULL,'35','09','180','004',NULL,NULL,'03125',NULL,NULL,NULL,NULL,'SELODAKON',NULL,NULL,NULL,62,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(18,NULL,'35','09','180','007',NULL,NULL,'56901',NULL,NULL,NULL,NULL,'PATEMON',NULL,NULL,NULL,47,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(19,NULL,'35','09','190','009',NULL,NULL,'03376',NULL,NULL,NULL,NULL,'TUGUSARI',NULL,NULL,NULL,62,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(20,NULL,'35','09','200','006',NULL,NULL,'57672',NULL,NULL,NULL,NULL,'SUCI',NULL,NULL,NULL,30,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(21,NULL,'35','09','250','001',NULL,NULL,'04098',NULL,NULL,NULL,NULL,'SUREN',NULL,NULL,NULL,46,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(22,NULL,'35','09','250','002',NULL,NULL,'04167',NULL,NULL,NULL,NULL,'SUMBER SALAK',NULL,NULL,NULL,72,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(23,NULL,'35','09','250','003',NULL,NULL,'58988',NULL,NULL,NULL,NULL,'SUMBER BULUS',NULL,NULL,NULL,29,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(24,NULL,'35','09','260','008',NULL,NULL,'04592',NULL,NULL,NULL,NULL,'PRINGGONDANI',NULL,NULL,NULL,50,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(25,NULL,'35','09','710','002',NULL,NULL,'59905',NULL,NULL,NULL,NULL,'SEMPUSARI',NULL,NULL,NULL,110,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(26,NULL,'35','09','710','007',NULL,NULL,'60488',NULL,NULL,NULL,NULL,'KEBON AGUNG',NULL,NULL,NULL,64,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(27,NULL,'35','09','720','001',NULL,NULL,'60552',NULL,NULL,NULL,NULL,'KERANJINGAN',NULL,NULL,NULL,45,1,'2026-09-12 08:45:40','2026-09-12 08:45:40'),(28,NULL,'35','09','720','006',NULL,NULL,'61110',NULL,NULL,NULL,NULL,'TEGAL GEDE',NULL,NULL,NULL,73,1,'2026-09-12 08:45:40','2026-09-12 08:45:40');
/*!40000 ALTER TABLE `sls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `orang_id` int unsigned DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Argon2id',
  `role_id` tinyint unsigned NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `must_reset` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=wajib ganti password saat login pertama (migrasi Jember3509)',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `ix_users_role` (`role_id`,`is_aktif`),
  KEY `ix_users_orang` (`orang_id`),
  CONSTRAINT `fk_users_orang` FOREIGN KEY (`orang_id`) REFERENCES `orang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'Administrator','admin@bpsjember.go.id','$argon2id$v=19$m=65536,t=4,p=1$aFNld1UvZ2hYd0hwOXBiWg$PVroKkgGI3bNqDKK/pVig58eFfq1vVhyhySUaU7h36M',1,1,1,'2026-09-13 07:38:40','2026-09-12 08:45:36','2026-09-13 07:38:40'),(2,1,'Petugas Dummy 01','dummy01@contoh.go.id','$argon2id$v=19$m=65536,t=4,p=1$YklTbXdGN3hEMlZYRFhYWg$1Amly0CHrsEIQzpORnsTfpqt/EHbpENdRxZ/JCdsBFA',5,1,1,NULL,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(3,2,'Petugas Dummy 02','dummy02@contoh.go.id','$argon2id$v=19$m=65536,t=4,p=1$YklTbXdGN3hEMlZYRFhYWg$1Amly0CHrsEIQzpORnsTfpqt/EHbpENdRxZ/JCdsBFA',5,1,1,NULL,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(4,3,'Petugas Dummy 03','dummy03@contoh.go.id','$argon2id$v=19$m=65536,t=4,p=1$YklTbXdGN3hEMlZYRFhYWg$1Amly0CHrsEIQzpORnsTfpqt/EHbpENdRxZ/JCdsBFA',5,1,1,NULL,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(5,4,'Petugas Dummy 04','dummy04@contoh.go.id','$argon2id$v=19$m=65536,t=4,p=1$YklTbXdGN3hEMlZYRFhYWg$1Amly0CHrsEIQzpORnsTfpqt/EHbpENdRxZ/JCdsBFA',5,1,1,NULL,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(6,5,'Petugas Dummy 05','dummy05@contoh.go.id','$argon2id$v=19$m=65536,t=4,p=1$YklTbXdGN3hEMlZYRFhYWg$1Amly0CHrsEIQzpORnsTfpqt/EHbpENdRxZ/JCdsBFA',5,1,1,NULL,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(7,6,'Petugas Dummy 06','dummy06@contoh.go.id','$argon2id$v=19$m=65536,t=4,p=1$YklTbXdGN3hEMlZYRFhYWg$1Amly0CHrsEIQzpORnsTfpqt/EHbpENdRxZ/JCdsBFA',5,1,1,NULL,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(8,7,'Petugas Dummy 07','dummy07@contoh.go.id','$argon2id$v=19$m=65536,t=4,p=1$YklTbXdGN3hEMlZYRFhYWg$1Amly0CHrsEIQzpORnsTfpqt/EHbpENdRxZ/JCdsBFA',5,1,1,NULL,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(9,8,'Petugas Dummy 08','dummy08@contoh.go.id','$argon2id$v=19$m=65536,t=4,p=1$YklTbXdGN3hEMlZYRFhYWg$1Amly0CHrsEIQzpORnsTfpqt/EHbpENdRxZ/JCdsBFA',5,1,1,NULL,'2026-09-12 08:45:36','2026-09-12 08:45:36'),(10,9,'Petugas PCL Demo','pcl.demo@bpsjember.go.id','$argon2id$v=19$m=65536,t=4,p=1$OU10ck9USGZxSzZkc3NtQg$1hmp6zcu1IF3vzZHp40KcKtnDJXWFTiaRwnt2Skdx94',4,1,0,NULL,'2026-09-12 08:45:43','2026-09-12 08:45:43'),(11,37,'Petugas PML Demo','pml.demo@bpsjember.go.id','$argon2id$v=19$m=65536,t=4,p=1$OU10ck9USGZxSzZkc3NtQg$1hmp6zcu1IF3vzZHp40KcKtnDJXWFTiaRwnt2Skdx94',3,1,0,NULL,'2026-09-12 08:45:43','2026-09-12 08:45:43'),(12,NULL,'Petugas Operator Demo','operator.demo@bpsjember.go.id','$argon2id$v=19$m=65536,t=4,p=1$OU10ck9USGZxSzZkc3NtQg$1hmp6zcu1IF3vzZHp40KcKtnDJXWFTiaRwnt2Skdx94',2,1,0,NULL,'2026-09-12 08:45:43','2026-09-12 08:45:43'),(13,NULL,'Petugas Viewer Demo','viewer.demo@bpsjember.go.id','$argon2id$v=19$m=65536,t=4,p=1$OU10ck9USGZxSzZkc3NtQg$1hmp6zcu1IF3vzZHp40KcKtnDJXWFTiaRwnt2Skdx94',6,1,0,NULL,'2026-09-12 08:45:43','2026-09-12 08:45:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'sipandhalu'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-13 16:05:17
