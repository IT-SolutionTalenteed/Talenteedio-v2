<?php

namespace App\Console\Commands;

use App\Models\Offre;
use Illuminate\Console\Command;

class ArchiveAllOffres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offres:archive-all {--force : Archiver sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive toutes les offres non-archivées de la base de données';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Offre::notArchived()->count();

        if ($count === 0) {
            $this->info('Aucune offre à archiver.');
            return Command::SUCCESS;
        }

        $this->info("Nombre d'offres à archiver : {$count}");

        if (!$this->option('force')) {
            if (!$this->confirm('Voulez-vous vraiment archiver toutes ces offres ?')) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
        }

        $this->info('Archivage en cours...');
        
        $archived = 0;
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        Offre::notArchived()->chunk(100, function ($offres) use (&$archived, $bar) {
            foreach ($offres as $offre) {
                $offre->archive();
                $archived++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ {$archived} offre(s) archivée(s) avec succès !");

        return Command::SUCCESS;
    }
}

