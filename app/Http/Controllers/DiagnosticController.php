<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiagnosticController extends Controller
{
    /**
     * Vérifier la configuration PHP et les permissions
     */
    public function checkConfig()
    {
        $config = [
            'php_version' => PHP_VERSION,
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
            'memory_limit' => ini_get('memory_limit'),
            'file_uploads' => ini_get('file_uploads') ? 'enabled' : 'disabled',
            'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
        ];

        $permissions = [
            'storage_app_public' => [
                'path' => storage_path('app/public'),
                'exists' => is_dir(storage_path('app/public')),
                'writable' => is_writable(storage_path('app/public')),
                'permissions' => is_dir(storage_path('app/public')) ? substr(sprintf('%o', fileperms(storage_path('app/public'))), -4) : null,
            ],
            'storage_app_public_evenements' => [
                'path' => storage_path('app/public/evenements'),
                'exists' => is_dir(storage_path('app/public/evenements')),
                'writable' => is_writable(storage_path('app/public/evenements')),
                'permissions' => is_dir(storage_path('app/public/evenements')) ? substr(sprintf('%o', fileperms(storage_path('app/public/evenements'))), -4) : null,
            ],
            'storage_logs' => [
                'path' => storage_path('logs'),
                'exists' => is_dir(storage_path('logs')),
                'writable' => is_writable(storage_path('logs')),
                'permissions' => is_dir(storage_path('logs')) ? substr(sprintf('%o', fileperms(storage_path('logs'))), -4) : null,
            ],
        ];

        // Test d'écriture
        $write_test = false;
        $test_file = storage_path('app/public/test_diagnostic.txt');
        try {
            $write_test = file_put_contents($test_file, 'test') !== false;
            if ($write_test && file_exists($test_file)) {
                unlink($test_file);
            }
        } catch (\Exception $e) {
            $write_test = false;
        }

        $recommendations = [];

        // Vérifier upload_max_filesize
        $upload_bytes = $this->convertToBytes(ini_get('upload_max_filesize'));
        if ($upload_bytes < 5 * 1024 * 1024) {
            $recommendations[] = 'upload_max_filesize est trop petit (< 5M). Recommandé: 10M minimum';
        }

        // Vérifier post_max_size
        $post_bytes = $this->convertToBytes(ini_get('post_max_size'));
        if ($post_bytes < 10 * 1024 * 1024) {
            $recommendations[] = 'post_max_size est trop petit (< 10M). Recommandé: 20M minimum';
        }

        // Vérifier que post_max_size > upload_max_filesize
        if ($post_bytes <= $upload_bytes) {
            $recommendations[] = 'post_max_size doit être plus grand que upload_max_filesize';
        }

        // Vérifier les permissions
        if (!is_writable(storage_path('app/public'))) {
            $recommendations[] = 'Le dossier storage/app/public n\'est pas accessible en écriture. Exécuter: chmod -R 775 storage/app/public';
        }

        if (!is_dir(storage_path('app/public/evenements'))) {
            $recommendations[] = 'Le dossier storage/app/public/evenements n\'existe pas. Exécuter: mkdir -p storage/app/public/evenements';
        }

        return response()->json([
            'status' => 'ok',
            'config' => $config,
            'permissions' => $permissions,
            'write_test' => $write_test,
            'recommendations' => $recommendations,
            'php_ini_file' => php_ini_loaded_file(),
        ]);
    }

    /**
     * Tester l'upload d'un fichier
     */
    public function testUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        try {
            $file = $request->file('file');
            
            $info = [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'size_human' => $this->formatBytes($file->getSize()),
                'is_valid' => $file->isValid(),
                'error' => $file->getError(),
                'error_message' => $this->getUploadErrorMessage($file->getError()),
            ];

            if ($file->isValid()) {
                $path = $file->store('diagnostic', 'public');
                $info['stored_path'] = $path;
                $info['full_path'] = storage_path('app/public/' . $path);
                
                // Supprimer le fichier de test
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Upload réussi',
                    'file_info' => $info,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Fichier invalide',
                    'file_info' => $info,
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    private function convertToBytes($value)
    {
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

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function getUploadErrorMessage($code)
    {
        $errors = [
            UPLOAD_ERR_OK => 'Pas d\'erreur',
            UPLOAD_ERR_INI_SIZE => 'Fichier > upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'Fichier > MAX_FILE_SIZE du formulaire',
            UPLOAD_ERR_PARTIAL => 'Fichier partiellement uploadé',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier uploadé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Échec d\'écriture sur le disque',
            UPLOAD_ERR_EXTENSION => 'Extension PHP a arrêté l\'upload',
        ];

        return $errors[$code] ?? 'Erreur inconnue';
    }
}
