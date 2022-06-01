<?php

namespace App\Command;

use App\Service\CSVService;
use App\Service\FareEstimatorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'app:fare-estimator')]
class FareEstimatorCommand extends Command
{
    protected CSVService $manageCSVService;
    protected FareEstimatorService $fareEstimatorService;
    protected $parameterBag;

    public function __construct(CSVService $csvService, FareEstimatorService $fareEstimatorService, ParameterBagInterface $parameterBag)
    {
        $this->manageCSVService = $csvService;
        $this->fareEstimatorService = $fareEstimatorService;
        $this->parameterBag = $parameterBag;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDefinition([
            new InputArgument('filePath', InputArgument::OPTIONAL, 'The file path of the CSV that is gonna be processed', 'paths.csv'),
            ])
            ->setHelp('This command estimate the fares that different rides should have in base of a CSV Input file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timezone = $this->parameterBag->get('timezone');
        date_default_timezone_set($timezone);
        $this->manageCSVService->filterData($input->getArgument('filePath'));
        $this->manageCSVService->createResultFile();
        $finalResult = $this->fareEstimatorService->calculateFare();
        $this->manageCSVService->writeFareInFile($finalResult);
        $this->manageCSVService->closeResultFile();

        $io = new SymfonyStyle($input, $output);
        $io->table(['id_ride', 'fare_estimate'], $finalResult);
        echo 'The file result.csv has been generated successfully';

        return Command::SUCCESS;
    }
}
