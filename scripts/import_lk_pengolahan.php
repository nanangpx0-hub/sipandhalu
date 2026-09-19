<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/../data/LK Pengolahan Sampel (2).xlsx';
if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

$db = Database::connection();

// 1. Cari periode aktif (default: 2026-S2 Susenas September / ID 1)
$periodeId = 1;
$p = $db->query("SELECT id, label FROM periode WHERE status = 'AKTIF' ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($p) {
    $periodeId = (int) $p['id'];
    echo "Menggunakan periode aktif: [{$periodeId}] {$p['label']}\n";
} else {
    echo "Menggunakan default periode ID: {$periodeId}\n";
}

// 2. Petakan NKS ke sampel_id pada periode ini
$stmtSampel = $db->prepare(
    "SELECT sp.id AS sampel_id, sl.nks
     FROM sampel sp
     JOIN sls sl ON sl.id = sp.sls_id
     WHERE sp.periode_id = :p"
);
$stmtSampel->execute([':p' => $periodeId]);
$sampelMap = []; // nks => sampel_id
foreach ($stmtSampel->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $sampelMap[trim($row['nks'])] = (int) $row['sampel_id'];
}
echo "Ditemukan " . count($sampelMap) . " sampel SLS di database untuk periode ini.\n";

echo "Membaca file Excel: $file\n";
$reader = IOFactory::createReaderForFile($file);
$reader->setReadDataOnly(true);
$sp = $reader->load($file);

// 3. Proses Sheet 'Rekap' (280 baris ruta: status dokumen & status transfer)
$rekapSheet = $sp->getSheetByName('Rekap');
if (!$rekapSheet) {
    echo "Sheet 'Rekap' tidak ditemukan!\n";
    exit(1);
}

$hRow = $rekapSheet->getHighestRow();
echo "Memproses Sheet 'Rekap' (1..{$hRow})...\n";

$updateRekapStmt = $db->prepare(
    "UPDATE sampel_ruta
     SET status_dokumen = :sdok,
         status_transfer_k = :stk,
         status_transfer_kp = :stkp,
         status_transfer_seruti = :stser
     WHERE sampel_id = :sid AND no_urut_ruta = :no"
);

$db->beginTransaction();
$updatedRekap = 0;
for ($r = 2; $r <= $hRow; $r++) {
    $nks = trim((string)$rekapSheet->getCell([2, $r])->getValue());
    $noRuta = (int) $rekapSheet->getCell([5, $r])->getValue();
    if ($nks === '' || $noRuta < 1 || $noRuta > 10) {
        continue;
    }

    if (!isset($sampelMap[$nks])) {
        continue;
    }
    $sampelId = $sampelMap[$nks];

    $sDokRaw = strtolower(trim((string)$rekapSheet->getCell([6, $r])->getValue()));
    $sDok = ($sDokRaw === 'ada') ? 'ADA' : 'BELUM';
    $stK = (trim((string)$rekapSheet->getCell([10, $r])->getValue()) === '1') ? 1 : 0;
    $stKP = (trim((string)$rekapSheet->getCell([11, $r])->getValue()) === '1') ? 1 : 0;
    $stSeruti = (trim((string)$rekapSheet->getCell([12, $r])->getValue()) === '1') ? 1 : 0;

    $updateRekapStmt->execute([
        ':sdok' => $sDok,
        ':stk' => $stK,
        ':stkp' => $stKP,
        ':stser' => $stSeruti,
        ':sid' => $sampelId,
        ':no' => $noRuta,
    ]);
    $updatedRekap++;
}
$db->commit();
echo "Berhasil update status rekap untuk {$updatedRekap} baris ruta.\n";

// 4. Proses Sheet 8 Pengolah (Catatan pemeriksaan KP, Modul, dan Uji Petik)
$pengolahSheets = [
    'Nur Ida Suryandari',
    'Iffa Dzakiyya Khairunnisa',
    'Laviana Ika Putrisari',
    'Anung Anindhita Pratiwi',
    'Aminatus Sholeha',
    'Putri Salsabhila Fahira',
    'Astri Widarianti',
    'Prasistiwi Andrianingtyas',
];

