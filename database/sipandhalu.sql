/*
 Navicat Premium Dump SQL

 Source Server         : Laragon
 Source Server Type    : MySQL
 Source Server Version : 80030 (8.0.30)
 Source Host           : localhost:3306
 Source Schema         : sipandhalu

 Target Server Type    : MySQL
 Target Server Version : 80030 (8.0.30)
 File Encoding         : 65001

 Date: 19/09/2026 15:22:36
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for audit_logs
-- ----------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NULL DEFAULT NULL,
  `aksi` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'CREATE,UPDATE,DELETE,LOGIN,LOGOUT,RESET_PW,IMPORT',
  `tabel_target` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_target` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `before_json` json NULL,
  `after_json` json NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ix_audit_user`(`user_id` ASC, `created_at` ASC) USING BTREE,
  INDEX `ix_audit_tabel`(`tabel_target` ASC, `id_target` ASC) USING BTREE,
  INDEX `ix_audit_waktu`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of audit_logs
-- ----------------------------
INSERT INTO `audit_logs` VALUES (1, 1, 'LOGIN', 'users', '1', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-12 13:20:18');
INSERT INTO `audit_logs` VALUES (2, 1, 'LOGIN', 'users', '1', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-14 12:47:04');
INSERT INTO `audit_logs` VALUES (3, 1, 'LOGOUT', 'users', '1', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-14 12:47:12');
INSERT INTO `audit_logs` VALUES (4, 1, 'LOGIN', 'users', '1', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-14 12:47:17');
INSERT INTO `audit_logs` VALUES (5, 1, 'LOGIN', 'users', '1', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-15 13:50:48');
INSERT INTO `audit_logs` VALUES (6, 1, 'IMPORT', 'sampel_ruta', '1', NULL, '{\"rekap\": {\"total\": 280, \"errors\": [], \"dilewati\": 0, \"terupdate\": 280}, \"periode_id\": 1}', 'cli', 'import_dok_kirim.php', '2026-09-15 14:02:04');
INSERT INTO `audit_logs` VALUES (7, 1, 'LOGIN', 'users', '1', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', '2026-09-19 11:58:23');
INSERT INTO `audit_logs` VALUES (8, 1, 'LOGIN', 'users', '1', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', '', '2026-09-19 12:48:17');
INSERT INTO `audit_logs` VALUES (9, 1, 'LOGIN', 'users', '1', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', '', '2026-09-19 12:48:26');
INSERT INTO `audit_logs` VALUES (10, 1, 'LOGIN', 'users', '1', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', '', '2026-09-19 13:00:49');
INSERT INTO `audit_logs` VALUES (11, 1, 'LOGIN', 'users', '1', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', '', '2026-09-19 13:01:26');
INSERT INTO `audit_logs` VALUES (12, 1, 'UPDATE', 'sampel_ruta', '1', '{\"id\": 1, \"nks\": \"52097\", \"hp_pcl\": null, \"hp_pml\": null, \"pcl_id\": 159, \"pml_id\": 141, \"ttd_sos\": null, \"nama_pcl\": \"Diah Puji Lestari\", \"nama_pml\": \"Wahyu Wijayanti\", \"nama_sls\": \"PONTANG\", \"ttd_ipds\": null, \"nama_desa\": \"PONTANG\", \"sampel_id\": 15, \"catatan_kp\": 0, \"created_at\": \"2026-09-15 13:50:50\", \"periode_id\": 1, \"updated_at\": \"2026-09-15 13:50:50\", \"hp_pengolah\": \"081333334014\", \"pengolah_id\": 2, \"ket_m_sosial\": null, \"no_urut_ruta\": 10, \"catatan_modul\": 0, \"ket_kp_sosial\": null, \"nama_pengolah\": \"Anung Anindhita Pratiwi\", \"ket_m_lapangan\": null, \"ket_m_pengolah\": null, \"nama_kecamatan\": \"AMBULU\", \"status_dokumen\": \"BELUM\", \"status_selesai\": \"BELUM\", \"tgl_pengiriman\": null, \"ket_kp_lapangan\": null, \"ket_kp_pengolah\": null, \"status_transfer_k\": 0, \"status_transfer_kp\": 0, \"uji_petik_pengawas\": null, \"status_transfer_seruti\": 0}', '{\"id\": 1, \"nks\": \"52097\", \"hp_pcl\": null, \"hp_pml\": null, \"pcl_id\": 159, \"pml_id\": 141, \"ttd_sos\": null, \"nama_pcl\": \"Diah Puji Lestari\", \"nama_pml\": \"Wahyu Wijayanti\", \"nama_sls\": \"PONTANG\", \"ttd_ipds\": null, \"nama_desa\": \"PONTANG\", \"sampel_id\": 15, \"catatan_kp\": 0, \"created_at\": \"2026-09-15 13:50:50\", \"periode_id\": 1, \"updated_at\": \"2026-09-19 13:01:26\", \"hp_pengolah\": \"081333334014\", \"pengolah_id\": 2, \"ket_m_sosial\": null, \"no_urut_ruta\": 10, \"catatan_modul\": 0, \"ket_kp_sosial\": null, \"nama_pengolah\": \"Anung Anindhita Pratiwi\", \"ket_m_lapangan\": null, \"ket_m_pengolah\": null, \"nama_kecamatan\": \"AMBULU\", \"status_dokumen\": \"BELUM\", \"status_selesai\": \"BELUM\", \"tgl_pengiriman\": null, \"ket_kp_lapangan\": null, \"ket_kp_pengolah\": null, \"status_transfer_k\": 1, \"status_transfer_kp\": 1, \"uji_petik_pengawas\": null, \"status_transfer_seruti\": 0}', '', '', '2026-09-19 13:01:26');
INSERT INTO `audit_logs` VALUES (13, 1, 'UPDATE', 'sampel_ruta', 'batch_status_transfer_k', NULL, '{\"nks\": \"50536\", \"field\": \"status_transfer_k\", \"value\": 1, \"affected\": 10}', '127.0.0.1', '', '2026-09-19 13:01:26');
INSERT INTO `audit_logs` VALUES (14, 2, 'LOGIN', 'users', '2', NULL, '{\"hasil\": \"sukses\"}', '127.0.0.1', '', '2026-09-19 13:01:35');
INSERT INTO `audit_logs` VALUES (15, 2, 'UPDATE', 'sampel_ruta', '1', '{\"id\": 1, \"nks\": \"52097\", \"hp_pcl\": null, \"hp_pml\": null, \"pcl_id\": 159, \"pml_id\": 141, \"ttd_sos\": null, \"nama_pcl\": \"Diah Puji Lestari\", \"nama_pml\": \"Wahyu Wijayanti\", \"nama_sls\": \"PONTANG\", \"ttd_ipds\": null, \"nama_desa\": \"PONTANG\", \"sampel_id\": 15, \"catatan_kp\": 0, \"created_at\": \"2026-09-15 13:50:50\", \"periode_id\": 1, \"updated_at\": \"2026-09-19 13:01:26\", \"hp_pengolah\": \"081333334014\", \"pengolah_id\": 2, \"ket_m_sosial\": null, \"no_urut_ruta\": 10, \"catatan_modul\": 0, \"ket_kp_sosial\": null, \"nama_pengolah\": \"Anung Anindhita Pratiwi\", \"ket_m_lapangan\": null, \"ket_m_pengolah\": null, \"nama_kecamatan\": \"AMBULU\", \"status_dokumen\": \"BELUM\", \"status_selesai\": \"BELUM\", \"tgl_pengiriman\": null, \"ket_kp_lapangan\": null, \"ket_kp_pengolah\": null, \"status_transfer_k\": 1, \"status_transfer_kp\": 1, \"uji_petik_pengawas\": null, \"status_transfer_seruti\": 0}', '{\"id\": 1, \"nks\": \"52097\", \"hp_pcl\": null, \"hp_pml\": null, \"pcl_id\": 159, \"pml_id\": 141, \"ttd_sos\": null, \"nama_pcl\": \"Diah Puji Lestari\", \"nama_pml\": \"Wahyu Wijayanti\", \"nama_sls\": \"PONTANG\", \"ttd_ipds\": null, \"nama_desa\": \"PONTANG\", \"sampel_id\": 15, \"catatan_kp\": 0, \"created_at\": \"2026-09-15 13:50:50\", \"periode_id\": 1, \"updated_at\": \"2026-09-19 13:01:26\", \"hp_pengolah\": \"081333334014\", \"pengolah_id\": 2, \"ket_m_sosial\": null, \"no_urut_ruta\": 10, \"catatan_modul\": 0, \"ket_kp_sosial\": null, \"nama_pengolah\": \"Anung Anindhita Pratiwi\", \"ket_m_lapangan\": null, \"ket_m_pengolah\": null, \"nama_kecamatan\": \"AMBULU\", \"status_dokumen\": \"BELUM\", \"status_selesai\": \"BELUM\", \"tgl_pengiriman\": null, \"ket_kp_lapangan\": null, \"ket_kp_pengolah\": null, \"status_transfer_k\": 1, \"status_transfer_kp\": 1, \"uji_petik_pengawas\": null, \"status_transfer_seruti\": 0}', '', '', '2026-09-19 13:01:35');
INSERT INTO `audit_logs` VALUES (16, 2, 'UPDATE', 'sampel_ruta', 'batch_status_transfer_kp', NULL, '{\"nks\": \"50536\", \"field\": \"status_transfer_kp\", \"value\": 1, \"affected\": 10}', '127.0.0.1', '', '2026-09-19 13:01:35');

-- ----------------------------
-- Table structure for desa
-- ----------------------------
DROP TABLE IF EXISTS `desa`;
CREATE TABLE `desa`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `kecamatan_kode` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kode kecamatan 3 digit, cth 170 (cek eksistensi di Service)',
  `kode` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kode desa 3 digit',
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_full` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (concat(_utf8mb4'3509',`kecamatan_kode`,`kode`)) STORED NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_desa`(`kecamatan_kode` ASC, `kode` ASC) USING BTREE,
  INDEX `ix_desa_kec`(`kecamatan_kode` ASC) USING BTREE,
  INDEX `ix_desa_nama`(`nama` ASC) USING BTREE,
  CONSTRAINT `ck_desa_kec` CHECK (regexp_like(`kecamatan_kode`,_utf8mb4'^[0-9]{3}$')),
  CONSTRAINT `ck_desa_kode` CHECK (regexp_like(`kode`,_utf8mb4'^[0-9]{3}$'))
) ENGINE = InnoDB AUTO_INCREMENT = 85 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of desa
-- ----------------------------
INSERT INTO `desa` VALUES (1, '020', '003', 'MENAMPU', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (2, '020', '007', 'TEMBOKREJO', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (3, '020', '008', 'KARANGREJO', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (4, '030', '001', 'MOJOMULYO', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (5, '040', '005', 'DUKUH DEMPOK', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (6, '050', '001', 'SUMBERREJO', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (7, '050', '005', 'PONTANG', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (8, '060', '005', 'SIDODADI', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (9, '070', '002', 'PACE', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (10, '070', '009', 'SIDOMULYO', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (11, '090', '005', 'MUMBULSARI', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (12, '120', '003', 'ROWOTAMTU', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (13, '130', '004', 'BALUNG KULON', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (14, '140', '001', 'SUKORENO', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (15, '170', '002', 'ROWO TENGAH', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (16, '180', '004', 'SELODAKON', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (17, '180', '007', 'PATEMON', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (18, '190', '009', 'TUGUSARI', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (19, '200', '006', 'SUCI', DEFAULT, '2026-09-12 12:55:31');
INSERT INTO `desa` VALUES (20, '250', '001', 'SUREN', DEFAULT, '2026-09-12 12:55:32');
INSERT INTO `desa` VALUES (21, '250', '002', 'SUMBER SALAK', DEFAULT, '2026-09-12 12:55:32');
INSERT INTO `desa` VALUES (22, '250', '003', 'SUMBER BULUS', DEFAULT, '2026-09-12 12:55:32');
INSERT INTO `desa` VALUES (23, '260', '008', 'PRINGGONDANI', DEFAULT, '2026-09-12 12:55:32');
INSERT INTO `desa` VALUES (24, '710', '002', 'SEMPUSARI', DEFAULT, '2026-09-12 12:55:32');
INSERT INTO `desa` VALUES (25, '710', '007', 'KEBON AGUNG', DEFAULT, '2026-09-12 12:55:32');
INSERT INTO `desa` VALUES (26, '720', '001', 'KERANJINGAN', DEFAULT, '2026-09-12 12:55:32');
INSERT INTO `desa` VALUES (27, '720', '006', 'TEGAL GEDE', DEFAULT, '2026-09-12 12:55:32');

-- ----------------------------
-- Table structure for jadwal_pengawas_pengolahan
-- ----------------------------
DROP TABLE IF EXISTS `jadwal_pengawas_pengolahan`;
CREATE TABLE `jadwal_pengawas_pengolahan`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `periode_id` int UNSIGNED NOT NULL COMMENT 'FK periode (Susenas S2 2026)',
  `tanggal` date NOT NULL,
  `orang_id` int UNSIGNED NULL DEFAULT NULL COMMENT 'FK orang (NULL saat LIBUR)',
  `nama_pengawas` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'snapshot nama saat dijadwalkan',
  `hari` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Senin..Minggu (id)',
  `status` enum('TUGAS','LIBUR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TUGAS',
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_jadwal_periode_tanggal`(`periode_id` ASC, `tanggal` ASC) USING BTREE,
  INDEX `ix_jadwal_tanggal`(`tanggal` ASC) USING BTREE,
  INDEX `ix_jadwal_orang`(`orang_id` ASC) USING BTREE,
  INDEX `ix_jadwal_status`(`status` ASC) USING BTREE,
  CONSTRAINT `fk_jadwal_orang` FOREIGN KEY (`orang_id`) REFERENCES `orang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_jadwal_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 27 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of jadwal_pengawas_pengolahan
-- ----------------------------
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (1, 1, '2026-09-15', 181, 'Arumita Hertriesa', 'Selasa', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (2, 1, '2026-09-16', 141, 'Wahyu Wijayanti', 'Rabu', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (3, 1, '2026-09-17', 183, 'Nanang Pamungkas', 'Kamis', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (4, 1, '2026-09-18', 184, 'Qudrat Jufrian Bharata', 'Jumat', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (5, 1, '2026-09-19', NULL, '-', 'Sabtu', 'LIBUR', 'Libur akhir pekan', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (6, 1, '2026-09-20', NULL, '-', 'Minggu', 'LIBUR', 'Libur akhir pekan', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (7, 1, '2026-09-21', 185, 'Silvie Kristya Ardearista', 'Senin', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (8, 1, '2026-09-22', 181, 'Arumita Hertriesa', 'Selasa', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (9, 1, '2026-09-23', 141, 'Wahyu Wijayanti', 'Rabu', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (10, 1, '2026-09-24', 183, 'Nanang Pamungkas', 'Kamis', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (11, 1, '2026-09-25', 184, 'Qudrat Jufrian Bharata', 'Jumat', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (12, 1, '2026-09-26', NULL, '-', 'Sabtu', 'LIBUR', 'Libur akhir pekan', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (13, 1, '2026-09-27', NULL, '-', 'Minggu', 'LIBUR', 'Libur akhir pekan', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (14, 1, '2026-09-28', 185, 'Silvie Kristya Ardearista', 'Senin', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (15, 1, '2026-09-29', 181, 'Arumita Hertriesa', 'Selasa', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (16, 1, '2026-09-30', 141, 'Wahyu Wijayanti', 'Rabu', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (17, 1, '2026-10-01', 183, 'Nanang Pamungkas', 'Kamis', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (18, 1, '2026-10-02', 184, 'Qudrat Jufrian Bharata', 'Jumat', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (19, 1, '2026-10-03', 185, 'Silvie Kristya Ardearista', 'Sabtu', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (20, 1, '2026-10-04', 181, 'Arumita Hertriesa', 'Minggu', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (21, 1, '2026-10-05', 141, 'Wahyu Wijayanti', 'Senin', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (22, 1, '2026-10-06', 183, 'Nanang Pamungkas', 'Selasa', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (23, 1, '2026-10-07', 184, 'Qudrat Jufrian Bharata', 'Rabu', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (24, 1, '2026-10-08', 185, 'Silvie Kristya Ardearista', 'Kamis', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (25, 1, '2026-10-09', 181, 'Arumita Hertriesa', 'Jumat', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `jadwal_pengawas_pengolahan` VALUES (26, 1, '2026-10-10', 141, 'Wahyu Wijayanti', 'Sabtu', 'TUGAS', 'Piket pengolahan Susenas S2 2026', '2026-09-19 14:09:46', '2026-09-19 14:09:46');

-- ----------------------------
-- Table structure for kecamatan
-- ----------------------------
DROP TABLE IF EXISTS `kecamatan`;
CREATE TABLE `kecamatan`  (
  `kode` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kode kecamatan 3 digit, cth 170',
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`kode`) USING BTREE,
  UNIQUE INDEX `uq_kec_nama`(`nama` ASC) USING BTREE,
  CONSTRAINT `ck_kec_kode` CHECK (regexp_like(`kode`,_utf8mb4'^[0-9]{3}$'))
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of kecamatan
-- ----------------------------
INSERT INTO `kecamatan` VALUES ('020', 'GUMUKMAS', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('030', 'PUGER', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('040', 'WULUHAN', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('050', 'AMBULU', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('060', 'TEMPUREJO', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('070', 'SILO', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('090', 'MUMBULSARI', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('120', 'RAMBIPUJI', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('130', 'BALUNG', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('140', 'UMBULSARI', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('170', 'SUMBERBARU', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('180', 'TANGGUL', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('190', 'BANGSALSARI', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('200', 'PANTI', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('250', 'LEDOKOMBO', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('260', 'SUMBERJAMBE', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('710', 'KALIWATES', '2026-09-12 12:55:31');
INSERT INTO `kecamatan` VALUES ('720', 'SUMBERSARI', '2026-09-12 12:55:31');

-- ----------------------------
-- Table structure for orang
-- ----------------------------
DROP TABLE IF EXISTS `orang`;
CREATE TABLE `orang`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'kanonik Title Case, TRIM',
  `nama_normalized` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci GENERATED ALWAYS AS (lower(trim(`nama`))) STORED NULL,
  `no_hp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `role_id` tinyint UNSIGNED NULL DEFAULT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_orang_nama`(`nama_normalized` ASC) USING BTREE,
  INDEX `ix_orang_aktif`(`is_aktif` ASC) USING BTREE,
  INDEX `ix_orang_nama`(`nama` ASC) USING BTREE,
  INDEX `ix_orang_role`(`role_id` ASC) USING BTREE,
  FULLTEXT INDEX `ft_orang_nama`(`nama`),
  CONSTRAINT `fk_orang_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 186 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of orang
-- ----------------------------
INSERT INTO `orang` VALUES (1, 'Aminatus Sholeha', DEFAULT, '085816765962', 'aminatuss182002@gmail.com', 5, NULL, 1, '2026-09-12 12:55:31', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (2, 'Anung Anindhita Pratiwi', DEFAULT, '081333334014', 'anunganindhitap@gmail.com', 5, NULL, 1, '2026-09-12 12:55:31', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (3, 'Putri Salsabhila Fahira', DEFAULT, '0895808623060', 'putrisalsabhilafahira10@gmail.com', 5, NULL, 1, '2026-09-12 12:55:31', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (4, 'Iffa Dzakiyya', DEFAULT, '081515780248', 'bpsbpsiffa36@gmail.com', 5, NULL, 1, '2026-09-12 12:55:31', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (5, 'Prasistiwi Andrianingtyas', DEFAULT, '082334919235', 'prasistiwi@gmail.com', 5, NULL, 1, '2026-09-12 12:55:31', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (6, 'Laviana Ika Putrisari', DEFAULT, '085252201043', 'lavianaikarumby@gmail.com', 5, NULL, 1, '2026-09-12 12:55:31', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (7, 'Nur Ida Suryandari', DEFAULT, '089682170216', 'nidasuryandari@gmail.com', 5, NULL, 1, '2026-09-12 12:55:31', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (8, 'Astri Widarianti', DEFAULT, '081216986675', 'a.widarianti@gmail.com', 5, NULL, 1, '2026-09-12 12:55:31', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (9, 'Pcl Dummy 01', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (10, 'Pcl Dummy 02', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (11, 'Pcl Dummy 03', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (12, 'Pcl Dummy 04', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (13, 'Pcl Dummy 05', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (14, 'Pcl Dummy 06', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (15, 'Pcl Dummy 07', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (16, 'Pcl Dummy 08', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (17, 'Pcl Dummy 09', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (18, 'Pcl Dummy 10', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (19, 'Pcl Dummy 11', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (20, 'Pcl Dummy 12', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (21, 'Pcl Dummy 13', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (22, 'Pcl Dummy 14', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (23, 'Pcl Dummy 15', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (24, 'Pcl Dummy 16', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (25, 'Pcl Dummy 17', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (26, 'Pcl Dummy 18', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (27, 'Pcl Dummy 19', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (28, 'Pcl Dummy 20', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (29, 'Pcl Dummy 21', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (30, 'Pcl Dummy 22', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (31, 'Pcl Dummy 23', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (32, 'Pcl Dummy 24', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (33, 'Pcl Dummy 25', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (34, 'Pcl Dummy 26', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (35, 'Pcl Dummy 27', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (36, 'Pcl Dummy 28', DEFAULT, NULL, NULL, 4, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (37, 'Pml Dummy 01', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (38, 'Pml Dummy 02', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (39, 'Pml Dummy 03', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (40, 'Pml Dummy 04', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (41, 'Pml Dummy 05', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (42, 'Pml Dummy 06', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (43, 'Pml Dummy 07', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (44, 'Pml Dummy 08', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (45, 'Pml Dummy 09', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (46, 'Pml Dummy 10', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (47, 'Pml Dummy 11', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (48, 'Pml Dummy 12', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (49, 'Pml Dummy 13', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (50, 'Pml Dummy 14', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (51, 'Pml Dummy 15', DEFAULT, NULL, NULL, 3, NULL, 0, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (138, 'Junaidi Ari Siswanto', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (139, 'Endy Setiobudi', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (140, 'Puji Hidayatus Sholikhah', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (141, 'Wahyu Wijayanti', DEFAULT, NULL, NULL, 19, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 14:09:46');
INSERT INTO `orang` VALUES (142, 'Hajar Lutfi', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (143, 'Ratna Wijayanti', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (144, 'Diyan Kasihati', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (145, 'Mohamad Aripin', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (146, 'Dewi Sunyi Apriani', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (147, 'Eka Wijaya', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (148, 'Ike Noorhayati', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (149, 'Hery Yahman', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (150, 'Husnul Chotimah', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (151, 'Nur Hidayat', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (152, 'Rizqi Elviah', DEFAULT, NULL, NULL, 3, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (153, 'Denik Vinawati', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (154, 'Aprilia Tri Wahyuni', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (155, 'Fatihatul Jannah', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (156, 'Sri Bekti Ajeng Pratiwi', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (157, 'Alfiah', DEFAULT, '082319281750', NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (158, 'Muhammad Fahrul Rozi', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (159, 'Diah Puji Lestari', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (160, 'Aprilia Panca Wage Yanti', DEFAULT, '085233287334', NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (161, 'Zuhrotul Baiti', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (162, 'Dwi Ayuningtiyas', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (163, 'Rini Watiningsih', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (164, 'Muji Sholeh', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (165, 'Endang Mulyani', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (166, 'Ike Winarningsih', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (167, 'Septian', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (168, 'Iis Nur Sholawatin', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (169, 'Ratna Fitri Arianti', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (170, 'Syarifah Fajarwati', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (171, 'Indana Bintan Zakiyyah', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (172, 'Vindy Dwi Ristanti', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (173, 'Ika Safitri', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (174, 'Eva Lusiana', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (175, 'Rizqiyatul Khoirot', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (176, 'Ferida Budiarti', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (177, 'Ida Sopia', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (178, 'Happy Yulia Rahmawati', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (179, 'Suryaningsih', DEFAULT, NULL, NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (180, 'Aris Mawati', DEFAULT, '085233872667', NULL, 4, NULL, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `orang` VALUES (181, 'Arumita Hertriesa', DEFAULT, NULL, NULL, 19, NULL, 1, '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `orang` VALUES (183, 'Nanang Pamungkas', DEFAULT, NULL, NULL, 19, NULL, 1, '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `orang` VALUES (184, 'Qudrat Jufrian Bharata', DEFAULT, NULL, NULL, 19, NULL, 1, '2026-09-19 14:09:46', '2026-09-19 14:09:46');
INSERT INTO `orang` VALUES (185, 'Silvie Kristya Ardearista', DEFAULT, NULL, NULL, 19, NULL, 1, '2026-09-19 14:09:46', '2026-09-19 14:09:46');

-- ----------------------------
-- Table structure for orang_alias
-- ----------------------------
DROP TABLE IF EXISTS `orang_alias`;
CREATE TABLE `orang_alias`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `orang_id` int UNSIGNED NOT NULL,
  `alias_normalized` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'LOWER(TRIM(varian))',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_alias`(`alias_normalized` ASC) USING BTREE,
  INDEX `ix_alias_orang`(`orang_id` ASC) USING BTREE,
  CONSTRAINT `fk_alias_orang` FOREIGN KEY (`orang_id`) REFERENCES `orang` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 30 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of orang_alias
-- ----------------------------
INSERT INTO `orang_alias` VALUES (1, 2, 'anung anindhita p', '2026-09-12 12:55:31');
INSERT INTO `orang_alias` VALUES (2, 3, 'putri salsabhila fahira', '2026-09-12 12:55:31');
INSERT INTO `orang_alias` VALUES (3, 4, 'iffa dzakiyya khairunnisa', '2026-09-12 12:55:31');
INSERT INTO `orang_alias` VALUES (4, 5, 'prasistiwi', '2026-09-12 12:55:31');
INSERT INTO `orang_alias` VALUES (5, 8, 'astri widarianti', '2026-09-12 12:55:31');

-- ----------------------------
-- Table structure for peminjaman_dokumen
-- ----------------------------
DROP TABLE IF EXISTS `peminjaman_dokumen`;
CREATE TABLE `peminjaman_dokumen`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sampel_id` int UNSIGNED NOT NULL,
  `jenis_dok` enum('SEMUA','PEMUTAKHIRAN','PETA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SEMUA',
  `peminjam_id` int UNSIGNED NOT NULL COMMENT 'FK orang',
  `peminjam_peran` enum('PCL','PML','PENGOLAH','LAINNYA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_pinjam` datetime NOT NULL,
  `alasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `operator_pinjam_id` int UNSIGNED NOT NULL COMMENT 'FK users',
  `waktu_kembali` datetime NULL DEFAULT NULL,
  `operator_kembali_id` int UNSIGNED NULL DEFAULT NULL COMMENT 'FK users',
  `catatan_kembali` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('DIPINJAM','DIKEMBALIKAN') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DIPINJAM',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_pinjam_orang`(`peminjam_id` ASC) USING BTREE,
  INDEX `fk_pinjam_op`(`operator_pinjam_id` ASC) USING BTREE,
  INDEX `ix_pinjam_sampel`(`sampel_id` ASC) USING BTREE,
  INDEX `ix_pinjam_status`(`status` ASC) USING BTREE,
  INDEX `ix_pinjam_waktu`(`waktu_pinjam` ASC) USING BTREE,
  CONSTRAINT `fk_pinjam_op` FOREIGN KEY (`operator_pinjam_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pinjam_orang` FOREIGN KEY (`peminjam_id`) REFERENCES `orang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pinjam_sampel` FOREIGN KEY (`sampel_id`) REFERENCES `sampel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of peminjaman_dokumen
-- ----------------------------

-- ----------------------------
-- Table structure for penugasan
-- ----------------------------
DROP TABLE IF EXISTS `penugasan`;
CREATE TABLE `penugasan`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sampel_id` int UNSIGNED NOT NULL,
  `pcl_id` int UNSIGNED NOT NULL,
  `pml_id` int UNSIGNED NOT NULL,
  `pengolah_id` int UNSIGNED NOT NULL,
  `status` enum('DRAFT','AKTIF','SELESAI','BATAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AKTIF',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_tugas_sampel`(`sampel_id` ASC) USING BTREE,
  INDEX `ix_tugas_pcl`(`pcl_id` ASC) USING BTREE,
  INDEX `ix_tugas_pml`(`pml_id` ASC) USING BTREE,
  INDEX `ix_tugas_pengolah`(`pengolah_id` ASC) USING BTREE,
  CONSTRAINT `fk_tugas_pcl` FOREIGN KEY (`pcl_id`) REFERENCES `orang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tugas_pengolah` FOREIGN KEY (`pengolah_id`) REFERENCES `orang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tugas_pml` FOREIGN KEY (`pml_id`) REFERENCES `orang` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tugas_sampel` FOREIGN KEY (`sampel_id`) REFERENCES `sampel` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 255 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of penugasan
-- ----------------------------
INSERT INTO `penugasan` VALUES (1, 1, 154, 138, 2, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (2, 2, 155, 139, 5, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (3, 3, 156, 139, 5, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (4, 4, 161, 143, 2, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (5, 5, 162, 143, 5, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (6, 6, 166, 144, 3, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (7, 7, 169, 146, 4, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (8, 8, 171, 147, 1, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (9, 9, 173, 148, 5, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (10, 10, 174, 148, 5, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (11, 11, 176, 149, 3, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (12, 12, 153, 138, 2, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (13, 13, 157, 140, 1, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (14, 14, 158, 141, 2, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (15, 15, 159, 141, 2, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (16, 16, 160, 142, 1, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (17, 17, 163, 142, 4, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (18, 18, 164, 140, 6, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (19, 19, 165, 144, 1, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (20, 20, 167, 145, 3, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (21, 21, 168, 145, 6, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (22, 22, 170, 146, 6, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (23, 23, 172, 147, 3, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (24, 24, 175, 149, 4, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (25, 25, 177, 150, 4, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (26, 26, 178, 150, 8, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (27, 27, 179, 151, 7, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (28, 28, 180, 152, 6, 'AKTIF', '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (29, 29, 14, 44, 4, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (30, 30, 15, 45, 5, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (31, 31, 16, 46, 6, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (32, 32, 17, 47, 7, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (33, 33, 18, 48, 8, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (34, 34, 19, 49, 1, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (35, 35, 20, 50, 2, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (36, 36, 21, 51, 3, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (37, 37, 22, 37, 4, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (38, 38, 23, 38, 5, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (39, 39, 24, 39, 6, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (40, 40, 25, 40, 7, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (41, 41, 26, 41, 8, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (42, 42, 27, 42, 1, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (43, 43, 28, 43, 2, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (44, 44, 29, 44, 3, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (45, 45, 30, 45, 4, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (46, 46, 31, 46, 5, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (47, 47, 32, 47, 6, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (48, 48, 33, 48, 7, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (49, 49, 34, 49, 8, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (50, 50, 35, 50, 1, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (51, 51, 36, 51, 2, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (52, 52, 9, 37, 3, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (53, 53, 10, 38, 4, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (54, 54, 11, 39, 5, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (55, 55, 12, 40, 6, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (56, 56, 13, 41, 7, 'SELESAI', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (57, 57, 9, 37, 1, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (58, 58, 10, 38, 2, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (59, 59, 11, 39, 3, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (60, 60, 12, 40, 4, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (61, 61, 13, 41, 5, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (62, 62, 14, 42, 6, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (63, 63, 15, 43, 7, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (64, 64, 16, 44, 8, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (65, 65, 17, 45, 1, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (66, 66, 18, 46, 2, 'DRAFT', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `penugasan` VALUES (227, 200, 153, 138, 2, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (228, 201, 154, 138, 2, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (229, 202, 155, 139, 5, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (230, 203, 156, 139, 5, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (231, 204, 157, 140, 1, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (232, 205, 158, 141, 2, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (233, 206, 159, 141, 2, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (234, 207, 160, 142, 1, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (235, 208, 161, 143, 2, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (236, 209, 162, 143, 5, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (237, 210, 163, 142, 4, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (238, 211, 164, 140, 6, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (239, 212, 165, 144, 1, 'AKTIF', '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `penugasan` VALUES (240, 213, 166, 144, 3, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (241, 214, 167, 145, 3, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (242, 215, 168, 145, 6, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (243, 216, 169, 146, 4, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (244, 217, 170, 146, 6, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (245, 218, 171, 147, 1, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (246, 219, 172, 147, 3, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (247, 220, 173, 148, 5, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (248, 221, 174, 148, 5, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (249, 222, 175, 149, 4, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (250, 223, 176, 149, 3, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (251, 224, 177, 150, 4, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (252, 225, 178, 150, 8, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (253, 226, 179, 151, 7, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `penugasan` VALUES (254, 227, 180, 152, 6, 'AKTIF', '2026-09-19 12:46:52', '2026-09-19 12:46:52');

-- ----------------------------
-- Table structure for periode
-- ----------------------------
DROP TABLE IF EXISTS `periode`;
CREATE TABLE `periode`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `tahun` smallint UNSIGNED NOT NULL,
  `jenis` enum('SERUTI_Q1','SERUTI_Q2','SERUTI_Q3','SERUTI_Q4','SUSENAS_S1','SUSENAS_S2') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl_mulai` date NULL DEFAULT NULL,
  `tgl_selesai` date NULL DEFAULT NULL,
  `status` enum('DRAFT','AKTIF','TUTUP') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_periode`(`tahun` ASC, `jenis` ASC) USING BTREE,
  INDEX `ix_periode_status`(`status` ASC, `tahun` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of periode
-- ----------------------------
INSERT INTO `periode` VALUES (1, 2026, 'SUSENAS_S2', '2026-S2 Susenas September', NULL, NULL, 'AKTIF', NULL, '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `periode` VALUES (2, 2026, 'SUSENAS_S1', '2026-S1 Susenas Maret', '2026-03-01', '2026-03-31', 'TUTUP', 'Periode DUMMY (TUTUP): demo histori + rotasi petugas.', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `periode` VALUES (3, 2026, 'SERUTI_Q1', '2026-Q1 Seruti Triwulan I', NULL, NULL, 'DRAFT', 'Periode DUMMY (DRAFT): demo SLS berulang + penugasan baru.', '2026-09-12 12:55:32', '2026-09-12 12:55:32');
INSERT INTO `periode` VALUES (8, 2026, 'SERUTI_Q3', '2026-Q3 Seruti Triwulan III', '2026-07-01', '2026-09-30', 'AKTIF', 'Master wilayah & penugasan Seruti Triwulan III 2026 (integrasi Susenas S2 2026)', '2026-09-19 12:46:51', '2026-09-19 12:46:51');

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `id` tinyint UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_roles_code`(`code` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 50 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, 'ADMIN', 'Administrator', '2026-09-12 12:55:31');
INSERT INTO `roles` VALUES (2, 'OPERATOR', 'Operator Pengolahan', '2026-09-12 12:55:31');
INSERT INTO `roles` VALUES (3, 'PML', 'Pengawas Lapangan', '2026-09-12 12:55:31');
INSERT INTO `roles` VALUES (4, 'PCL', 'Pencacah Lapangan', '2026-09-12 12:55:31');
INSERT INTO `roles` VALUES (5, 'PENGOLAH', 'Pengolah Data', '2026-09-12 12:55:31');
INSERT INTO `roles` VALUES (6, 'VIEWER', 'Viewer', '2026-09-12 12:55:31');
INSERT INTO `roles` VALUES (19, 'PENGAWAS_OLAH', 'Pengawas Pengolahan', '2026-09-15 14:25:13');
INSERT INTO `roles` VALUES (20, 'SM_SOSIAL', 'SM Tim Statistik Sosial', '2026-09-15 14:25:13');
INSERT INTO `roles` VALUES (21, 'SM_PLS', 'SM Tim Pengolahan & Layanan Statistik', '2026-09-15 14:25:13');

-- ----------------------------
-- Table structure for sampel
-- ----------------------------
DROP TABLE IF EXISTS `sampel`;
CREATE TABLE `sampel`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `periode_id` int UNSIGNED NOT NULL,
  `sls_id` int UNSIGNED NOT NULL,
  `target_sampel` smallint UNSIGNED NOT NULL DEFAULT 10,
  `muatan_awal` int UNSIGNED NULL DEFAULT NULL COMMENT 'JRT Excel alokasi',
  `hasil_kk` int UNSIGNED NULL DEFAULT NULL COMMENT 'Blok II.1 pemutakhiran',
  `hasil_rt` int UNSIGNED NULL DEFAULT NULL COMMENT 'Blok II.2 pemutakhiran',
  `dokumen_vsen` tinyint(1) NOT NULL DEFAULT 0,
  `peta_ws` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dok_pemutakhiran_status` enum('BELUM','DITERIMA','DIPINJAM') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  `dok_pemutakhiran_waktu` datetime NULL DEFAULT NULL,
  `dok_pemutakhiran_oleh` int UNSIGNED NULL DEFAULT NULL,
  `dok_pemutakhiran_penyerah` int UNSIGNED NULL DEFAULT NULL,
  `peta_status` enum('BELUM','DITERIMA','DIPINJAM') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  `peta_waktu` datetime NULL DEFAULT NULL,
  `peta_oleh` int UNSIGNED NULL DEFAULT NULL,
  `peta_penyerah` int UNSIGNED NULL DEFAULT NULL,
  `catatan_dokumen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_sampel`(`periode_id` ASC, `sls_id` ASC) USING BTREE,
  INDEX `ix_sampel_periode`(`periode_id` ASC) USING BTREE,
  INDEX `ix_sampel_sls`(`sls_id` ASC) USING BTREE,
  INDEX `ix_sampel_dok`(`dokumen_vsen` ASC, `peta_ws` ASC) USING BTREE,
  INDEX `ix_sampel_pemutakhiran_status`(`dok_pemutakhiran_status` ASC) USING BTREE,
  INDEX `ix_sampel_peta_status`(`peta_status` ASC) USING BTREE,
  CONSTRAINT `fk_sampel_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_sampel_sls` FOREIGN KEY (`sls_id`) REFERENCES `sls` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 228 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sampel
-- ----------------------------
INSERT INTO `sampel` VALUES (1, 1, 2, 10, 58, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (2, 1, 3, 10, 89, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (3, 1, 4, 10, 50, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (4, 1, 9, 10, 47, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (5, 1, 10, 10, 51, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (6, 1, 14, 10, 45, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (7, 1, 17, 10, 62, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (8, 1, 19, 10, 62, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (9, 1, 21, 10, 46, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (10, 1, 22, 10, 72, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (11, 1, 24, 10, 50, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (12, 1, 1, 10, 83, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (13, 1, 5, 10, 39, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (14, 1, 6, 10, 59, NULL, NULL, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (15, 1, 7, 10, 92, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (16, 1, 8, 10, 54, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (17, 1, 11, 10, 49, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (18, 1, 12, 10, 26, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (19, 1, 13, 10, 74, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (20, 1, 15, 10, 47, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (21, 1, 16, 10, 101, 100, 97, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (22, 1, 18, 10, 47, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (23, 1, 20, 10, 30, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (24, 1, 23, 10, 29, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (25, 1, 25, 10, 110, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (26, 1, 26, 10, 64, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (27, 1, 27, 10, 45, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (28, 1, 28, 10, 73, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (29, 2, 2, 10, 58, 58, 55, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (30, 2, 3, 10, 89, 89, 86, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (31, 2, 4, 10, 50, 50, 47, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (32, 2, 9, 10, 47, 47, 44, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (33, 2, 10, 10, 51, 51, 48, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (34, 2, 14, 10, 45, 45, 42, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (35, 2, 17, 10, 62, 62, 59, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (36, 2, 19, 10, 62, 62, 59, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (37, 2, 21, 10, 46, 46, 43, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (38, 2, 22, 10, 72, 72, 69, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (39, 2, 24, 10, 50, 50, 47, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (40, 2, 1, 10, 83, 83, 80, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (41, 2, 5, 10, 39, 39, 36, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (42, 2, 6, 10, 59, 59, 56, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (43, 2, 7, 10, 92, 92, 89, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (44, 2, 8, 10, 54, 54, 51, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (45, 2, 11, 10, 49, 49, 46, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (46, 2, 12, 10, 26, 26, 23, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (47, 2, 13, 10, 74, 74, 71, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (48, 2, 15, 10, 47, 47, 44, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (49, 2, 16, 10, 97, 97, 94, 1, 1, '2026-09-12 12:55:32', '2026-09-14 12:36:13', 'DITERIMA', '2026-09-12 12:56:27', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (50, 2, 18, 10, 47, 47, 44, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (51, 2, 20, 10, 30, 30, 27, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (52, 2, 23, 10, 29, 29, 26, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (53, 2, 25, 10, 110, 110, 107, 1, 1, '2026-09-12 12:55:32', '2026-09-12 15:33:03', 'DITERIMA', '2026-09-12 12:55:32', NULL, NULL, 'DITERIMA', '2026-09-12 15:33:03', NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (54, 2, 26, 10, 64, 64, 61, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (55, 2, 27, 10, 45, 45, 42, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (56, 2, 28, 10, 73, 73, 70, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (57, 3, 2, 10, 58, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (58, 3, 3, 10, 89, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (59, 3, 4, 10, 50, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (60, 3, 9, 10, 47, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (61, 3, 10, 10, 51, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (62, 3, 14, 10, 45, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (63, 3, 17, 10, 62, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (64, 3, 19, 10, 62, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (65, 3, 21, 10, 46, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-12 12:55:32', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (66, 3, 16, 10, 97, NULL, NULL, 0, 0, '2026-09-12 12:55:32', '2026-09-14 12:36:13', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (200, 8, 1, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (201, 8, 2, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (202, 8, 3, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (203, 8, 4, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (204, 8, 5, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (205, 8, 6, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (206, 8, 7, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (207, 8, 8, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (208, 8, 9, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (209, 8, 10, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (210, 8, 11, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (211, 8, 12, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (212, 8, 13, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (213, 8, 14, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:51', '2026-09-19 12:46:51', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (214, 8, 15, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (215, 8, 16, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (216, 8, 17, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (217, 8, 18, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (218, 8, 19, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (219, 8, 20, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (220, 8, 21, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (221, 8, 22, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (222, 8, 23, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (223, 8, 24, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (224, 8, 25, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (225, 8, 26, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (226, 8, 27, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);
INSERT INTO `sampel` VALUES (227, 8, 28, 10, 50, NULL, NULL, 1, 1, '2026-09-19 12:46:52', '2026-09-19 12:46:52', 'BELUM', NULL, NULL, NULL, 'BELUM', NULL, NULL, NULL, NULL);

-- ----------------------------
-- Table structure for sampel_ruta
-- ----------------------------
DROP TABLE IF EXISTS `sampel_ruta`;
CREATE TABLE `sampel_ruta`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sampel_id` int UNSIGNED NOT NULL,
  `no_urut_ruta` tinyint UNSIGNED NOT NULL COMMENT '1 s.d. 10',
  `status_dokumen` enum('BELUM','ADA') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  `status_selesai` enum('BELUM','SUDAH') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  `status_transfer_k` tinyint(1) NOT NULL DEFAULT 0,
  `status_transfer_kp` tinyint(1) NOT NULL DEFAULT 0,
  `status_transfer_seruti` tinyint(1) NOT NULL DEFAULT 0,
  `catatan_modul` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Ya, 0=Tidak',
  `ket_m_pengolah` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `ket_m_lapangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `ket_m_sosial` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `uji_petik_pengawas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `catatan_kp` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Ya, 0=Tidak',
  `ket_kp_pengolah` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `ket_kp_lapangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `ket_kp_sosial` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `tgl_pengiriman` date NULL DEFAULT NULL,
  `ttd_sos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Paraf/nama verifikator Tim Sosial',
  `ttd_ipds` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Paraf/nama verifikator Tim IPDS',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_sampel_ruta`(`sampel_id` ASC, `no_urut_ruta` ASC) USING BTREE,
  INDEX `ix_sampel_ruta_status`(`status_selesai` ASC, `tgl_pengiriman` ASC) USING BTREE,
  INDEX `ix_ruta_transfer`(`status_transfer_k` ASC, `status_transfer_kp` ASC, `status_transfer_seruti` ASC) USING BTREE,
  INDEX `ix_ruta_dokumen`(`status_dokumen` ASC) USING BTREE,
  CONSTRAINT `fk_ruta_sampel` FOREIGN KEY (`sampel_id`) REFERENCES `sampel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ck_no_urut_ruta` CHECK (`no_urut_ruta` between 1 and 10)
) ENGINE = InnoDB AUTO_INCREMENT = 1588 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sampel_ruta
-- ----------------------------
INSERT INTO `sampel_ruta` VALUES (1, 15, 10, 'BELUM', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-19 13:01:26');
INSERT INTO `sampel_ruta` VALUES (2, 15, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (3, 15, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (4, 15, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (5, 15, 6, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (6, 15, 5, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (7, 15, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (8, 15, 3, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (9, 15, 2, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (10, 15, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (11, 16, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (12, 16, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (13, 16, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (14, 16, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (15, 16, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (16, 16, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (17, 16, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (18, 16, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (19, 16, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (20, 16, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (21, 17, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (22, 17, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (23, 17, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (24, 17, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (25, 17, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (26, 17, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (27, 17, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (28, 17, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (29, 17, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (30, 17, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (31, 18, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (32, 18, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Puji Hidayatus Sholikhah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (33, 18, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (34, 18, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (35, 18, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (36, 18, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (37, 18, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (38, 18, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (39, 18, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Puji Hidayatus Sholikhah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (40, 18, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (41, 19, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (42, 19, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (43, 19, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (44, 19, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (45, 19, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (46, 19, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (47, 19, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (48, 19, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (49, 19, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (50, 19, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (51, 20, 10, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (52, 20, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (53, 20, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (54, 20, 7, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (55, 20, 6, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (56, 20, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (57, 20, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (58, 20, 3, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (59, 20, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (60, 20, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (61, 22, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (62, 22, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (63, 22, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (64, 22, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (65, 22, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (66, 22, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (67, 22, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (68, 22, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (69, 22, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (70, 22, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (71, 23, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (72, 23, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (73, 23, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (74, 23, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (75, 23, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (76, 23, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (77, 23, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Eka Wijaya (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (78, 23, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (79, 23, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Eka Wijaya (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (80, 23, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Eka Wijaya (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (81, 24, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (82, 24, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (83, 24, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (84, 24, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (85, 24, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (86, 24, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (87, 24, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (88, 24, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (89, 24, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (90, 24, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (91, 25, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (92, 25, 9, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, 'sudah clean tapi nama kepala rumah tangga di sistem belum diganti nama panjang (ilham afaneza rahmatullah)', NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (93, 25, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (94, 25, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (95, 25, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (96, 25, 5, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (97, 25, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (98, 25, 3, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (99, 25, 2, 'ADA', 'SUDAH', 1, 1, 0, 1, 'koordinat lintang error, status perkawinan suami istri tidak sama(404), umur sudah benar tapi tetep error (407), no urut keluarga error (408), Tidak berkode 4/5 kosong(609)blok IX banyak yang kosong jadi error (905-911), merenovasi rumah (1503 aiii) kosong, ada ART yang bersekolah maksimal SMA sederajat tapi terisi kode 7 tidak relevan (1413), error (1014)', NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (100, 25, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (101, 26, 10, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (102, 26, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (103, 26, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (104, 26, 7, 'ADA', 'SUDAH', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (105, 26, 6, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (106, 26, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (107, 26, 4, 'ADA', 'SUDAH', 0, 0, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (108, 26, 3, 'ADA', 'SUDAH', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (109, 26, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (110, 26, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Husnul Chotimah (Tim Sosial)', 'Astri Widarianti (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (111, 27, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (112, 27, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (113, 27, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (114, 27, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (115, 27, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (116, 27, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (117, 27, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (118, 27, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (119, 27, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (120, 27, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (121, 28, 10, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (122, 28, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (123, 28, 8, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (124, 28, 7, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (125, 28, 6, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (126, 28, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (127, 28, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (128, 28, 3, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (129, 28, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (130, 28, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Rizqi Elviah (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (131, 54, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (132, 54, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (133, 54, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (134, 54, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (135, 54, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (136, 54, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (137, 54, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (138, 54, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (139, 54, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (140, 54, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (141, 55, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (142, 55, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (143, 55, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (144, 55, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (145, 55, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (146, 55, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (147, 55, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (148, 55, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (149, 55, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (150, 55, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (151, 56, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (152, 56, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (153, 56, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (154, 56, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (155, 56, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (156, 56, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (157, 56, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (158, 56, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (159, 56, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (160, 56, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (161, 57, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (162, 57, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (163, 57, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (164, 57, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (165, 57, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (166, 57, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (167, 57, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (168, 57, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (169, 57, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (170, 57, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (171, 58, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (172, 58, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (173, 58, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (174, 58, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (175, 58, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (176, 58, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (177, 58, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (178, 58, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (179, 58, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (180, 58, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (181, 59, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (182, 59, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (183, 59, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (184, 59, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (185, 59, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (186, 59, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (187, 59, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (188, 59, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (189, 59, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (190, 59, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (191, 60, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (192, 60, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (193, 60, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (194, 60, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (195, 60, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (196, 60, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (197, 60, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (198, 60, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (199, 60, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (200, 60, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (201, 61, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (202, 61, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (203, 61, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (204, 61, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (205, 61, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (206, 61, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (207, 61, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (208, 61, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (209, 61, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (210, 61, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (211, 62, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (212, 62, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (213, 62, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (214, 62, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (215, 62, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (216, 62, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (217, 62, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (218, 62, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (219, 62, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (220, 62, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (221, 63, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (222, 63, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (223, 63, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (224, 63, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (225, 63, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (226, 63, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (227, 63, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (228, 63, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (229, 63, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (230, 63, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (231, 64, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (232, 64, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (233, 64, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (234, 64, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (235, 64, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (236, 64, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (237, 64, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (238, 64, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (239, 64, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (240, 64, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (241, 65, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (242, 65, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (243, 65, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (244, 65, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (245, 65, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (246, 65, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (247, 65, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (248, 65, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (249, 65, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (250, 65, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (251, 66, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (252, 66, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (253, 66, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (254, 66, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (255, 66, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (256, 66, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (257, 66, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (258, 66, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (259, 66, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (260, 66, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (261, 1, 10, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (262, 1, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (263, 1, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (264, 1, 7, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (265, 1, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (266, 1, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (267, 1, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (268, 1, 3, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (269, 1, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (270, 1, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (271, 2, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (272, 2, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (273, 2, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Endy Setiobudi (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (274, 2, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (275, 2, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (276, 2, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (277, 2, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Endy Setiobudi (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (278, 2, 3, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Endy Setiobudi (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (279, 2, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (280, 2, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (281, 3, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (282, 3, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Endy Setiobudi (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (283, 3, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Endy Setiobudi (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (284, 3, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (285, 3, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (286, 3, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (287, 3, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (288, 3, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (289, 3, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (290, 3, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Endy Setiobudi (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (291, 4, 10, 'ADA', 'SUDAH', 0, 0, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Ratna Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (292, 4, 9, 'ADA', 'SUDAH', 0, 0, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Ratna Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (293, 4, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ratna Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (294, 4, 7, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ratna Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (295, 4, 6, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ratna Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (296, 4, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ratna Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (297, 4, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (298, 4, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (299, 4, 2, 'ADA', 'SUDAH', 0, 0, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Ratna Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (300, 4, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (301, 5, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (302, 5, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ratna Wijayanti (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (303, 5, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (304, 5, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (305, 5, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (306, 5, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (307, 5, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (308, 5, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (309, 5, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (310, 5, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (311, 6, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (312, 6, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Diyan Kasihati (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (313, 6, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Diyan Kasihati (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (314, 6, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (315, 6, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (316, 6, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Diyan Kasihati (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (317, 6, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Diyan Kasihati (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (318, 6, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (319, 6, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Diyan Kasihati (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (320, 6, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Diyan Kasihati (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (321, 7, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (322, 7, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Dewi Sunyi Apriani (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (323, 7, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Dewi Sunyi Apriani (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (324, 7, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (325, 7, 6, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Dewi Sunyi Apriani (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (326, 7, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Dewi Sunyi Apriani (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (327, 7, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Dewi Sunyi Apriani (Tim Sosial)', 'Iffa Dzakiyya (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (328, 7, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (329, 7, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (330, 7, 1, 'BELUM', 'BELUM', 0, 0, 0, 1, '1503 blok XV kosong padahal di halaman pertama ada nominal daro harga rumah', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-19 11:38:40');
INSERT INTO `sampel_ruta` VALUES (331, 8, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (332, 8, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (333, 8, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (334, 8, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (335, 8, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (336, 8, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Eka Wijaya (Tim Sosial)', 'Aminatus Sholeha (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (337, 8, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (338, 8, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (339, 8, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Eka Wijaya (Tim Sosial)', 'Aminatus Sholeha (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (340, 8, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Eka Wijaya (Tim Sosial)', 'Aminatus Sholeha (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (341, 9, 10, 'ADA', 'SUDAH', 0, 0, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (342, 9, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (343, 9, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (344, 9, 7, 'ADA', 'SUDAH', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (345, 9, 6, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (346, 9, 5, 'ADA', 'SUDAH', 0, 0, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (347, 9, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (348, 9, 3, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (349, 9, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (350, 9, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (351, 10, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (352, 10, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (353, 10, 8, 'ADA', 'SUDAH', 1, 0, 0, 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (354, 10, 7, 'ADA', 'SUDAH', 1, 0, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (355, 10, 6, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (356, 10, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (357, 10, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (358, 10, 3, 'ADA', 'SUDAH', 1, 0, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (359, 10, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (360, 10, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Ike Noorhayati (Tim Sosial)', 'Prasistiwi Andrianingtyas (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (361, 11, 10, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (362, 11, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (363, 11, 8, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (364, 11, 7, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (365, 11, 6, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (366, 11, 5, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (367, 11, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (368, 11, 3, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (369, 11, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (370, 11, 1, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Hery Yahman (Tim Sosial)', 'Putri Salsabhila Fahira (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (371, 12, 10, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (372, 12, 9, 'BELUM', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-19 13:01:35');
INSERT INTO `sampel_ruta` VALUES (373, 12, 8, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (374, 12, 7, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (375, 12, 6, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (376, 12, 5, 'BELUM', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-19 13:01:35');
INSERT INTO `sampel_ruta` VALUES (377, 12, 4, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (378, 12, 3, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (379, 12, 2, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (380, 12, 1, 'ADA', 'BELUM', 1, 1, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Junaidi Ari Siswanto (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (381, 13, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (382, 13, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (383, 13, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Puji Hidayatus Sholikhah (Tim Sosial)', 'Aminatus Sholeha (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (384, 13, 7, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Puji Hidayatus Sholikhah (Tim Sosial)', 'Aminatus Sholeha (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (385, 13, 6, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Puji Hidayatus Sholikhah (Tim Sosial)', 'Aminatus Sholeha (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (386, 13, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (387, 13, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Puji Hidayatus Sholikhah (Tim Sosial)', 'Aminatus Sholeha (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (388, 13, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (389, 13, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Puji Hidayatus Sholikhah (Tim Sosial)', 'Aminatus Sholeha (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (390, 13, 1, 'ADA', 'BELUM', 0, 0, 0, 1, '1) R502 digit ke 11 dan ke 12 tidak sesuai dengan R502 digit ke 3 dan 4 R406c, 2) R1004 = 1 tapi R1005, R1006, R1007, R1008, R1009, R1010, R1011 kode 5 semua', NULL, NULL, NULL, 1, '1)isian rincian KOR_R603 berkode 1 tetapi isian R284K6 dan R285K6 tidak terisi, 2)R298K10 terisi tetapi R29K10 terisinya kurang dari 1000, 3) R298K11 terisi tetapi R29K11 terisinya kurang dari 1000', NULL, NULL, '2026-09-14', 'Puji Hidayatus Sholikhah (Tim Sosial)', 'Aminatus Sholeha (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:38');
INSERT INTO `sampel_ruta` VALUES (391, 14, 10, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (392, 14, 9, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (393, 14, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (394, 14, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (395, 14, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (396, 14, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (397, 14, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (398, 14, 3, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (399, 14, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (400, 14, 1, 'ADA', 'SUDAH', 1, 1, 0, 1, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-14', 'Wahyu Wijayanti (Tim Sosial)', 'Anung Anindhita Pratiwi (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (401, 21, 10, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (402, 21, 9, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (403, 21, 8, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (404, 21, 7, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (405, 21, 6, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (406, 21, 5, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (407, 21, 4, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (408, 21, 3, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (409, 21, 2, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (410, 21, 1, 'ADA', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-09-14', 'Mohamad Aripin (Tim Sosial)', 'Laviana Ika Putrisari (Tim IPDS)', '2026-09-15 13:50:50', '2026-09-19 13:22:37');
INSERT INTO `sampel_ruta` VALUES (411, 29, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (412, 29, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (413, 29, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (414, 29, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (415, 29, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (416, 29, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (417, 29, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (418, 29, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (419, 29, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (420, 29, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (421, 30, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (422, 30, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (423, 30, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (424, 30, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (425, 30, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (426, 30, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (427, 30, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (428, 30, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (429, 30, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (430, 30, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (431, 31, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (432, 31, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (433, 31, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (434, 31, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (435, 31, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (436, 31, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (437, 31, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (438, 31, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (439, 31, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (440, 31, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (441, 32, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (442, 32, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (443, 32, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (444, 32, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (445, 32, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (446, 32, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (447, 32, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (448, 32, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (449, 32, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (450, 32, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (451, 33, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (452, 33, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (453, 33, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (454, 33, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (455, 33, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (456, 33, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (457, 33, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (458, 33, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (459, 33, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (460, 33, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (461, 34, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (462, 34, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (463, 34, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (464, 34, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (465, 34, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (466, 34, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (467, 34, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (468, 34, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (469, 34, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (470, 34, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (471, 35, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (472, 35, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (473, 35, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (474, 35, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (475, 35, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (476, 35, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (477, 35, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (478, 35, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (479, 35, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (480, 35, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (481, 36, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (482, 36, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (483, 36, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (484, 36, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (485, 36, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (486, 36, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (487, 36, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (488, 36, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (489, 36, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (490, 36, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (491, 37, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (492, 37, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (493, 37, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (494, 37, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (495, 37, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (496, 37, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (497, 37, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (498, 37, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (499, 37, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (500, 37, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (501, 38, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (502, 38, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (503, 38, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (504, 38, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (505, 38, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (506, 38, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (507, 38, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (508, 38, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (509, 38, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (510, 38, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (511, 39, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (512, 39, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (513, 39, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (514, 39, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (515, 39, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (516, 39, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (517, 39, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (518, 39, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (519, 39, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (520, 39, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (521, 40, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (522, 40, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (523, 40, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (524, 40, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (525, 40, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (526, 40, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (527, 40, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (528, 40, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (529, 40, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (530, 40, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (531, 41, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (532, 41, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (533, 41, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (534, 41, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (535, 41, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (536, 41, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (537, 41, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (538, 41, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (539, 41, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (540, 41, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (541, 42, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (542, 42, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (543, 42, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (544, 42, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (545, 42, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (546, 42, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (547, 42, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (548, 42, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (549, 42, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (550, 42, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (551, 43, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (552, 43, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (553, 43, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (554, 43, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (555, 43, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (556, 43, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (557, 43, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (558, 43, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (559, 43, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (560, 43, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (561, 44, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (562, 44, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (563, 44, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (564, 44, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (565, 44, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (566, 44, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (567, 44, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (568, 44, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (569, 44, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (570, 44, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (571, 45, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (572, 45, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (573, 45, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (574, 45, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (575, 45, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (576, 45, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (577, 45, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (578, 45, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (579, 45, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (580, 45, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (581, 46, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (582, 46, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (583, 46, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (584, 46, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (585, 46, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (586, 46, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (587, 46, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (588, 46, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (589, 46, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (590, 46, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (591, 47, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (592, 47, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (593, 47, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (594, 47, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (595, 47, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (596, 47, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (597, 47, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (598, 47, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (599, 47, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (600, 47, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (601, 48, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (602, 48, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (603, 48, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (604, 48, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (605, 48, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (606, 48, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (607, 48, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (608, 48, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (609, 48, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (610, 48, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (611, 49, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (612, 49, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (613, 49, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (614, 49, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (615, 49, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (616, 49, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (617, 49, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (618, 49, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (619, 49, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (620, 49, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (621, 50, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (622, 50, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (623, 50, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (624, 50, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (625, 50, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (626, 50, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (627, 50, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (628, 50, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (629, 50, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (630, 50, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (631, 51, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (632, 51, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (633, 51, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (634, 51, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (635, 51, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (636, 51, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (637, 51, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (638, 51, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (639, 51, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (640, 51, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (641, 52, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (642, 52, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (643, 52, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (644, 52, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (645, 52, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (646, 52, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (647, 52, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (648, 52, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (649, 52, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (650, 52, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (651, 53, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (652, 53, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (653, 53, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (654, 53, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (655, 53, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (656, 53, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (657, 53, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (658, 53, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (659, 53, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (660, 53, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-15 13:50:50', '2026-09-15 13:50:50');
INSERT INTO `sampel_ruta` VALUES (1307, 200, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1308, 200, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1309, 200, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1310, 200, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1311, 200, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1312, 200, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1313, 200, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1314, 200, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1315, 200, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1316, 200, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1317, 201, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1318, 201, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1319, 201, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1320, 201, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1321, 201, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1322, 201, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1323, 201, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1324, 201, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1325, 201, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1326, 201, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1327, 202, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1328, 202, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1329, 202, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1330, 202, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1331, 202, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1332, 202, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1333, 202, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1334, 202, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1335, 202, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1336, 202, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1337, 203, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1338, 203, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1339, 203, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1340, 203, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1341, 203, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1342, 203, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1343, 203, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1344, 203, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1345, 203, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1346, 203, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1347, 204, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1348, 204, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1349, 204, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1350, 204, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1351, 204, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1352, 204, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1353, 204, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1354, 204, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1355, 204, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1356, 204, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1357, 205, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1358, 205, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1359, 205, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1360, 205, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1361, 205, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1362, 205, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1363, 205, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1364, 205, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1365, 205, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1366, 205, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1367, 206, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1368, 206, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1369, 206, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1370, 206, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1371, 206, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1372, 206, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1373, 206, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1374, 206, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1375, 206, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1376, 206, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1377, 207, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1378, 207, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1379, 207, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1380, 207, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1381, 207, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1382, 207, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1383, 207, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1384, 207, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1385, 207, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1386, 207, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1387, 208, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1388, 208, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1389, 208, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1390, 208, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1391, 208, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1392, 208, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1393, 208, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1394, 208, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1395, 208, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1396, 208, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1397, 209, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1398, 209, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1399, 209, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1400, 209, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1401, 209, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1402, 209, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1403, 209, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1404, 209, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1405, 209, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1406, 209, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1407, 210, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1408, 210, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1409, 210, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1410, 210, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1411, 210, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1412, 210, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1413, 210, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1414, 210, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1415, 210, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1416, 210, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1417, 211, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1418, 211, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1419, 211, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1420, 211, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1421, 211, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1422, 211, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1423, 211, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1424, 211, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1425, 211, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1426, 211, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1427, 212, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1428, 212, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1429, 212, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1430, 212, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1431, 212, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1432, 212, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1433, 212, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1434, 212, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1435, 212, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1436, 212, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:51', '2026-09-19 12:46:51');
INSERT INTO `sampel_ruta` VALUES (1437, 213, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1438, 213, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1439, 213, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1440, 213, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1441, 213, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1442, 213, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1443, 213, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1444, 213, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1445, 213, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1446, 213, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1447, 214, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1448, 214, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1449, 214, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1450, 214, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1451, 214, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1452, 214, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1453, 214, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1454, 214, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1455, 214, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1456, 214, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1457, 215, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1458, 215, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1459, 215, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1460, 215, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1461, 215, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1462, 215, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1463, 215, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1464, 215, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1465, 215, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1466, 215, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1467, 216, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1468, 216, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1469, 216, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1470, 216, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1471, 216, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1472, 216, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1473, 216, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1474, 216, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1475, 216, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1476, 216, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1477, 217, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1478, 217, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1479, 217, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1480, 217, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1481, 217, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1482, 217, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1483, 217, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1484, 217, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1485, 217, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1486, 217, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1487, 218, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1488, 218, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1489, 218, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1490, 218, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1491, 218, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1492, 218, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1493, 218, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1494, 218, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1495, 218, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1496, 218, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1497, 219, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1498, 219, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1499, 219, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1500, 219, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1501, 219, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1502, 219, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1503, 219, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1504, 219, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1505, 219, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1506, 219, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1507, 220, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1508, 220, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1509, 220, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1510, 220, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1511, 220, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1512, 220, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1513, 220, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1514, 220, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1515, 220, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1516, 220, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1517, 221, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1518, 221, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1519, 221, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1520, 221, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1521, 221, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1522, 221, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1523, 221, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1524, 221, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1525, 221, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1526, 221, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1527, 222, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1528, 222, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1529, 222, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1530, 222, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1531, 222, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1532, 222, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1533, 222, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1534, 222, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1535, 222, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1536, 222, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1537, 223, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1538, 223, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1539, 223, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1540, 223, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1541, 223, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1542, 223, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1543, 223, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1544, 223, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1545, 223, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1546, 223, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1547, 224, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1548, 224, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1549, 224, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1550, 224, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1551, 224, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1552, 224, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1553, 224, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1554, 224, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1555, 224, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1556, 224, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1557, 225, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1558, 225, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1559, 225, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1560, 225, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1561, 225, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1562, 225, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1563, 225, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1564, 225, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1565, 225, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1566, 225, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1567, 226, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1568, 226, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1569, 226, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1570, 226, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1571, 226, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1572, 226, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1573, 226, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1574, 226, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1575, 226, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1576, 226, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1577, 227, 1, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1578, 227, 2, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1579, 227, 3, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1580, 227, 4, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1581, 227, 5, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1582, 227, 6, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1583, 227, 7, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1584, 227, 8, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1585, 227, 9, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');
INSERT INTO `sampel_ruta` VALUES (1586, 227, 10, 'BELUM', 'BELUM', 0, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-19 12:46:52', '2026-09-19 12:46:52');

-- ----------------------------
-- Table structure for sls
-- ----------------------------
DROP TABLE IF EXISTS `sls`;
CREATE TABLE `sls`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_full` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'prov2+kab2+kec3+desa3+sls4+sub2',
  `prov` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '35',
  `kab` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '09',
  `kec` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `desa` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sls` char(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '4 digit, cth 0042',
  `sub` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '2 digit, cth 00',
  `nks` char(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'NKS BPS 5 digit, stabil per SLS',
  `desa_id` int UNSIGNED NULL DEFAULT NULL,
  `dusun` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `rw` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `rt` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `nama_sls` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ketua` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `klasifikasi` tinyint NULL DEFAULT NULL COMMENT '1=Perkotaan, 2=Perdesaan',
  `jml_kk` int UNSIGNED NULL DEFAULT NULL,
  `jml_rt` int UNSIGNED NULL DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_sls_nks`(`nks` ASC) USING BTREE,
  UNIQUE INDEX `kode_full`(`kode_full` ASC) USING BTREE,
  INDEX `fk_sls_desa`(`desa_id` ASC) USING BTREE,
  INDEX `ix_sls_kec_desa`(`kec` ASC, `desa` ASC) USING BTREE,
  INDEX `ix_sls_full`(`kode_full` ASC) USING BTREE,
  CONSTRAINT `fk_sls_desa` FOREIGN KEY (`desa_id`) REFERENCES `desa` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ck_sls_full` CHECK ((`kode_full` is null) or regexp_like(`kode_full`,_utf8mb4'^[0-9]{16}$')),
  CONSTRAINT `ck_sls_nks` CHECK (regexp_like(`nks`,_utf8mb4'^[0-9]{5}$'))
) ENGINE = InnoDB AUTO_INCREMENT = 88 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sls
-- ----------------------------
INSERT INTO `sls` VALUES (1, NULL, '35', '09', '020', '003', NULL, NULL, '50536', 1, NULL, NULL, NULL, 'MENAMPU', NULL, NULL, NULL, 83, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (2, NULL, '35', '09', '020', '007', NULL, NULL, '00327', 2, NULL, NULL, NULL, 'TEMBOKREJO', NULL, NULL, NULL, 58, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (3, NULL, '35', '09', '020', '008', NULL, NULL, '00407', 3, NULL, NULL, NULL, 'KARANGREJO', NULL, NULL, NULL, 89, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (4, NULL, '35', '09', '030', '001', NULL, NULL, '00445', 4, NULL, NULL, NULL, 'MOJOMULYO', NULL, NULL, NULL, 50, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (5, NULL, '35', '09', '040', '005', NULL, NULL, '51611', 5, NULL, NULL, NULL, 'DUKUH DEMPOK', NULL, NULL, NULL, 39, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (6, NULL, '35', '09', '050', '001', NULL, NULL, '51797', 6, NULL, NULL, NULL, 'SUMBERREJO', NULL, NULL, NULL, 59, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (7, NULL, '35', '09', '050', '005', NULL, NULL, '52097', 7, NULL, NULL, NULL, 'PONTANG', NULL, NULL, NULL, 92, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (8, NULL, '35', '09', '060', '005', NULL, NULL, '52471', 8, NULL, NULL, NULL, 'SIDODADI', NULL, NULL, NULL, 54, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (9, NULL, '35', '09', '070', '002', NULL, NULL, '01356', 9, NULL, NULL, NULL, 'PACE', NULL, NULL, NULL, 47, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (10, NULL, '35', '09', '070', '009', NULL, NULL, '01692', 10, NULL, NULL, NULL, 'SIDOMULYO', NULL, NULL, NULL, 51, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (11, NULL, '35', '09', '090', '005', NULL, NULL, '53400', 11, NULL, NULL, NULL, 'MUMBULSARI', NULL, NULL, NULL, 49, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (12, NULL, '35', '09', '120', '003', NULL, NULL, '54904', 12, NULL, NULL, NULL, 'ROWOTAMTU', NULL, NULL, NULL, 26, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (13, NULL, '35', '09', '130', '004', NULL, NULL, '55394', 13, NULL, NULL, NULL, 'BALUNG KULON', NULL, NULL, NULL, 74, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (14, NULL, '35', '09', '140', '001', NULL, NULL, '02185', 14, NULL, NULL, NULL, 'SUKORENO', NULL, NULL, NULL, 45, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (15, NULL, '35', '09', '170', '002', NULL, NULL, '56326', 15, NULL, NULL, NULL, 'ROWO TENGAH', NULL, NULL, NULL, 47, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (16, '3509170002004200', '35', '09', '170', '002', '0042', '00', '56361', 15, 'SADENGAN', '014', '001', 'ROWO TENGAH', NULL, 1, 100, 97, 1, '2026-09-12 12:55:32', '2026-09-14 12:36:11');
INSERT INTO `sls` VALUES (17, NULL, '35', '09', '180', '004', NULL, NULL, '03125', 16, NULL, NULL, NULL, 'SELODAKON', NULL, NULL, NULL, 62, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (18, NULL, '35', '09', '180', '007', NULL, NULL, '56901', 17, NULL, NULL, NULL, 'PATEMON', NULL, NULL, NULL, 47, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (19, NULL, '35', '09', '190', '009', NULL, NULL, '03376', 18, NULL, NULL, NULL, 'TUGUSARI', NULL, NULL, NULL, 62, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (20, NULL, '35', '09', '200', '006', NULL, NULL, '57672', 19, NULL, NULL, NULL, 'SUCI', NULL, NULL, NULL, 30, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (21, NULL, '35', '09', '250', '001', NULL, NULL, '04098', 20, NULL, NULL, NULL, 'SUREN', NULL, NULL, NULL, 46, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (22, NULL, '35', '09', '250', '002', NULL, NULL, '04167', 21, NULL, NULL, NULL, 'SUMBER SALAK', NULL, NULL, NULL, 72, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (23, NULL, '35', '09', '250', '003', NULL, NULL, '58988', 22, NULL, NULL, NULL, 'SUMBER BULUS', NULL, NULL, NULL, 29, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (24, NULL, '35', '09', '260', '008', NULL, NULL, '04592', 23, NULL, NULL, NULL, 'PRINGGONDANI', NULL, NULL, NULL, 50, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (25, NULL, '35', '09', '710', '002', NULL, NULL, '59905', 24, NULL, NULL, NULL, 'SEMPUSARI', NULL, NULL, NULL, 110, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (26, NULL, '35', '09', '710', '007', NULL, NULL, '60488', 25, NULL, NULL, NULL, 'KEBON AGUNG', NULL, NULL, NULL, 64, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (27, NULL, '35', '09', '720', '001', NULL, NULL, '60552', 26, NULL, NULL, NULL, 'KERANJINGAN', NULL, NULL, NULL, 45, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');
INSERT INTO `sls` VALUES (28, NULL, '35', '09', '720', '006', NULL, NULL, '61110', 27, NULL, NULL, NULL, 'TEGAL GEDE', NULL, NULL, NULL, 73, 1, '2026-09-12 12:55:32', '2026-09-19 12:46:51');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `orang_id` int UNSIGNED NULL DEFAULT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Argon2id',
  `role_id` tinyint UNSIGNED NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `must_reset` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=wajib ganti password saat login pertama (migrasi Jember3509)',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_users_email`(`email` ASC) USING BTREE,
  INDEX `ix_users_role`(`role_id` ASC, `is_aktif` ASC) USING BTREE,
  INDEX `ix_users_orang`(`orang_id` ASC) USING BTREE,
  CONSTRAINT `fk_users_orang` FOREIGN KEY (`orang_id`) REFERENCES `orang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, NULL, 'Administrator', 'admin@bpsjember.go.id', '$argon2id$v=19$m=65536,t=4,p=1$TDlXRmNjbVlWNHNtRlpBaQ$LxppBdKDX6ObblxDPkObv0wpktsv8Zu4+azM0L8oVho', 1, 1, 1, '2026-09-19 13:01:26', '2026-09-12 12:55:31', '2026-09-19 13:01:26');
INSERT INTO `users` VALUES (2, 1, 'Aminatus Sholeha', 'aminatuss182002@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$R3RTZ200ZGg0czBwWjc5Vg$zetWns9FoKLY5jBB6GUUmmuelaJSZlfiUnnWTjJ8dp8', 5, 1, 0, '2026-09-19 13:01:35', '2026-09-12 12:55:31', '2026-09-19 13:01:35');
INSERT INTO `users` VALUES (3, 2, 'Anung Anindhita Pratiwi', 'anunganindhitap@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$T2svMXBDTXlabWVRNFdwcA$vR9AyEa8uouNLp3qWPzLZ4H6zkGgvvtf9YV9XqAsEZU', 5, 1, 1, NULL, '2026-09-12 12:55:31', '2026-09-12 12:55:31');
INSERT INTO `users` VALUES (4, 3, 'Putri Salsabhila Fahira', 'putrisalsabhilafahira10@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$T2svMXBDTXlabWVRNFdwcA$vR9AyEa8uouNLp3qWPzLZ4H6zkGgvvtf9YV9XqAsEZU', 5, 1, 1, NULL, '2026-09-12 12:55:31', '2026-09-12 12:55:31');
INSERT INTO `users` VALUES (5, 4, 'Iffa Dzakiyya', 'bpsbpsiffa36@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$T2svMXBDTXlabWVRNFdwcA$vR9AyEa8uouNLp3qWPzLZ4H6zkGgvvtf9YV9XqAsEZU', 5, 1, 1, NULL, '2026-09-12 12:55:31', '2026-09-12 12:55:31');
INSERT INTO `users` VALUES (6, 5, 'Prasistiwi Andrianingtyas', 'prasistiwi@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$T2svMXBDTXlabWVRNFdwcA$vR9AyEa8uouNLp3qWPzLZ4H6zkGgvvtf9YV9XqAsEZU', 5, 1, 1, NULL, '2026-09-12 12:55:31', '2026-09-12 12:55:31');
INSERT INTO `users` VALUES (7, 6, 'Laviana Ika Putrisari', 'lavianaikarumby@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$T2svMXBDTXlabWVRNFdwcA$vR9AyEa8uouNLp3qWPzLZ4H6zkGgvvtf9YV9XqAsEZU', 5, 1, 1, NULL, '2026-09-12 12:55:31', '2026-09-12 12:55:31');
INSERT INTO `users` VALUES (8, 7, 'Nur Ida Suryandari', 'nidasuryandari@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$T2svMXBDTXlabWVRNFdwcA$vR9AyEa8uouNLp3qWPzLZ4H6zkGgvvtf9YV9XqAsEZU', 5, 1, 1, NULL, '2026-09-12 12:55:31', '2026-09-12 12:55:31');
INSERT INTO `users` VALUES (9, 8, 'Astri Widarianti', 'a.widarianti@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$T2svMXBDTXlabWVRNFdwcA$vR9AyEa8uouNLp3qWPzLZ4H6zkGgvvtf9YV9XqAsEZU', 5, 1, 1, NULL, '2026-09-12 12:55:31', '2026-09-12 12:55:31');
INSERT INTO `users` VALUES (10, 9, 'Petugas PCL Demo', 'pcl.demo@bpsjember.go.id', '$argon2id$v=19$m=65536,t=4,p=1$V2dKRXlSWVAuODN4a3FRZA$N6qMP0rkyFG0zehxMIsXjnRnnpRHpJhzpQijGOnfVj8', 4, 1, 0, NULL, '2026-09-12 12:55:32', '2026-09-14 12:36:13');
INSERT INTO `users` VALUES (11, 37, 'Petugas PML Demo', 'pml.demo@bpsjember.go.id', '$argon2id$v=19$m=65536,t=4,p=1$V2dKRXlSWVAuODN4a3FRZA$N6qMP0rkyFG0zehxMIsXjnRnnpRHpJhzpQijGOnfVj8', 3, 1, 0, NULL, '2026-09-12 12:55:32', '2026-09-14 12:36:13');
INSERT INTO `users` VALUES (12, NULL, 'Petugas Operator Demo', 'operator.demo@bpsjember.go.id', '$argon2id$v=19$m=65536,t=4,p=1$V2dKRXlSWVAuODN4a3FRZA$N6qMP0rkyFG0zehxMIsXjnRnnpRHpJhzpQijGOnfVj8', 2, 1, 0, NULL, '2026-09-12 12:55:32', '2026-09-14 12:36:13');
INSERT INTO `users` VALUES (13, NULL, 'Petugas Viewer Demo', 'viewer.demo@bpsjember.go.id', '$argon2id$v=19$m=65536,t=4,p=1$V2dKRXlSWVAuODN4a3FRZA$N6qMP0rkyFG0zehxMIsXjnRnnpRHpJhzpQijGOnfVj8', 6, 1, 0, NULL, '2026-09-12 12:55:32', '2026-09-14 12:36:13');

SET FOREIGN_KEY_CHECKS = 1;
