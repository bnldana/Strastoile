<?php

namespace App\Console\Commands;

use App\Services\Scraping\AllocineScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ScrapeAllocine extends Command
{
    protected $signature = 'scrape:allocine 
                          {cinema? : Nom spécifique du cinéma à scraper}
                          {--force : Force le scraping même si déjà effectué aujourd\'hui}
                          {--no-delay : Désactive le délai entre chaque scraping}';

    protected $description = 'Scrape les horaires depuis Allociné';

    protected $scraper;
    protected $startTime;

    public function __construct(AllocineScraper $scraper)
    {
        parent::__construct();
        $this->scraper = $scraper;
    }

    public function handle()
    {
        $this->startTime = microtime(true);
        $cinema = $this->argument('cinema');
        $force = $this->option('force');
        $noDelay = $this->option('no-delay');

        try {
            // Log le début de l'opération
            $this->logStart($cinema);

            if ($cinema) {
                $this->scrapeSingleCinema($cinema);
            } else {
                $this->scrapeAllCinemas($noDelay);
            }

            // Log la fin de l'opération
            $this->logCompletion();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logError($e);
            return Command::FAILURE;
        }
    }

    protected function scrapeSingleCinema(string $cinema): void
    {
        if (!array_key_exists($cinema, $this->scraper->getCinemaIds())) {
            $availableCinemas = implode(', ', array_keys($this->scraper->getCinemaIds()));
            $error = "Cinéma '$cinema' non trouvé. Cinémas disponibles : $availableCinemas";
            $this->error($error);
            Log::error($error);
            throw new \InvalidArgumentException($error);
        }

        $this->info("Début du scraping pour $cinema...");

        try {
            $startTime = microtime(true);
            $this->scraper->scrapeCinema($cinema, $this->scraper->getCinemaIds()[$cinema]);
            $duration = round(microtime(true) - $startTime, 2);

            $successMessage = "Scraping terminé pour $cinema (durée: {$duration}s)";
            $this->info($successMessage);
            Log::info($successMessage);
        } catch (\Exception $e) {
            $error = "Erreur lors du scraping de $cinema: " . $e->getMessage();
            $this->error($error);
            Log::error($error, [
                'cinema' => $cinema,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function scrapeAllCinemas(bool $noDelay): void
    {
        $cinemas = $this->scraper->getCinemaIds();
        $total = count($cinemas);
        $success = 0;
        $failed = 0;

        Log::info("Démarrage du scraping complet", ['total_cinemas' => $total]);

        foreach ($cinemas as $cinemaName => $cinemaId) {
            try {
                // Utilisation correcte de la concaténation de chaînes
                $this->info("\nTraitement de {$cinemaName} (" . ($success + $failed + 1) . "/{$total})");
                $startTime = microtime(true);

                $this->scraper->scrapeCinema($cinemaName, $cinemaId);

                $duration = round(microtime(true) - $startTime, 2);
                $success++;

                $this->info("✓ {$cinemaName} terminé (durée: {$duration}s)");
                Log::info("Cinéma scrapé avec succès", [
                    'cinema' => $cinemaName,
                    'duration' => $duration
                ]);

                if (!$noDelay && $success + $failed < $total) {
                    $this->info("Attente de 2 secondes...");
                    sleep(2);
                }
            } catch (\Exception $e) {
                $failed++;
                $error = "✗ Erreur pour {$cinemaName}: " . $e->getMessage();
                $this->error($error);
                Log::error($error, [
                    'cinema' => $cinemaName,
                    'exception' => $e
                ]);
            }
        }

        $this->displaySummary($success, $failed);
    }

    protected function logStart(?string $cinema): void
    {
        $message = $cinema
            ? "Démarrage du scraping pour le cinéma: $cinema"
            : "Démarrage du scraping pour tous les cinémas";

        Log::info($message, [
            'started_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'cinema' => $cinema ?? 'all'
        ]);
    }

    protected function logCompletion(): void
    {
        $duration = round(microtime(true) - $this->startTime, 2);
        $message = "Scraping terminé en {$duration} secondes";

        $this->info("\n" . $message);
        Log::info($message, [
            'duration' => $duration,
            'completed_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }

    protected function logError(\Exception $e): void
    {
        $error = "Erreur fatale lors du scraping: " . $e->getMessage();
        $this->error($error);
        Log::error($error, [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
    }

    protected function displaySummary(int $success, int $failed): void
    {
        $total = $success + $failed;
        $duration = round(microtime(true) - $this->startTime, 2);

        $summary = "\nRésumé du scraping:";
        $summary .= "\n✓ Réussis: $success";
        $summary .= "\n✗ Échoués: $failed";
        $summary .= "\nDurée totale: {$duration}s";

        $this->info($summary);
        Log::info("Résumé du scraping", [
            'success' => $success,
            'failed' => $failed,
            'total' => $total,
            'duration' => $duration
        ]);
    }
}
