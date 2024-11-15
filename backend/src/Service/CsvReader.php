<?php

namespace App\Service;

use Exception;

readonly class CsvReader
{
    /** @throws Exception */
    public function read(string $filePath): array
    {
        $resource = fopen($filePath, 'r');

        if ($resource === false) {
            throw new Exception("Unable to read file.");
        }

        $csvData = [];
        while (($data = fgetcsv($resource, 1000, ',')) !== false) {
            $csvData[] = $data;
        }
        fclose($resource);

        return $csvData;
    }

    /** @throws Exception */
    public function write(string $filePath, array $rows): void
    {
        $resource = fopen($filePath, 'w');

        if ($resource === false) {
            throw new Exception("Unable to open file for writing: " . $filePath);
        }

        foreach ($rows as $row) {
            if (fputcsv($resource, $row) === false) {
                throw new Exception("Unable to write data to CSV file: " . $filePath);
            }
        }
        fclose($resource);
    }
}