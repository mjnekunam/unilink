<?php

namespace App\Services;

use Morilog\Jalali\Jalalian;
use Morilog\Jalali\CalendarUtils;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class HolidayCrawler
{
    public function fetchHolidays()
    {
        // Fetch the HTML content from the URL
        $response = Http::get('https://www.time.ir/fa/eventyear-تقویم-سالانه');

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch data from time.ir");
        }

        $html = $response->body();

        // Create a DomCrawler instance
        $crawler = new Crawler($html);

        $holidays = [];
        $currentYear = Jalalian::now()->getYear(); // Get the current Persian year

        // Use the .eventHoliday filter to find the holiday items
        $crawler->filter('.eventHoliday')->each(function ($node) use (&$holidays, $currentYear) {
            // Extract the raw text
            $rawText = trim($node->text());

            // Regex to match the date, month, and description
            if (preg_match('/^(\d{1,2})\s+(فروردین|اردیبهشت|خرداد|تیر|مرداد|شهریور|مهر|آبان|آذر|دی|بهمن|اسفند)\s+(.+)$/u', $rawText, $matches)) {
                $day = CalendarUtils::convertNumbers($matches[1], true);         // Day
                $month = $this->convertMonthToNumber($matches[2]); // Convert month name to number
                $description = $matches[3];

                // Convert to Jalali date format YYYY/MM/DD
                $formattedDate = sprintf("%04d/%02d/%02d", $currentYear, $month, $day);

                $holidays[] = [
                    'date' => $formattedDate, // Formatted date in YYYY/MM/DD
                    'description' => $description
                ];
            }
        });

        return $holidays;
    }

    // Helper function to map month names to numbers
    private function convertMonthToNumber($month)
    {
        $months = [
            'فروردین' => '01',
            'اردیبهشت' => '02',
            'خرداد' => '03',
            'تیر' => '04',
            'مرداد' => '05',
            'شهریور' => '06',
            'مهر' => '07',
            'آبان' => '08',
            'آذر' => '09',
            'دی' => '10',
            'بهمن' => '11',
            'اسفند' => '12',
        ];

        return $months[$month] ?? null;
    }
}
