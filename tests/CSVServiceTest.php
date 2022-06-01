<?php

namespace App\Tests;

use App\Service\CSVService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CSVServiceTest extends KernelTestCase
{

    public function testResultFileIsCreated(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $csvService = $container->get(CSVService::class);

        $csvService->createResultFile('testResult.csv');
        $this->assertFileExists('testResult.csv');

    }

    public function testResultFileHaveHeaders(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $csvService = $container->get(CSVService::class);
        $csvService->createResultFile('testResult.csv');
        $fileResult = fopen('testResult.csv', 'r');
        $row = fgetcsv($fileResult);
        $expected = [
            'id_ride',
            'fare_estimate'
        ];
        $this->assertEquals($row, $expected);
        unlink('pathsFilteredTest.csv');
    }

    public function testIsFilteringData(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $csvService = $container->get(CSVService::class);
        $csvService->filterData('pathsTest.csv', 'pathsFilteredTest.csv');
        $fileResult = fopen('pathsFilteredTest.csv', 'r');

        while (($row = fgetcsv($fileResult)) !== false) {
            $result[] = $row;
        }

        $this->assertCount(2, $result);
        unlink('pathsFilteredTest.csv');
    }
}
