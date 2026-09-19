<?php
$dirs = ['app', 'config', 'database', 'tests', 'scripts'];
$errorCount = 0;
$checked = 0;

foreach ($dirs as $d) {
    if (!is_dir(__DIR__ . '/../' . $d)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../' . $d));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), ['php', 'phtml'], true)) {
            $checked++;
            $path = $file->getPathname();
            $cmd = 'php -l "' . $path . '" 2>&1';
            exec($cmd, $output, $returnVar);
            if ($returnVar !== 0) {
                echo "LINT ERROR: {$path}\n";
                echo implode("\n", $output) . "\n";
                $errorCount++;
            }
            $output = [];
        }
    }
}

echo "Checked {$checked} files. Errors: {$errorCount}\n";
if ($errorCount > 0) exit(1);
