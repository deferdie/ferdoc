<?php

namespace Deferdie\Docker\Console\ReverseProxy;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class ReverseProxyCommand extends Command
{
    // Set the command properties
    protected function configure()
    {
        $this->setName('proxy')
            ->addArgument('action')
            ->setDescription('Reverse proxy service for containers');            
    }

    // Execute the command
    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        // Start a proxy server based on nginx
        if($input->getArgument('action') == 'start')
        {
            $output->writeln('Starting reverse proxy');

            $this->startProxy($output);
        }
    }

    private function startProxy($output)
    { 
        $process = new Process('docker-compose -f '.__DIR__.'\docker-compose.yml up --force-recreate');

        $process->setTimeout(0);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        $output->writeln($process->getOutput());
    }
}