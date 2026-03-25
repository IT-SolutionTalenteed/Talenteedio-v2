<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ImportController extends Controller
{
    /**
     * H-08 — Lance l'import candidats depuis un fichier XLS uploadé.
     */
    public function importCandidats(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx|max:20480',
        ]);

        $path = $request->file('file')->storeAs('imports', 'candidats_import.xls', 'local');
        $fullPath = storage_path("app/{$path}");

        // Capturer la sortie de la commande artisan
        Artisan::call('import:candidats', [
            'file'       => $fullPath,
            '--dry-run'  => $request->boolean('dry_run'),
        ]);

        $output = Artisan::output();

        // Parser les stats depuis la sortie tabulaire
        $stats = $this->parseStats($output);

        return response()->json([
            'output' => $output,
            'stats'  => $stats,
            'dry_run' => $request->boolean('dry_run'),
        ]);
    }

    private function parseStats(string $output): array
    {
        // Cherche les valeurs dans la ligne du tableau de résultats
        // Format : | 3733  | 4  | 0  | 214 |
        if (preg_match('/\|\s*(\d+)\s*\|\s*(\d+)\s*\|\s*(\d+)\s*\|\s*(\d+)\s*\|/', $output, $matches)) {
            return [
                'created'  => (int) $matches[1],
                'skipped'  => (int) $matches[2],
                'existing' => (int) $matches[3],
                'errors'   => (int) $matches[4],
            ];
        }
        return ['created' => 0, 'skipped' => 0, 'existing' => 0, 'errors' => 0];
    }
}
