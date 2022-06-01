<?php

namespace App\Service;

class FareEstimatorService
{
    protected $iddleTimes = [];
    protected $result = [];

    public function calculateDistanceBetweenPoints(array $previousRow, array $row): float
    {
        if ($previousRow[0] != $row[0]) {
            return 0;
        }

        $previousLatitude = $previousRow[1];
        $previousLongitude = $previousRow[2];

        $actualLatitude = $row[1];
        $actualLongitude = $row[2];

        $distanceInMeters = $this->haversineGreatCircleDistance($previousLatitude, $previousLongitude, $actualLatitude, $actualLongitude);

        return $distanceInMeters;
    }

    public function velocityRequirement($previousRow, $row)
    {
        if ($previousRow[0] != $row[0]) {
            return;
        }

        $distanceInMeters = $this->calculateDistanceBetweenPoints($previousRow, $row);

        $previousTimestamp = $previousRow[3];
        $actualTimestamp = $row[3];
        $diff = $actualTimestamp - $previousTimestamp;

        $kmPerHour = $this->getKmPerHour($diff, $distanceInMeters);

        if ($kmPerHour > 100) {
            return false;
        }

        return true;
    }

    public function compileResult(): array
    {
        $idleFare = 11.90;
        $flagFare = 1.30;
        $finalResult = [];

        foreach ($this->result as $key => $item) {
            $iddleTime = $this->iddleTimes[$key];
            $this->result[$key] += $flagFare;
            if ($iddleTime >= 3600) {
                $this->result[$key] += $idleFare;
            }
            $finalResult[] = [$key, round($this->result[$key], 2)];
        }

        return $finalResult;
    }

    public function getFareAmountBetweenTwoLocations($previousRow, $row): array
    {
        $distanceInMeters = $this->calculateDistanceBetweenPoints($previousRow, $row);
        $fareAmount = 1.30;
        $previousTimestamp = $previousRow[3];
        $actualTimestamp = $row[3];
        $differenceInSecondsBetweenTimestamps = $actualTimestamp - $previousTimestamp;

        $result = ['totalFare' => 0, 'idleTime' => 0];

        $kmPerHour = $this->getKmPerHour($differenceInSecondsBetweenTimestamps, $distanceInMeters);

        if ($kmPerHour < 10) {
            $result['idleTime'] = $differenceInSecondsBetweenTimestamps;

            return $result;
        }

        $previousDate = new \DateTime();
        $previousDate->setTimestamp($previousTimestamp);
        $hour = $previousDate->format('H')."\n";

        if ($hour > 5 && 23 == $hour) {
            $fareAmount = 0.74;
        }

        $result['totalFare'] = $fareAmount * ($distanceInMeters / 1000);

        return $result;
    }

    protected function getKmPerHour(mixed $diff, $distanceInMeters): float
    {
        $kmPerHour = 0;

        if ($diff > 0) {
            $kmPerHour = ($distanceInMeters / $diff) * 3.6;
        }

        return $kmPerHour;
    }

    public function calculateFare(): array
    {
        $filteredPath = 'filteredPaths.csv';
        $filteredFile = fopen($filteredPath, 'r');
        $previousRow = null;
        while (($row = fgetcsv($filteredFile)) !== false) {
            if (isset($previousRow)) {
                $result = $this->getFareAmountBetweenTwoLocations($previousRow, $row);
                if (!isset($this->result[$row[0]])) {
                    $this->result[$row[0]] = 0;
                }
                $this->result[$row[0]] += $result['totalFare'];
                $this->iddleTimes[$row[0]] = $result['idleTime'];
            }

            $previousRow = $row;
        }

        fclose($filteredFile);

        return $this->compileResult();
    }

    protected function haversineGreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
