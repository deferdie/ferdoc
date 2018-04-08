<?php

namespace Deferdie\Docker\Console;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class StopContainers extends Command
{
    // Set the command properties
    protected function configure()
    {
        $this->setName('down')
            ->setDescription('Stop all the contianers');            
    }

    // Execute the command
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process('docker-compose down');

        $process->setTimeout(0);

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {
                echo 'OUT > '.$buffer;
            }
        });

        $output->writeln($process->getOutput());

        $output->writeln('Containers stopped');
    }
}