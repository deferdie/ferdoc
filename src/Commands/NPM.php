<?php

namespace Deferdie\Docker\Console\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class NPM extends Command
{
    // Set the command properties
    protected function configure()
    {
        $this->setName('npm')
            ->addArgument('option', InputArgument::REQUIRED, 'watch, install')
            ->setDescription('Runs NPM install');            
    }

    // Execute the command
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($input->getArgument('option') == 'install')
        {
            $this->runNPMInstall($input, $output);
            return;
        }
        
        if($input->getArgument('option') == 'watch')
        {
            $this->runNPMWatch($input, $output);
            return;
        }
    }

    private function runNPMInstall($input, $output)
    {
        $process = new Process('docker-compose run --rm -w /var/www/html node npm install');

        $output->writeln('Running NPM install, please wait');

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
    
    private function runNPMWatch($input, $output)
    {
        $process = new Process('docker-compose run --rm -w /var/www/html node node_modules/.bin/webpack --watch --watch-poll --config=node_modules/laravel-mix/setup/webpack.config.js');

        $output->writeln('Running watcher...');

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