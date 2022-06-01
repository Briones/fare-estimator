<?php

namespace App\Service;

class CSVService
{
    protected $resultFile = null;
    protected array $result = [];
    protected FareEstimatorService $fareEstimatorService;

    public function __construct(FareEstimatorService $fareEstimatorService)
    {
        $this->fareEstimatorService = $fareEstimatorService;
    }

    public function filterData(string $originalCSVFile = 'paths.csv', string $filteredCSVFile = 'filteredPaths.csv')
    {
        $originalFile = fopen($originalCSVFile, 'r');
        $filteredFile = fopen($filteredCSVFile, 'w');
        $previousRow = null;

        while (($row = fgetcsv($originalFile)) !== false) {
            $validRow = $this->checkRowData($row);

            if (!$validRow) {
                continue;
            }

            if (isset($previousRow)) {
                $velocityRequirement = $this->fareEstimatorService->velocityRequirement($previousRow, $row);
                if ($velocityRequirement) {
                    fputcsv($filteredFile, $row);
                }
            } else {
                fputcsv($filteredFile, $row);
            }

            $previousRow = $row;
        }

        fclose($originalFile);
        fclose($filteredFile);
    }

    public function createResultFile(string $resultPath = 'result.csv'): void
    {
        $this->resultFile = fopen($resultPath, 'w');
        fputcsv($this->resultFile, ['id_ride', 'fare_estimate']);
    }

    public function closeResultFile(): void
    {
        fclose($this->resultFile);
    }

    public function writeFareInFile(array $result): void
    {
        foreach ($result as $item) {
            fputcsv($this->resultFile, $item);
        }
    }

    /**
     * Validate if the row has consistent amount and typ of data.
     */
    protected function checkRowData(array $row): bool
    {
        if (4 !== count($row)) {
            return false;
        }

        if (!is_numeric($row[0])) {
            return false;
        }

        if (!is_numeric($row[1])) {
            return false;
        }

        if (!is_numeric($row[2])) {
            return false;
        }

        if (!is_numeric($row[3])) {
            return false;
        }

        return true;
    }
}
