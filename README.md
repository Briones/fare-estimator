# Fare Estimator - BEAT Challenge Test

This system is based in PHP 8.1 and Symfony 6.2 as a Framework

The main goal of the current system is to be a Fare Estimator for actual rides,
in order to compare them with the actual fares and avoid overcharging our valuable customers.


The system needs an input of a CSV File where there should be a list of the rides locations and timestamps in the following way:

```
1,37.966660,23.728308,1405594957
1,37.966627,23.728263,1405594966
1,37.966625,23.728263,1405594974
2,37.946545,23.754918,1405591065
2,37.946545,23.754918,1405591073
2,37.946545,23.754918,1405591084
2,37.946413,23.754767,1405591094
```

Where the values are in the following order: Id,Latitude,Longitude,Timestamp
There is also an example of this file called `paths.csv` in the root path of this repository,
this is also the file that will be used to make the calculations.

## PreRequirements

Have PHP 8.1 and Composer 2 Installed

## Setup

Download the repository and once you are in the folder, execute

```bash
composer install
```

This will automatically install the packages needed.

## Execute the Command

This application is Command based, so you can run it from CLI in the following way:
```bash
    php bin/console app:fare-estimator <filePath> 
```

The filePath argument is optional, in case you don't provide a file, it will automatically use the `paths.csv` included in this repo.

The script first tries to filter that elements that doesn't fit with the rules, removing the registers that have a speed faster than 100km/h
between to consecutive rows, but for this matter it creates a new file called `filteredPaths.csv` in order to not affect the original set of data.

The system relies on mainly two services: `CSVService.php` and `FareEstimatorService.php`, the first one is in charge of handling everything about
the CSV files, creation, opening, closing and manipulation. The second one is the one in charge of handle all the rules about Fares, and it has
also some calculations about the distance between two locations, the time that a ride was idle, and calculate the finish fare amount that should be charged to the customer.


# Tests

For maintenance convenience the system includes a UnitTest Set that test the main functions of each Service. In order to be able to use the test
there is included some test csv files like `pathTest.csv` and it also will be created a `testResult.csv` that will be created and deleted at the end of the test.

To run the Unit Tests you can run the following command inside on the project folder:

```bash
php bin/phpunit
```







