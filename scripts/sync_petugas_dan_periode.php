<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../data/petugas.xlsx';
if (!file_exists($file)) {
    fwrite(STDERR, "Error: File {$file} tidak ditemukan.\n");
    exit(1);
}

function syncToDb(PDO $pdo, string $dbLabel, string $excelFile): void
{
    echo "========================================================\n";
    echo " SINKRONISASI DATABASE: {$dbLabel}\n";
    echo "========================================================\n";

    // 1. Roles Map
    $rolesMap = [];
    foreach ($pdo->query("SELECT id, code FROM roles")->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $rolesMap[$r['code']] = (int)$r['id'];
    }
    $rolePcl = $rolesMap['PCL'] ?? 4;
    $rolePml = $rolesMap['PML'] ?? 3;
    $rolePengolah = $rolesMap['PENGOLAH'] ?? 5;

    // 2. Load Excel Alokasi
    $sp = IOFactory::load($excelFile);
    $sheet = $sp->getSheetByName('Alokasi') ?? $sp->getSheet(0);

    $alokasiRows = [];
    for ($r = 2; $r <= $sheet->getHighestRow(); $r++) {
        $nks = trim((string)$sheet->getCell([1, $r])->getValue());
        if ($nks === '') {
            continue;
        }
        $kecKode = trim((string)$sheet->getCell([2, $r])->getValue());
        $kecNama = trim((string)$sheet->getCell([3, $r])->getValue());
        $desaKode = trim((string)$sheet->getCell([4, $r])->getValue());
        $desaNama = trim((string)$sheet->getCell([5, $r])->getValue());
        
        $pclNama = Validator::canonicalNama((string)$sheet->getCell([6, $r])->getValue());
        $pclHp = trim((string)$sheet->getCell([7, $r])->getValue()) ?: null;
        
        $pmlNama = Validator::canonicalNama((string)$sheet->getCell([8, $r])->getValue());
        $pmlHp = trim((string)$sheet->getCell([9, $r])->getValue()) ?: null;
        
        $pengolahRaw = trim((string)$sheet->getCell([10, $r])->getValue());
        $pengolahHp = trim((string)$sheet->getCell([11, $r])->getValue()) ?: null;

        $alokasiRows[] = [
            'row' => $r,
            'nks' => $nks,
            'kec_kode' => $kecKode,
            'kec_nama' => $kecNama,
            'desa_kode' => $desaKode,
            'desa_nama' => $desaNama,
            'pcl_nama' => $pclNama,
            'pcl_hp' => $pclHp,
            'pml_nama' => $pmlNama,
            'pml_hp' => $pmlHp,
            'pengolah_raw' => $pengolahRaw,
            'pengolah_hp' => $pengolahHp,
        ];
    }

    echo "Total SLS/NKS di Excel: " . count($alokasiRows) . "\n";

    // 3. Mapping & Sinkronisasi 8 Pengolah
    // Mapping variasi nama excel ke nama kanonik di DB
    $pengolahMap = [
        'Anung Anindhita P' => ['canonical' => 'Anung Anindhita Pratiwi', 'alias' => 'anung anindhita p'],
        'Prasistiwi' => ['canonical' => 'Prasistiwi Andrianingtyas', 'alias' => 'prasistiwi'],
        'Aminatus Sholeha' => ['canonical' => 'Aminatus Sholeha', 'alias' => null],
        'Iffa Dzakiyya Khairunnisa' => ['canonical' => 'Iffa Dzakiyya', 'alias' => 'iffa dzakiyya khairunnisa'],
        'Laviana Ika Putrisari' => ['canonical' => 'Laviana Ika Putrisari', 'alias' => null],
        'Putri Salsabhila Fahira' => ['canonical' => 'Putri Salsabhila Fahira', 'alias' => null],
        'Astri Widarianti' => ['canonical' => 'Astri Widarianti', 'alias' => null],
        'Nur Ida Suryandari' => ['canonical' => 'Nur Ida Suryandari', 'alias' => null],
    ];

    $pengolahDbIds = [];
    $stmtUpdHp = $pdo->prepare("UPDATE orang SET no_hp = :hp, role_id = :rid WHERE id = :id");
    $stmtInsAlias = $pdo->prepare("INSERT IGNORE INTO orang_alias (orang_id, alias_normalized) VALUES (:oid, :alias)");

    foreach ($alokasiRows as $row) {
        $raw = $row['pengolah_raw'];
        if (!isset($pengolahMap[$raw])) {
            continue;
        }
        $info = $pengolahMap[$raw];
        $canonical = $info['canonical'];
        $norm = Validator::normalized($canonical);

        $st = $pdo->prepare("SELECT id FROM orang WHERE nama_normalized = :norm LIMIT 1");
        $st->execute([':norm' => $norm]);
        $oid = $st->fetchColumn();

        if ($oid) {
            $oid = (int)$oid;
            $pengolahDbIds[$raw] = $oid;
            if (!empty($row['pengolah_hp'])) {
                $stmtUpdHp->execute([':hp' => $row['pengolah_hp'], ':rid' => $rolePengolah, ':id' => $oid]);
            }
            if (!empty($info['alias'])) {
                $stmtInsAlias->execute([':oid' => $oid, ':alias' => Validator::normalized($info['alias'])]);
            }
        }
    }
    echo "Pengolah tersinkron: " . count($pengolahDbIds) . " orang\n";

    // 4. Sinkronisasi 15 PML
    $pmlDbIds = [];
    $stmtFindOrang = $pdo->prepare("SELECT id, no_hp FROM orang WHERE nama_normalized = :norm LIMIT 1");
    $stmtInsOrang = $pdo->prepare("
        INSERT INTO orang (nama, no_hp, role_id, is_aktif)
        VALUES (:nama, :hp, :rid, 1)
        ON DUPLICATE KEY UPDATE role_id = VALUES(role_id), no_hp = COALESCE(VALUES(no_hp), no_hp), is_aktif = 1
    ");

    foreach ($alokasiRows as $row) {
        $pmlNama = $row['pml_nama'];
        if (isset($pmlDbIds[$pmlNama])) {
            continue;
        }
        $norm = Validator::normalized($pmlNama);
        $stmtFindOrang->execute([':norm' => $norm]);
        $existing = $stmtFindOrang->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $oid = (int)$existing['id'];
            if ($row['pml_hp'] && empty($existing['no_hp'])) {
                $stmtUpdHp->execute([':hp' => $row['pml_hp'], ':rid' => $rolePml, ':id' => $oid]);
            } else {
                $pdo->prepare("UPDATE orang SET role_id = :rid, is_aktif = 1 WHERE id = :id")->execute([':rid' => $rolePml, ':id' => $oid]);
            }
        } else {
            $stmtInsOrang->execute([
                ':nama' => $pmlNama,
                ':hp' => $row['pml_hp'],
                ':rid' => $rolePml,
            ]);
            $stmtFindOrang->execute([':norm' => $norm]);
            $oid = (int)$stmtFindOrang->fetchColumn();
        }
        $pmlDbIds[$pmlNama] = $oid;
    }
    echo "PML tersinkron: " . count($pmlDbIds) . " orang\n";

    // 5. Sinkronisasi 28 PCL
    $pclDbIds = [];
    foreach ($alokasiRows as $row) {
        $pclNama = $row['pcl_nama'];
        if (isset($pclDbIds[$pclNama])) {
            continue;
        }
        $norm = Validator::normalized($pclNama);
        $stmtFindOrang->execute([':norm' => $norm]);
        $existing = $stmtFindOrang->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $oid = (int)$existing['id'];
            if ($row['pcl_hp'] && empty($existing['no_hp'])) {
                $stmtUpdHp->execute([':hp' => $row['pcl_hp'], ':rid' => $rolePcl, ':id' => $oid]);
            } else {
                $pdo->prepare("UPDATE orang SET role_id = :rid, is_aktif = 1 WHERE id = :id")->execute([':rid' => $rolePcl, ':id' => $oid]);
            }
        } else {
            $stmtInsOrang->execute([
                ':nama' => $pclNama,
                ':hp' => $row['pcl_hp'],
                ':rid' => $rolePcl,
            ]);
            $stmtFindOrang->execute([':norm' => $norm]);
            $oid = (int)$stmtFindOrang->fetchColumn();
        }
        $pclDbIds[$pclNama] = $oid;
    }
    echo "PCL tersinkron: " . count($pclDbIds) . " orang\n";

    // 6. Nonaktifkan Petugas Dummy
    $stmtDeact = $pdo->prepare("UPDATE orang SET is_aktif = 0 WHERE nama LIKE 'Pcl Dummy%' OR nama LIKE 'Pml Dummy%'");
    $stmtDeact->execute();
    echo "Petugas dummy dinonaktifkan (is_aktif = 0)\n";

    // 7. Update sls.desa_id
    $stmtSlsDesa = $pdo->prepare("
        UPDATE sls s
        JOIN desa d ON d.kecamatan_kode = s.kec AND d.kode = s.desa
        SET s.desa_id = d.id
        WHERE s.desa_id IS NULL
    ");
    $stmtSlsDesa->execute();
    echo "Relasi SLS -> Desa diperbarui: " . $stmtSlsDesa->rowCount() . " baris\n";

    // 8. Update Penugasan pada Periode 1: SUSENAS_S2 (2026-S2 Susenas September)
    $stmtFindSampel = $pdo->prepare("
        SELECT sp.id AS sampel_id
        FROM sampel sp
        JOIN sls s ON s.id = sp.sls_id
        WHERE sp.periode_id = :pid AND s.nks = :nks
        LIMIT 1
    ");
    $stmtAssign = $pdo->prepare("
        INSERT INTO penugasan (sampel_id, pcl_id, pml_id, pengolah_id, status)
        VALUES (:sid, :pcl, :pml, :peng, 'AKTIF')
        ON DUPLICATE KEY UPDATE
            pcl_id = VALUES(pcl_id),
            pml_id = VALUES(pml_id),
            pengolah_id = VALUES(pengolah_id),
            status = 'AKTIF'
    ");

    $periodeS2Id = 1; // 2026-S2 Susenas September
    $s2Updated = 0;
    foreach ($alokasiRows as $row) {
        $stmtFindSampel->execute([':pid' => $periodeS2Id, ':nks' => $row['nks']]);
        $sampelId = $stmtFindSampel->fetchColumn();
        if ($sampelId) {
            $pclId = $pclDbIds[$row['pcl_nama']];
            $pmlId = $pmlDbIds[$row['pml_nama']];
            $pengId = $pengolahDbIds[$row['pengolah_raw']];
            $stmtAssign->execute([
                ':sid' => (int)$sampelId,
                ':pcl' => $pclId,
                ':pml' => $pmlId,
                ':peng' => $pengId,
            ]);
            $s2Updated++;
        }
    }
    echo "Penugasan Periode Susenas S2 (ID {$periodeS2Id}) tersinkron: {$s2Updated} SLS\n";

    // 9. Buat/Update Periode SERUTI_Q3 (2026-Q3 Seruti Triwulan III)
    $stPerQ3 = $pdo->prepare("SELECT id FROM periode WHERE tahun = 2026 AND jenis = 'SERUTI_Q3' LIMIT 1");
    $stPerQ3->execute();
    $perQ3Id = $stPerQ3->fetchColumn();

    if (!$perQ3Id) {
        $pdo->prepare("
            INSERT INTO periode (tahun, jenis, label, tgl_mulai, tgl_selesai, status, catatan)
            VALUES (2026, 'SERUTI_Q3', '2026-Q3 Seruti Triwulan III', '2026-07-01', '2026-09-30', 'AKTIF', 'Master wilayah & penugasan Seruti Triwulan III 2026 (integrasi Susenas S2 2026)')
        ")->execute();
        $perQ3Id = (int)$pdo->lastInsertId();
        echo "Periode baru dibuat: ID {$perQ3Id} - 2026-Q3 Seruti Triwulan III (AKTIF)\n";
    } else {
        $perQ3Id = (int)$perQ3Id;
        $pdo->prepare("
            UPDATE periode SET status = 'AKTIF', label = '2026-Q3 Seruti Triwulan III',
                   tgl_mulai = '2026-07-01', tgl_selesai = '2026-09-30'
            WHERE id = :id
        ")->execute([':id' => $perQ3Id]);
        echo "Periode SERUTI_Q3 ditemukan: ID {$perQ3Id} (AKTIF)\n";
    }

    // 10. Tambah 28 Sampel, Penugasan, dan Inisialisasi 10 Ruta untuk SERUTI_Q3
    $stmtInsSampel = $pdo->prepare("
        INSERT INTO sampel (periode_id, sls_id, target_sampel, muatan_awal, dokumen_vsen, peta_ws)
        VALUES (:pid, :sls_id, 10, 50, 1, 1)
        ON DUPLICATE KEY UPDATE target_sampel = 10, dokumen_vsen = 1, peta_ws = 1
    ");
    $stmtFindSls = $pdo->prepare("SELECT id FROM sls WHERE nks = :nks LIMIT 1");
    $stmtInsRuta = $pdo->prepare("
        INSERT IGNORE INTO sampel_ruta (sampel_id, no_urut_ruta, status_dokumen, status_selesai)
        VALUES (:sid, :no, 'BELUM', 'BELUM')
    ");

    $q3SampelCount = 0;
    foreach ($alokasiRows as $row) {
        $stmtFindSls->execute([':nks' => $row['nks']]);
        $slsId = $stmtFindSls->fetchColumn();
        if (!$slsId) {
            echo "Peringatan: SLS NKS {$row['nks']} tidak ditemukan!\n";
            continue;
        }
        $slsId = (int)$slsId;

        // Upsert sampel
        $stmtInsSampel->execute([':pid' => $perQ3Id, ':sls_id' => $slsId]);
        
        // Cari id sampel
        $stmtFindSampel->execute([':pid' => $perQ3Id, ':nks' => $row['nks']]);
        $sampelId = (int)$stmtFindSampel->fetchColumn();

        // Assign petugas riil
        $pclId = $pclDbIds[$row['pcl_nama']];
        $pmlId = $pmlDbIds[$row['pml_nama']];
        $pengId = $pengolahDbIds[$row['pengolah_raw']];
        $stmtAssign->execute([
            ':sid' => $sampelId,
            ':pcl' => $pclId,
            ':pml' => $pmlId,
            ':peng' => $pengId,
        ]);

        // Inisialisasi 10 ruta per sampel
        for ($noRuta = 1; $noRuta <= 10; $noRuta++) {
            $stmtInsRuta->execute([':sid' => $sampelId, ':no' => $noRuta]);
        }
        $q3SampelCount++;
    }
    echo "Sampel, Penugasan & 10 Ruta SERUTI_Q3 tersinkron: {$q3SampelCount} SLS (280 Ruta)\n";

    // 11. Validasi Aturan K4 (1 orang 1 peran per periode)
    echo "--- Pengecekan Aturan K4 ---\n";
    foreach ([$periodeS2Id => 'SUSENAS_S2', $perQ3Id => 'SERUTI_Q3'] as $pid => $pLabel) {
        $stmtK4 = $pdo->prepare("
            SELECT pg.id, sp.id AS sampel_id, s.nks, pg.pcl_id, pg.pml_id, pg.pengolah_id
            FROM penugasan pg
            JOIN sampel sp ON sp.id = pg.sampel_id
            JOIN sls s ON s.id = sp.sls_id
            WHERE sp.periode_id = :pid
              AND (pg.pcl_id = pg.pml_id OR pg.pcl_id = pg.pengolah_id OR pg.pml_id = pg.pengolah_id)
        ");
        $stmtK4->execute([':pid' => $pid]);
        $dupsInSample = $stmtK4->fetchAll(PDO::FETCH_ASSOC);

        // Cek cross peran dalam periode
        $stmtCross = $pdo->prepare("
            WITH peran_orang AS (
                SELECT pg.pcl_id AS oid, 'PCL' AS peran FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id WHERE sp.periode_id=:p1
                UNION
                SELECT pg.pml_id AS oid, 'PML' AS peran FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id WHERE sp.periode_id=:p2
                UNION
                SELECT pg.pengolah_id AS oid, 'PENGOLAH' AS peran FROM penugasan pg JOIN sampel sp ON sp.id=pg.sampel_id WHERE sp.periode_id=:p3
            )
            SELECT oid, COUNT(DISTINCT peran) AS jml_peran, GROUP_CONCAT(peran) AS perans
            FROM peran_orang
            GROUP BY oid
            HAVING jml_peran > 1
        ");
        $stmtCross->execute([':p1' => $pid, ':p2' => $pid, ':p3' => $pid]);
        $crossViolations = $stmtCross->fetchAll(PDO::FETCH_ASSOC);

        echo "Periode {$pLabel} (ID {$pid}):\n";
        echo "  - Pelanggaran peran sama dalam 1 SLS: " . count($dupsInSample) . "\n";
        echo "  - Pelanggaran rangkap peran antar-SLS (K4): " . count($crossViolations) . "\n";
        if (count($dupsInSample) > 0 || count($crossViolations) > 0) {
            echo "  [PERINGATAN] Terjadi pelanggaran K4!\n";
        } else {
            echo "  [OK] Aturan K4 100% TERPENUHI (0 Pelanggaran).\n";
        }
    }
    echo "Sinkronisasi {$dbLabel} selesai.\n\n";
}

// Eksekusi pada DB aktif (sipandhalu)
$pdo = Database::connection();
syncToDb($pdo, 'sipandhalu (Database Utama)', $file);

// Eksekusi pada DB pengujian (sipandhalu_test) jika ada
try {
    $cfg = require __DIR__ . '/../config/database.php';
    $dsnTest = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $cfg['host'] ?? '127.0.0.1',
        $cfg['port'] ?? 3306,
        'sipandhalu_test',
        $cfg['charset'] ?? 'utf8mb4'
    );
    $pdoTest = new PDO($dsnTest, $cfg['username'] ?? 'root', $cfg['password'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    syncToDb($pdoTest, 'sipandhalu_test (Database Pengujian)', $file);
} catch (\Throwable $e) {
    echo "Catatan: Database sipandhalu_test tidak diupdate atau tidak tersedia: " . $e->getMessage() . "\n";
}
