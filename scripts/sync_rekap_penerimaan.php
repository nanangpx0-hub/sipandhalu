<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$excelFile = __DIR__ . '/../data/Rekap_Penerimaan_Dokumen.xlsx';
if (!file_exists($excelFile)) {
    fwrite(STDERR, "Error: File {$excelFile} tidak ditemukan.\n");
    exit(1);
}

$pdo = Database::connection();

echo "========================================================\n";
echo " SINKRONISASI REKAP PENERIMAAN DOKUMEN FISIK\n";
echo "========================================================\n";

// 1. Ambil alokasi PML dan Pengolah per NKS dari database (Periode 1: Susenas S2)
$stmtPetugas = $pdo->query("
    SELECT s.nks,
           o_pml.nama AS nama_pml,
           o_peng.nama AS nama_pengolah
    FROM sampel sp
    JOIN sls s ON s.id = sp.sls_id
    JOIN penugasan pg ON pg.sampel_id = sp.id
    LEFT JOIN orang o_pml ON o_pml.id = pg.pml_id
    LEFT JOIN orang o_peng ON o_peng.id = pg.pengolah_id
    WHERE sp.periode_id = 1
");
$petugasMap = [];
foreach ($stmtPetugas->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $petugasMap[$row['nks']] = [
        'pml' => $row['nama_pml'] ?? 'Tim Sosial',
        'pengolah' => $row['nama_pengolah'] ?? 'Tim IPDS',
    ];
}

// 2. Baca file Excel
$spreadsheet = IOFactory::load($excelFile);
$sheet = $spreadsheet->getSheet(0);
$highestRow = $sheet->getHighestRow();

// Set Header baru di Row 1
$sheet->setCellValue('G1', 'Tanggal Terima');
$sheet->setCellValue('H1', 'Diserahkan Oleh (Tim Sosial)');
$sheet->setCellValue('I1', 'Diterima Oleh (Tim IPDS)');
$sheet->setCellValue('J1', 'Kondisi Fisik Dokumen');

// Styling header
$sheet->getStyle('G1:J1')->getFont()->setBold(true);

$stmtFindRuta = $pdo->prepare("
    SELECT sr.id
    FROM sampel_ruta sr
    JOIN sampel sp ON sp.id = sr.sampel_id
    JOIN sls s ON s.id = sp.sls_id
    WHERE sp.periode_id = :pid AND s.nks = :nks AND sr.no_urut_ruta = :ruta
    LIMIT 1
");

$stmtUpdateRuta = $pdo->prepare("
    UPDATE sampel_ruta
    SET status_dokumen = :st,
        tgl_pengiriman = :tgl,
        ttd_sos = :sos,
        ttd_ipds = :ipds
    WHERE id = :id
");

$countAda = 0;
$countBelum = 0;
$defaultDate = '2026-09-14';

for ($r = 2; $r <= $highestRow; $r++) {
    $nks = trim((string)$sheet->getCell('B' . $r)->getValue());
    $ruta = (int)$sheet->getCell('E' . $r)->getValue();
    $statusRaw = trim((string)$sheet->getCell('F' . $r)->getValue());

    if ($nks === '' || $ruta <= 0) {
        continue;
    }

    $isAda = (mb_strtolower($statusRaw) === 'ada');
    $pmlNama = $petugasMap[$nks]['pml'] ?? 'Tim Sosial';
    $pengolahNama = $petugasMap[$nks]['pengolah'] ?? 'Tim IPDS';

    // Cari ID sampel_ruta di database (Periode 1 Susenas S2)
    $stmtFindRuta->execute([':pid' => 1, ':nks' => $nks, ':ruta' => $ruta]);
    $rutaId = $stmtFindRuta->fetchColumn();

    if ($isAda) {
        $penyerah = $pmlNama . ' (Tim Sosial)';
        $penerima = $pengolahNama . ' (Tim IPDS)';
        $kondisi = 'Lengkap & Terverifikasi';

        // Update sel Excel
        $sheet->setCellValue('G' . $r, $defaultDate);
        $sheet->setCellValue('H' . $r, $penyerah);
        $sheet->setCellValue('I' . $r, $penerima);
        $sheet->setCellValue('J' . $r, $kondisi);

        // Update DB
        if ($rutaId) {
            $stmtUpdateRuta->execute([
                ':st' => 'ADA',
                ':tgl' => $defaultDate,
                ':sos' => $penyerah,
                ':ipds' => $penerima,
                ':id' => (int)$rutaId,
            ]);
        }
        $countAda++;
    } else {
        // Kosong / Belum
        $sheet->setCellValue('G' . $r, '');
        $sheet->setCellValue('H' . $r, '');
        $sheet->setCellValue('I' . $r, '');
        $sheet->setCellValue('J' . $r, 'Belum diterima');

        if ($rutaId) {
            $stmtUpdateRuta->execute([
                ':st' => 'BELUM',
                ':tgl' => null,
                ':sos' => null,
                ':ipds' => null,
                ':id' => (int)$rutaId,
            ]);
        }
        $countBelum++;
    }
}

// Simpan kembali file Excel dengan kolom yang sudah lengkap
$writer = new Xlsx($spreadsheet);
$writer->save($excelFile);

echo "Sinkronisasi selesai:\n";
echo " - Dokumen ADA (diterima) : {$countAda} ruta (dengan tanggal, penyerah Sosial, dan penerima IPDS)\n";
echo " - Dokumen BELUM diterima  : {$countBelum} ruta\n";
echo " - File Excel {$excelFile} telah diperbarui dengan kolom serah terima lengkap.\n";
