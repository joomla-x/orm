<?php
$rootDir = dirname(__DIR__);

require_once $rootDir . '/vendor/autoload.php';

if (!defined('JPATH_ROOT')) {
    define('JPATH_ROOT', $rootDir);
}

$dataDir  = $rootDir . '/tests/data';
$origDir  = $dataDir . '/original';
if (file_exists($dataDir . '/sqlite.test.db')) {
    unlink($dataDir . '/sqlite.test.db');
}

if (file_exists($dataDir . '/sqlite.test.db-journal')) {
    unlink($dataDir . '/sqlite.test.db-journal');
}

echo "Copying sqlite.test.db\n";
copy($origDir . '/sqlite.test.db', $dataDir . '/sqlite.test.db');

foreach (glob("$origDir/*.csv") as $origFile) {
    $dataFile = basename($origFile);
    echo "Copying $dataFile\n";
    copy($origFile, $dataDir . '/' . $dataFile);
}
