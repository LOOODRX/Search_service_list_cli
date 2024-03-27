<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueryServicesCommand extends Command
{
    protected static $defaultName = 'query_services';

    protected function configure()
    {
        $this->setDescription('Query services by country code')
            ->addArgument('countryCode', InputArgument::REQUIRED, 'Enter country code is required');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $countryCode = strtoupper($input->getArgument('countryCode'));  // Convert the entered country code to uppercase characters
    
        // Get all value return from readCSV() method, servicesData contain services name, counts and country code
        $servicesData = $this->readCSV(); 
    
        $services = $servicesData['services']; // prepare the service name to be filtered

        $serviceCounts = $servicesData['serviceCounts'];
    
        $filteredServices = array_filter($services, function ($service) use ($countryCode) { // filter service name
            return strtoupper($service['country']) === $countryCode; // Uniformly capitalize the country code (read from csv) with the entered country code
        });
    
        if (empty($filteredServices)) {
            $output->writeln("No services found for country code '$countryCode'");
        } else {
            $output->writeln("Services provided by $countryCode:");
            foreach ($filteredServices as $service_name) {  // $filteredServices is an array (object) and needs to be converted to get value
                $serviceName = $service_name['name'];
            }
            $output->writeln("- $serviceName");
            $output->writeln("- Number: $serviceCounts[$serviceName] ");
        }
        return Command::SUCCESS;
    }
    
    private function readCSV()
    {
        $services = []; // store service name and country code
        $serviceCounts = []; // store service counts
    
        if (($handle = fopen('services.csv', 'r')) !== false) {  // Read the csv file from the beginning

            $header = fgetcsv($handle); // Read the first row of csv file
            $nameIndex = array_search('Service', $header); 
            $countryIndex = array_search('Country', $header); 
    
            while (($data = fgetcsv($handle)) !== false) {
                $serviceName = $data[$nameIndex];
                $countryCode = $data[$countryIndex];
    
                // count service name store in serviceCounts
                if (!isset($serviceCounts[$serviceName])) {
                    $serviceCounts[$serviceName] = 1; // If serviceName not exists before, set value as 1
                } else {
                    $serviceCounts[$serviceName]++; // If serviceName already exists, value + 1
                }
    
                $services[] = [
                    'name' => $serviceName,
                    'country' => $countryCode, 
                    // If you want to search by ref or other, can set variable in here
                ];
            }

            fclose($handle); // Close file read
        }
    
        return ['services' => $services, 'serviceCounts' => $serviceCounts];
    }
    
}

$application = new Application();
$application->add(new QueryServicesCommand());
$application->run();
