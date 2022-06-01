<?php

namespace App\Tests;

use App\Service\CSVService;
use App\Service\FareEstimatorService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FareEstimatorServiceTest extends KernelTestCase
{

    public function testDistanceCalculatedCorrectly(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $fareEstimatorService = $container->get(FareEstimatorService::class);
        $testRow = ['1','37.966627','23.728263','1405594966'];
        $testPreviousRow =  ['1', '37.966660', '23.728308', '1405594957'];

        $distanceInMeters = $fareEstimatorService->calculateDistanceBetweenPoints($testRow, $testPreviousRow);

        $this->assertEquals('5.387608950634', $distanceInMeters);
    }

    public function testVelocityRequirementDoesNotComply(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $fareEstimatorService = $container->get(FareEstimatorService::class);
        $testRow = ['1','37.966627','23.728263','1405594966'];
        $testPreviousRow =  ['1', '37.935597', '23.625688', '1405594967'];

        $velocityRequirement = $fareEstimatorService->velocityRequirement($testRow, $testPreviousRow);

        $this->assertFalse($velocityRequirement);
    }

    public function testVelocityRequirementComply(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $fareEstimatorService = $container->get(FareEstimatorService::class);
        $testRow = ['1','37.966627','23.728263','1405594966'];
        $testPreviousRow =  ['1', '37.966660', '23.728308', '1405594957'];

        $velocityRequirement = $fareEstimatorService->velocityRequirement($testRow, $testPreviousRow);

        $this->assertTrue($velocityRequirement);
    }
}
