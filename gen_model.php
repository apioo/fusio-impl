<?php

$schemas = [
    [
        'source' => 'resources/common_schema.json',
        'target' => 'src/Model',
        'config' => 'namespace=Fusio\Impl\Model',
    ],
    [
        'source' => 'resources/backend_schema.json',
        'target' => 'src/Backend/Model',
        'config' => 'namespace=Fusio\Impl\Backend\Model&mapping[common]=Fusio\Impl\Model',
    ],
    [
        'source' => 'resources/consumer_schema.json',
        'target' => 'src/Consumer/Model',
        'config' => 'namespace=Fusio\Impl\Consumer\Model&mapping[common]=Fusio\Impl\Model',
    ],
    [
        'source' => 'resources/system_schema.json',
        'target' => 'src/System/Model',
        'config' => 'namespace=Fusio\Impl\System\Model&mapping[common]=Fusio\Impl\Model',
    ]
];

foreach ($schemas as $row) {
    $folder = __DIR__ . '/' . $row['target'];
    if (!is_dir($folder)) {
        continue;
    }

    deleteFilesInFolder($folder);

    $cmd = sprintf('php vendor/psx/schema/bin/schema schema:parse %s %s --format=php --config=%s', ...array_values(array_map('escapeshellarg', $row)));

    echo 'Generate ' . $row['source'] . "\n";
    echo '> ' . $cmd . "\n";

    shell_exec($cmd);
}

function deleteFilesInFolder(string $folder): void
{
    $files = scandir($folder);
    foreach ($files as $file) {
        if ($file[0] === '.') {
            continue;
        }

        $path = $folder . '/' . $file;
        if (!is_file($path)) {
            continue;
        }

        unlink($path);
    }
}
