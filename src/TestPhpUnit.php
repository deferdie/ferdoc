<?php

namespace Deferdie\Docker\Console;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class TestPhpUnit extends Command
{
    // Set the command properties
    protected function configure()
    {
        $this->setName('test')
            ->setDescription('Runs PHP unit');            
    }

    // Execute the command
    protected function execute(InputInterface $input, OutputInterface $output)
    {
         $process = new Process('docker-compose run --rm -w /var/www/html app ./vendor/bin/phpunit');

        $output->writeln('Running Tests (Good luck!)');

        $process->setTimeout(0);

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {
                echo 'OUT > '.$buffer;
            }
        });

        $output->writeln($process->getOutput());
    }
}