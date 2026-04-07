<?php
/**
 * Script pour vérifier la configuration PHP pour les uploads
 * Usage: php check_php_config.php
 */

echo "=== Configuration PHP pour les uploads ===\n\n";

$configs = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
    'memory_limit' => ini_get('memory_limit'),
    'file_uploads' => ini_get('file_uploads') ? 'On' : 'Off',
    'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
];

foreach ($configs as $key => $value) {
    printf("%-25s : %s\n", $key, $value);
}

echo "\n=== Vérification des permissions ===\n\n";

$dirs = [
    'storage/app/public' => storage_path('app/public'),
    'storage/app/public/evenements' => storage_path('app/public/evenements'),
    'storage/logs' => storage_path('logs'),
];

foreach ($dirs as $name => $path) {
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
    
    printf("%-35s : %s (perms: %s, writable: %s)\n", 
        $name, 
        $exists ? '✓ Existe' : '✗ N\'existe pas',
        $perms,
        $writable ? 'Oui' : 'Non'
    );
}

echo "\n=== Recommandations ===\n\n";

$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');

function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int) $value;
    
    switch($last) {
        case 'g': $value *= 1024;
        case 'm': $value *= 1024;
        case 'k': $value *= 1024;
    }
    
    return $value;
}

$upload_bytes = convertToBytes($upload_max);
$post_bytes = convertToBytes($post_max);

if ($upload_bytes < 5 * 1024 * 1024) {
    echo "⚠️  upload_max_filesize ($upload_max) est trop petit. Recommandé: 10M minimum\n";
}

if ($post_bytes < 10 * 1024 * 1024) {
    echo "⚠️  post_max_size ($post_max) est trop petit. Recommandé: 20M minimum\n";
}

if ($post_bytes <= $upload_bytes) {
    echo "⚠️  post_max_size doit être plus grand que upload_max_filesize\n";
}

if (!is_writable(storage_path('app/public'))) {
    echo "⚠️  Le dossier storage/app/public n'est pas accessible en écriture\n";
    echo "   Exécuter: chmod -R 775 storage/app/public\n";
}

if (!is_dir(storage_path('app/public/evenements'))) {
    echo "⚠️  Le dossier storage/app/public/evenements n'existe pas\n";
    echo "   Exécuter: mkdir -p storage/app/public/evenements\n";
}

echo "\n=== Test d'upload ===\n\n";

$test_file = storage_path('app/public/test_upload.txt');
$test_content = 'Test upload - ' . date('Y-m-d H:i:s');

if (file_put_contents($test_file, $test_content)) {
    echo "✓ Test d'écriture réussi\n";
    unlink($test_file);
} else {
    echo "✗ Échec du test d'écriture\n";
}

echo "\n=== Fichier php.ini utilisé ===\n\n";
echo php_ini_loaded_file() . "\n";

echo "\n=== Fichiers .ini additionnels ===\n\n";
$additional = php_ini_scanned_files();
if ($additional) {
    echo $additional . "\n";
} else {
    echo "Aucun\n";
}

echo "\n";
