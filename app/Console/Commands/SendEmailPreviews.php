<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SendEmailPreviews extends Command
{
    protected $signature   = 'emails:preview {--to=test@hahaha.mg : Adresse destinataire}';
    protected $description = 'Envoie tous les templates email vers une adresse de test pour vérification visuelle';

    public function handle(): int
    {
        $to = $this->option('to');
        $this->info("📧 Envoi de tous les templates email vers : {$to}");
        $this->newLine();

        $sent   = 0;
        $errors = 0;

        // ── Données factices ────────────────────────────────────────────────
        $fakeUser = (object)[
            'name'  => 'Jean Dupont',
            'email' => $to,
        ];

        $fakeTalent = (object)[
            'name'  => 'Sophie Martin',
            'email' => $to,
        ];

        $fakeEntreprise = (object)[
            'nom'  => 'TechCorp Solutions',
            'email' => $to,
        ];

        $fakeEvenement = (object)[
            'titre' => 'Talenteed Job Fair – Printemps 2026',
        ];

        $fakeEntretien = (object)[
            'talent'      => $fakeTalent,
            'entreprise'  => $fakeEntreprise,
            'evenement'   => $fakeEvenement,
            'date'        => now()->addDays(3)->toDateString(),
            'heure_debut' => '10:15:00',
            'heure_fin'   => '10:30:00',
            'statut'      => 'confirme',
        ];

        $fakeEntretienRefuse = (object)[
            'talent'      => $fakeTalent,
            'entreprise'  => $fakeEntreprise,
            'evenement'   => $fakeEvenement,
            'date'        => now()->addDays(3)->toDateString(),
            'heure_debut' => '14:00:00',
            'heure_fin'   => '14:15:00',
            'statut'      => 'refuse',
        ];

        // ── Liste des mails à envoyer ────────────────────────────────────────
        $mails = [

            '1. Bienvenue talent' => fn() => Mail::send(
                'emails.welcome-talent',
                ['user' => $fakeUser],
                fn($m) => $m->to($to)->subject('[PREVIEW] Bienvenue sur Talenteed')
            ),

            '2. Compte entreprise créé' => fn() => Mail::send(
                'emails.entreprise-created',
                [
                    'nomEntreprise' => 'TechCorp Solutions',
                    'email'         => 'techcorp@example.com',
                    'password'      => 'Xk9#mP2qR',
                ],
                fn($m) => $m->to($to)->subject('[PREVIEW] Vos identifiants Talenteed')
            ),

            '3. Entretien réservé (talent)' => fn() => Mail::send(
                'emails.entretien-reserve',
                ['entretien' => $fakeEntretien, 'destinataire' => 'talent'],
                fn($m) => $m->to($to)->subject('[PREVIEW] Entretien réservé — Vue talent')
            ),

            '4. Entretien réservé (entreprise)' => fn() => Mail::send(
                'emails.entretien-reserve',
                ['entretien' => $fakeEntretien, 'destinataire' => 'entreprise'],
                fn($m) => $m->to($to)->subject('[PREVIEW] Entretien réservé — Vue entreprise')
            ),

            '5. Entretien réservé (admin)' => fn() => Mail::send(
                'emails.entretien-reserve',
                ['entretien' => $fakeEntretien, 'destinataire' => 'admin'],
                fn($m) => $m->to($to)->subject('[PREVIEW] Entretien réservé — Vue admin')
            ),

            '6. Entretien confirmé' => fn() => Mail::send(
                'emails.entretien-reponse',
                ['entretien' => $fakeEntretien],
                fn($m) => $m->to($to)->subject('[PREVIEW] Votre entretien est confirmé')
            ),

            '7. Entretien refusé' => fn() => Mail::send(
                'emails.entretien-reponse',
                ['entretien' => $fakeEntretienRefuse],
                fn($m) => $m->to($to)->subject('[PREVIEW] Entretien non disponible')
            ),

            '8. Rappel entretien (1h avant)' => fn() => Mail::send(
                'emails.rappel-entretien',
                ['entretien' => $fakeEntretien],
                fn($m) => $m->to($to)->subject('[PREVIEW] Rappel — Votre entretien dans 1 heure')
            ),

            '9. Demande de feedback' => fn() => Mail::send(
                'emails.demander-feedback',
                ['entretien' => $fakeEntretien],
                fn($m) => $m->to($to)->subject('[PREVIEW] Comment s\'est passé votre entretien ?')
            ),

            '10. Demande participation événement (admin)' => fn() => Mail::send(
                'emails.evenement-demande',
                [
                    'nomEntreprise'    => 'TechCorp Solutions',
                    'nomEvenement'     => 'Talenteed Job Fair – Printemps 2026',
                    'messageEntreprise' => 'Nous serions ravis de participer à cet événement et de rencontrer vos talents.',
                ],
                fn($m) => $m->to($to)->subject('[PREVIEW] Nouvelle demande de participation événement')
            ),
        ];

        // ── Envoi avec barre de progression ─────────────────────────────────
        $bar = $this->output->createProgressBar(count($mails));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $bar->start();

        foreach ($mails as $label => $send) {
            $bar->setMessage($label);
            try {
                $send();
                $this->output->write("\n  <info>✓</info> {$label}");
                $sent++;
            } catch (\Throwable $e) {
                $this->output->write("\n  <error>✗</error> {$label} — " . $e->getMessage());
                $errors++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ {$sent} email(s) envoyé(s) vers {$to}");
        if ($errors > 0) {
            $this->warn("⚠️  {$errors} erreur(s) — vérifiez la config MAIL_* dans .env");
        }

        $this->newLine();
        $this->line("💡 Pour visualiser : <comment>http://localhost:1080</comment> (MailDev)");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
