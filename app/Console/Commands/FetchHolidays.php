<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HolidayCrawler;
use Illuminate\Support\Facades\Storage;

class FetchHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch holidays and save them into a JSON file';

    /**
     * Execute the console command.
     */
    public function handle(HolidayCrawler $holidayCrawler)
    {
        $filePath = storage_path('app\holidays.json');

        // Check if update is needed
        if (!$this->needsUpdate($filePath)) {
            $this->info('Holidays are already up-to-date.');
            return;
        }

        // Fetch holidays
        try {
            $holidays = $holidayCrawler->fetchHolidays();

            // Save holidays to the JSON file
            Storage::put('holidays.json', json_encode($holidays, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            $this->info("Holidays successfully updated and saved to {$filePath}");
        } catch (\Exception $e) {
            $this->error('Failed to fetch and save holidays: ' . $e->getMessage());
        }
    }

    /**
     * Determine if the holidays.json file needs to be updated.
     *
     * @param string $filePath
     * @return bool
     */
    private function needsUpdate(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return true;
        }

        $fileContents = file_get_contents($filePath);
        $data = json_decode($fileContents, true);
        $lastUpdated = $data['last_updated'] ?? null;
        $currentYear = now()->year;

        return !$lastUpdated || date('Y', strtotime($lastUpdated)) < $currentYear;
    }
}
