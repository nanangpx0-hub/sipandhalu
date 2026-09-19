<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$fileRekap = __DIR__ . '/../data/Rekap_Penerimaan_Dokumen.xlsx';
$spRekap = IOFactory::load($fileRekap);
$sheetRekap = $spRekap->getSheet(0);

$rekapData = [];
for ($r = 2; $r <= $sheetRekap->getHighestRow(); $r++) {
    $nks = trim((string)$sheetRekap->getCell('B' . $r)->getValue());
    $ruta = (int)$sheetRekap->getCell('E' . $r)->getValue();
    $st = trim((string)$sheetRekap->getCell('F' . $r)->getValue());
    if ($nks && $ruta) {
        $rekapData[$nks][$ruta] = mb_strtolower($st) === 'ada' ? 'ADA' : 'BELUM';
    }
}

// Compare with template_dokkirimkab _15092026.xls
$fileDokKirim = __DIR__ . '/../data/template_dokkirimkab _15092026.xls';
$spKirim = IOFactory::load($fileDokKirim);
$sheetKirim = $spKirim->getSheet(0);

$dokKirimData = [];
for ($r = 4; $r <= $sheetKirim->getHighestRow(); $r++) {
    $nks = sprintf('%05d', (int)$sheetKirim->getCell('C' . $r)->getValue());
    $ruta = (int)$sheetKirim->getCell('D' . $r)->getValue();
    $selesai = trim((string)$sheetKirim->getCell('E' . $r)->getValue());
    $modul = trim((string)$sheetKirim->getCell('F' . $r)->getValue());
    $kp = trim((string)$sheetKirim->getCell('G' . $r)->getValue());
    $tgl = trim((string)$sheetKirim->getCell('H' . $r)->getFormattedValue());
    $ttdSos = trim((string)$sheetKirim->getCell('I' . $r)->getValue());
    $ttdIpds = trim((string)$sheetKirim->getCell('J' . $r)->getValue());

    if ($nks !== '00000' && $ruta > 0) {
        $dokKirimData[$nks][$ruta] = [
            'selesai' => $selesai,
            'modul' => $modul,
            'kp' => $kp,
            'tgl' => $tgl,
            'ttd_sos' => $ttdSos,
            'ttd_ipds' => $ttdIpds,
        ];
    }
}

echo "Total NKS in Rekap: " . count($rekapData) . "\n";
echo "Total NKS in DokKirim: " . count($dokKirimData) . "\n";

// Check overlap
$adaCount = 0;
$adaWithDokKirimDate = 0;
$distinctDates = [];
$distinctSos = [];
$distinctIpds = [];

foreach ($rekapData as $nks => $rutas) {
    foreach ($rutas as $noRuta => $st) {
        if ($st === 'ADA') {
            $adaCount++;
            if (isset($dokKirimData[$nks][$noRuta])) {
                $dk = $dokKirimData[$nks][$noRuta];
                if ($dk['tgl'] !== '') {
                    $adaWithDokKirimDate++;
                    $distinctDates[$dk['tgl']] = ($distinctDates[$dk['tgl']] ?? 0) + 1;
                }
                if ($dk['ttd_sos'] !== '') {
                    $distinctSos[$dk['ttd_sos']] = ($distinctSos[$dk['ttd_sos']] ?? 0) + 1;
                }
                if ($dk['ttd_ipds'] !== '') {
                    $distinctIpds[$dk['ttd_ipds']] = ($distinctIpds[$dk['ttd_ipds']] ?? 0) + 1;
                }
            }
        }
    }
}

echo "Total 'ADA' in Rekap: {$adaCount}\n";
echo "Total 'ADA' with date in DokKirim: {$adaWithDokKirimDate}\n";
echo "Dates in DokKirim for 'ADA':\n";
print_r($distinctDates);
echo "TTD Sos in DokKirim:\n";
print_r($distinctSos);
echo "TTD IPDS in DokKirim:\n";
print_r($distinctIpds);
