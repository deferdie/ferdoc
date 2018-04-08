<?php

namespace Deferdie\Docker\Console;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class ComposerInstall extends Command
{
    // Set the command properties
    protected function configure()
    {
        $this->setName('composer')
            ->addArgument('install')
            ->setDescription('Runs composer install');            
    }

    // Execute the command
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process('docker-compose run --rm -w /var/www/html app composer install');
        
        $output->writeln('Running composer install, please wait');

        $process->setTimeout(0);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        $output->writeln($process->getOutput());
    }
}