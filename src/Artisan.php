<?php

namespace Deferdie\Docker\Console;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class Artisan extends Command
{
    // Set the command properties
    protected function configure()
    {
        $this->setName('artisan')
            ->setDescription('Runs php artisan')
            ->addArgument('argument', InputArgument::IS_ARRAY, InputOption::VALUE_OPTIONAL)
            ->addOption('flags', InputOption::VALUE_REQUIRED);
    }

    // Execute the command
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arg = '';

        foreach($input->getArgument('argument') as $item)
        {
            $arg .= $item . ' ';
        }

        $dash = '';

        if($input->getOption('flags') != null)
        {
            $dash = '-';
        }

        $process = new Process('docker-compose run --rm -w /var/www/html app php artisan '. $arg . ' ' . $dash . $input->getOption('flags'));

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