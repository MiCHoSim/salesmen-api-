<?php

namespace App\Console\Commands;

use App\Models\Salesman;
use App\Services\CodelistService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImportSalesmenCommand extends Command
{
    protected $signature = 'salesmen:import {file=salesmen.csv}';
    protected $description = 'Import salesmen from CSV file';

    public function handle(): int
    {
        $filename = $this->argument('file');

        if (!Storage::exists($filename)) {
            $this->error("File {$filename} not found.");
            return Command::FAILURE;
        }

        $path = Storage::path($filename);
        $handle = fopen($path, 'r');

        if (!$handle) {
            $this->error("Cannot open file {$filename}.");
            return Command::FAILURE;
        }

        // detekcia delimiteru podľa prvého riadku
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            $this->error("CSV file {$filename} is empty.");
            fclose($handle);
            return Command::FAILURE;
        }

        $delimiter = str_contains((string)$firstLine, ';') ? ';' : ',';
        rewind($handle);

        // načítanie hlavičky a trim
        $header = fgetcsv($handle, 0, $delimiter);
        if ($header === false) {
            $this->error("CSV file {$filename} is empty or invalid.");
            fclose($handle);
            return Command::FAILURE;
        }
        $header = array_map(fn($h) => trim(strval($h)), $header);

        $imported = 0;
        $errors = [];

        $validGenders = CodelistService::genderCodes();
        $validMarital = CodelistService::maritalStatusCodes();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($row === [null] || empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                continue; // prázdny riadok
            }

            $row = array_map(fn($v) => trim((string)$v), $row);

            if (count($row) !== count($header)) {
                $errors[] = "Invalid row (wrong column count): " . implode(';', $row);
                continue;
            }

            $data = array_combine($header, $row); // bezpečné, už kontrolujeme count

            // priprava dát pre DB
            $salesmanData = [
                'first_name'     => $data['first_name'] ?? '',
                'last_name'      => $data['last_name'] ?? '',
                'titles_before'  => $this->parseTitles($data['titles_before'] ?? null),
                'titles_after'   => $this->parseTitles($data['titles_after'] ?? null),
                'prosight_id'    => $data['prosight_id'] ?? '',
                'email'          => $data['email'] ?? '',
                'phone'          => $data['phone'] !== '' ? $data['phone'] : null,
                'gender'         => $data['gender'] ?? '',
                'marital_status' => $data['marital_status'] !== '' ? $data['marital_status'] : null,
            ];

            // validácia
            $validator = Validator::make($salesmanData, [
                'first_name'     => 'required|string|min:2|max:50',
                'last_name'      => 'required|string|min:2|max:50',
                'prosight_id'    => 'required|string|size:5|regex:/^\d{5}$/',
                'email'          => 'required|email|max:255',
                'gender'         => ['required', 'string', 'in:' . implode(',', $validGenders)],
                'marital_status' => ['nullable', 'string', 'in:' . implode(',', $validMarital)],
            ]);

            if ($validator->fails()) {
                $errors[] = "Validation failed for row: " . implode(';', $row)
                    . " Errors: " . $validator->errors()->toJson();
                continue;
            }

            // unikátne email a prosight_id
            if (Salesman::where('email', $salesmanData['email'])->exists()) {
                $errors[] = "Email already exists: " . $salesmanData['email'];
                continue;
            }
            if (Salesman::where('prosight_id', $salesmanData['prosight_id'])->exists()) {
                $errors[] = "Prosight ID already exists: " . $salesmanData['prosight_id'];
                continue;
            }

            try {
                Salesman::create($salesmanData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Failed to create salesman: " . $e->getMessage();
            }
        }

        fclose($handle);

        $this->info("Successfully imported {$imported} salesmen.");

        if (!empty($errors)) {
            $this->warn("Errors encountered:");
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->line(" - {$error}");
            }
            if (count($errors) > 10) {
                $this->warn("... and " . (count($errors) - 10) . " more errors.");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Rozdelí titulový reťazec na pole
     *
     * @param string|null $titles
     * @return array<string>|null
     */
    private function parseTitles(?string $titles): ?array
    {
        if (empty($titles)) {
            return null;
        }

        $titlesArray = array_map('trim', explode(',', $titles));
        $titlesArray = array_filter($titlesArray);

        return !empty($titlesArray) ? $titlesArray : null;
    }
}