$updateCatatanStmt = $db->prepare(
    "UPDATE sampel_ruta
     SET catatan_kp = COALESCE(:ckp, catatan_kp),
         ket_kp_pengolah = :kp_pengolah,
         ket_kp_lapangan = :kp_lap,
         ket_kp_sosial = :kp_sos,
         catatan_modul = COALESCE(:cmod, catatan_modul),
         ket_m_pengolah = :m_pengolah,
         ket_m_lapangan = :m_lap,
         ket_m_sosial = :m_sos,
         uji_petik_pengawas = :uji
     WHERE sampel_id = :sid AND no_urut_ruta = :no"
);

$db->beginTransaction();
$totalCatatan = 0;
foreach ($pengolahSheets as $pName) {
    $sheet = $sp->getSheetByName($pName);
    if (!$sheet) continue;

    $pRows = $sheet->getHighestRow();
    for ($r = 2; $r <= $pRows; $r++) {
        $nks = trim((string)$sheet->getCell([2, $r])->getValue());
        $noRuta = (int) $sheet->getCell([3, $r])->getValue();
        if ($nks === '' || $noRuta < 1 || $noRuta > 10 || !isset($sampelMap[$nks])) {
            continue;
        }
        $sampelId = $sampelMap[$nks];

        $kpPengolah = trim((string)$sheet->getCell([5, $r])->getValue());
        $kpLap = trim((string)$sheet->getCell([6, $r])->getValue());
        $kpSos = trim((string)$sheet->getCell([7, $r])->getValue());

        $mPengolah = trim((string)$sheet->getCell([8, $r])->getValue());
        $mLap = trim((string)$sheet->getCell([9, $r])->getValue());
        $mSos = trim((string)$sheet->getCell([10, $r])->getValue());

        $uji = trim((string)$sheet->getCell([11, $r])->getValue());

        if ($kpPengolah !== '' || $kpLap !== '' || $kpSos !== '' ||
            $mPengolah !== '' || $mLap !== '' || $mSos !== '' || $uji !== '') {
            $updateCatatanStmt->execute([
                ':ckp' => ($kpPengolah !== '') ? 1 : null,
                ':kp_pengolah' => $kpPengolah !== '' ? $kpPengolah : null,
                ':kp_lap' => $kpLap !== '' ? $kpLap : null,
                ':kp_sos' => $kpSos !== '' ? $kpSos : null,
                ':cmod' => ($mPengolah !== '') ? 1 : null,
                ':m_pengolah' => $mPengolah !== '' ? $mPengolah : null,
                ':m_lap' => $mLap !== '' ? $mLap : null,
                ':m_sos' => $mSos !== '' ? $mSos : null,
                ':uji' => $uji !== '' ? $uji : null,
                ':sid' => $sampelId,
                ':no' => $noRuta,
            ]);
            $totalCatatan++;
            echo "  [+] Disimpan catatan ruta NKS {$nks} no. {$noRuta} (Pengolah: {$pName})\n";
        }
    }
}
$db->commit();
echo "Selesai sinkronisasi {$totalCatatan} catatan pemeriksaan error kuesioner ke database!\n";

// 5. Cek Ringkasan Data Hasil Sinkronisasi
$summary = $db->query(
    "SELECT 
        COUNT(*) AS total_ruta,
        SUM(CASE WHEN status_dokumen='ADA' THEN 1 ELSE 0 END) AS dok_ada,
        SUM(status_transfer_k) AS transfer_k,
        SUM(status_transfer_kp) AS transfer_kp,
        SUM(status_transfer_seruti) AS transfer_seruti,
        SUM(CASE WHEN ket_kp_pengolah IS NOT NULL OR ket_m_pengolah IS NOT NULL THEN 1 ELSE 0 END) AS ada_catatan_error
     FROM sampel_ruta sr
     JOIN sampel sp ON sp.id = sr.sampel_id
     WHERE sp.periode_id = {$periodeId}"
)->fetch(PDO::FETCH_ASSOC);

echo "\n=== RINGKASAN DATA HASIL SINKRONISASI ===\n";
echo "- Total Ruta: {$summary['total_ruta']}\n";
echo "- Status Dokumen ADA: {$summary['dok_ada']}\n";
echo "- Status Transfer K: {$summary['transfer_k']}\n";
echo "- Status Transfer KP: {$summary['transfer_kp']}\n";
echo "- Status Transfer Seruti: {$summary['transfer_seruti']}\n";
echo "- Ruta dengan Catatan Error Kuesioner: {$summary['ada_catatan_error']}\n";
