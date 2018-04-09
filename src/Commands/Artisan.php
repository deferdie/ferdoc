<?php

namespace Deferdie\Docker\Console\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;

class Artisan extends Command
{
    // Set the command properties
    protected function configure()
    {
        $this->setName('artisan')
            ->setDescription('Runs php artisan');
    }

    // Execute the command
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $output->writeln('<info>You can run things like, make:controller, make:event</info>');

        $question = new Question('Run : ');

        $answer = $helper->ask($input, $output, $question);

        $output->writeln('<info>Running please wait...</info>');

        $process = new Process('docker-compose run --rm -w /var/www/html app php artisan '.$answer);

        $process->setTimeout(0);

        $process->run(function ($type, $buffer) {
           echo $buffer;
        });

        $output->writeln($process->getOutput());
    }
}