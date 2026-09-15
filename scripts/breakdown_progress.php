<?php
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load(__DIR__ . '/../data/template_dokkirimkab _15092026.xls');
$sheet = $spreadsheet->getSheetByName('data');

$nksProgress = [];

for ($r = 4; $r <= 283; $r++) {
    $nks = str_pad(trim((string)$sheet->getCell("C$r")->getValue()), 5, '0', STR_PAD_LEFT);
    $ruta = (int)$sheet->getCell("D$r")->getValue();
    $selesai = trim((string)$sheet->getCell("E$r")->getValue());
    $modul = $sheet->getCell("F$r")->getValue();
    $kp = $sheet->getCell("G$r")->getValue();
    $tgl = $sheet->getCell("H$r")->getFormattedValue();

    if (!isset($nksProgress[$nks])) {
        $nksProgress[$nks] = [
            'total_ruta' => 0,
            'sudah' => 0,
            'rutas_sudah' => [],
            'modul_1' => 0,
            'kp_1' => 0,
            'tgl' => []
        ];
    }

    $nksProgress[$nks]['total_ruta']++;
    if (strtolower($selesai) === 'sudah') {
        $nksProgress[$nks]['sudah']++;
        $nksProgress[$nks]['rutas_sudah'][] = $ruta;
        if ((string)$modul === '1') $nksProgress[$nks]['modul_1']++;
        if ((string)$kp === '1') $nksProgress[$nks]['kp_1']++;
        if ($tgl) $nksProgress[$nks]['tgl'][$tgl] = true;
    }
}

echo "NKS Progress Breakdown:\n";
echo str_pad("NKS", 8) . str_pad("Progress", 12) . str_pad("Modul=1", 10) . str_pad("KP=1", 10) . str_pad("Ruta Selesai", 25) . "Tgl Pengiriman\n";
echo str_repeat("-", 80) . "\n";

$totalSudah = 0;
foreach ($nksProgress as $nks => $d) {
    $totalSudah += $d['sudah'];
    $tglStr = implode(', ', array_keys($d['tgl']));
    $rutas = empty($d['rutas_sudah']) ? '-' : implode(',', $d['rutas_sudah']);
    echo str_pad($nks, 8) 
       . str_pad("{$d['sudah']}/{$d['total_ruta']}", 12) 
       . str_pad((string)$d['modul_1'], 10) 
       . str_pad((string)$d['kp_1'], 10) 
       . str_pad($rutas, 25) 
       . $tglStr . "\n";
}

echo str_repeat("-", 80) . "\n";
echo "Total Ruta Selesai di template: {$totalSudah} / 280\n";
