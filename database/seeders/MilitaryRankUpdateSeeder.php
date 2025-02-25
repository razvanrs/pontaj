<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\MilitaryRank;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MilitaryRankUpdateSeeder extends Seeder
{
    /**
     * Normalize text by removing diacritics and standardizing format
     *
     * @param string $text
     * @return string
     */
    private function normalizeText($text)
    {
        if (empty($text)) {
            return '';
        }

        $text = trim($text);

        // Convert to lowercase
        $text = Str::lower($text);

        // Replace Romanian specific characters
        $replacements = [
            'ț' => 't',
            'ț' => 't', // different Unicode representation
            'ţ' => 't',
            'ș' => 's',
            'ș' => 's', // different Unicode representation
            'ş' => 's',
            'ă' => 'a',
            'â' => 'a',
            'î' => 'i',
            'é' => 'e',
            'ë' => 'e',
        ];

        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        // Remove all dots and hyphens
        $text = str_replace(['.', '-'], ' ', $text);

        // Normalize spaces
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove any remaining special characters
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);

        return trim($text);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create normalized variations of military ranks
        $militaryRanks = MilitaryRank::all()->mapWithKeys(function ($rank) {
            // Create variations of each rank
            $variations = [
                $rank->name,
                $rank->abbreviation,
                str_replace('-', ' ', $rank->name),
                str_replace(['-', '.'], ' ', $rank->abbreviation),
                'personal civil', // Special case
                'personal contractual', // Alternative for personal civil
            ];

            // Map all normalized variations to the rank ID
            return collect($variations)->mapWithKeys(function ($variation) use ($rank) {
                $normalized = $this->normalizeText($variation);
                return [$normalized => $rank->id];
            })->all();
        })->all();

        // Debug the mapping
        \Log::debug('Military rank mappings:', $militaryRanks);

        // Read the Excel file
        $spreadsheet = IOFactory::load(storage_path('imports/employees.xlsx'));
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Get header row and convert to lowercase
        $headers = array_map('strtolower', array_map('trim', $rows[0]));

        // Find the column indexes
        $erpIdIndex = array_search('erp_id', $headers);
        $militaryRankIndex = array_search('military_rank', $headers);
        $militaryRankTypeIndex = array_search('military_rank_type_id', $headers);

        if ($erpIdIndex === false || $militaryRankIndex === false || $militaryRankTypeIndex === false) {
            throw new \Exception('Required columns not found in Excel file');
        }

        // Process employees in chunks
        Employee::whereNull('military_rank_id')
            ->chunkById(100, function ($employees) use ($militaryRanks, $rows, $erpIdIndex, $militaryRankIndex, $militaryRankTypeIndex) {
                foreach ($employees as $employee) {
                    // Find the corresponding row in Excel
                    $excelRow = null;
                    foreach (array_slice($rows, 1) as $row) {
                        if ($row[$erpIdIndex] == $employee->erp_id) {
                            $excelRow = $row;
                            break;
                        }
                    }

                    if (!$excelRow) {
                        \Log::warning("No Excel row found for employee ERP ID: {$employee->erp_id}");
                        continue;
                    }

                    $rankFromExcel = $excelRow[$militaryRankIndex];

                    // Debug the rank being processed
                    \Log::debug("Rank to match: " . $rankFromExcel);

                    // Normalize the rank from Excel
                    $normalizedRank = $this->normalizeText($rankFromExcel);

                    // Find matching rank ID
                    $rankId = $militaryRanks[$normalizedRank] ?? null;

                    // Debug the found ID
                    \Log::debug("RankId: " . ($rankId ?? ''));

                    if ($rankId) {
                        DB::table('employees')
                            ->where('id', $employee->id)
                            ->update([
                                'military_rank_id' => $rankId,
                                'military_rank_type_id' => $excelRow[$militaryRankTypeIndex]
                            ]);
                    } else {
                        // Log unmatched ranks for manual review
                        \Log::warning("Could not match military rank: {$rankFromExcel} for employee ERP ID: {$employee->erp_id}");
                    }
                }
            });

        // Output summary of updates
        $updatedCount = Employee::whereNotNull('military_rank_id')->count();
        $totalCount = Employee::count();
        $this->command->info("Updated military ranks for {$updatedCount} out of {$totalCount} employees");
    }
}
