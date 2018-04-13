<?php

namespace Deferdie\Docker\Console\Commands;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use SebastiaanLuca\StubGenerator\StubGenerator;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

class DockerInit extends Command
{
    // The project work directory
    private $workFolder = null;

    // Docker directory in workspace
    private $dockerDir = null;
    
    // Template directory
    private $templateDirectory = null;

    // Project Application name
    private $appName = null;

    // Set things up
    public function __construct()
    {
        parent::__construct();
        
        $this->workfolder = getcwd();

        $this->templateDirectory = __DIR__.$this->directorySeperator().'tempaltes'.$this->directorySeperator();
        
        // Set the enviroment
        $this->LoadEnv();
    }

    protected function loadEnv()
    {
        try{
            if(file_exists($this->workfolder.$this->directorySeperator().'.env'))
            {
                $dotenv = new \Dotenv\Dotenv($this->workfolder);

                $dotenv->load();
                
                if(getenv('APP_NAME') != null)
                {
                    $this->appName = strtolower(getenv('APP_NAME'));
                }
            }else
            {
                $this->appName = strtolower(basename($this->workfolder));
            }
            
        }catch(Exception $e)
        {
            //die($e);
        }
    }

    // Set the command properties
    protected function configure()
    {
        $this->setName('docker init')
            ->setDescription('Create the scaffolding for Docker containers')
            ->addArgument('init');
            
    }

    // Execute the command
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if($input->getArgument('init'))
        {
            // Create a docker directory if it does not exist
            if(!in_array('docker', scandir($this->workfolder)))
            {
                $output->writeln('This will place a docker directory within your project');
                
                $output->writeln($this->workfolder . $this->directorySeperator() . 'docker');
                
                $this->makeDir($this->workfolder, 'docker');

                $output->writeln('<info>Directory created successfully!</info>');    
            }

            $output->writeln('<comment>This will override all the current docker directory contents</comment>');

            $helper = $this->getHelper('question');

            $question = new ChoiceQuestion(
                'Would you like to proceeed? (defaults to yes)',
                array('yes', 'no'),
                0
            );

            $question->setErrorMessage('option %s is invalid.');

            $answer = $helper->ask($input, $output, $question);

            $this->dockerDir = $this->workfolder .$this->directorySeperator(). 'docker';

            if($answer == 'yes')
            {
                // Create the folder structure
                $this->makeDir($this->dockerDir, 'app');
                
                $this->makeDir($this->dockerDir, 'node');

                $this->makeDir($this->dockerDir, 'nginx');

                $this->makeDir($this->dockerDir, 'php');
                
                $this->copyBuildFile();
                
                // App
                $this->setAppContainer();

                // Nginx
                $this->setNginxContainer();
                
                // PHP
                $this->setPhpContainer();
                
                // Node
                $this->setNodeContainer();
                
                // Generate docker-compose.yml
                $this->generateDockerCompose($input, $output);

                $output->writeln('<comment>Run ferdoc build to create your application containers</comment>');

            }else{
                $output->writeln('<warning>Not generating files!</warning>');
            }
        }
    }

    private function setAppContainer()
    {        
        copy($this->templateDirectory.'app'.$this->directorySeperator().'Dockerfile', $this->dockerDir.$this->directorySeperator().'app'.$this->directorySeperator().'Dockerfile');
    }
    
    private function setNginxContainer()
    {        
       copy($this->templateDirectory.'nginx'.$this->directorySeperator().'Dockerfile', $this->dockerDir.$this->directorySeperator().'nginx'.$this->directorySeperator().'Dockerfile');

       if(file_exists($this->dockerDir.$this->directorySeperator().'nginx'.$this->directorySeperator().'default'))
       {
            unlink($this->dockerDir.$this->directorySeperator().'nginx'.$this->directorySeperator().'default');
       }

       $stub = new StubGenerator(
            $this->templateDirectory.'nginx'.$this->directorySeperator().'stubs'.$this->directorySeperator().'default.stub',
            $this->dockerDir.$this->directorySeperator().'nginx'.$this->directorySeperator().'default'
        );

        $stub->render([
            ':PHP_FPM_CONTAINER:' => $this->appName.'_php:9000',
        ]);
    }
    
    private function setPhpContainer()
    {
        copy($this->templateDirectory.'php'.$this->directorySeperator().'php-fpm.conf', $this->dockerDir.$this->directorySeperator().'php'.$this->directorySeperator().'php-fpm.conf');

        copy($this->templateDirectory.'php'.$this->directorySeperator().'www.conf', $this->dockerDir.$this->directorySeperator().'php'.$this->directorySeperator().'www.conf');

        copy($this->templateDirectory.'php'.$this->directorySeperator().'Dockerfile', $this->dockerDir.$this->directorySeperator().'php'.$this->directorySeperator().'Dockerfile');
    }

    private function setNodeContainer()
    {
        copy($this->templateDirectory.'node'.$this->directorySeperator().'Dockerfile', $this->dockerDir.$this->directorySeperator().'node'.$this->directorySeperator().'Dockerfile');
    }

    // Set the build file
    private function copyBuildFile()
    {
        $buildFileLocation = $this->dockerDir.$this->directorySeperator().'build';

        if(file_exists($buildFileLocation))
        {
            unlink($buildFileLocation);
        }

        $stub = new StubGenerator(
            $this->templateDirectory.$this->directorySeperator().'build.stub',
            $buildFileLocation
        );

        $stub->render([
            ':APP_NAME:' => $this->appName,
        ]);
    }

    private function generateDockerCompose($input, $output)
    {
        $build = [
            'version' => '2',
            'services' => [],
            'networks' => [
                'fdnet' => [
                    'driver' => 'bridge'
                ],
                'reverseproxy_deferdie_reverse_proxy' => [
                    'external' => [
                        'name' => 'reverseproxy_deferdie_reverse_proxy'
                    ]
                ]
            ],
            'volumes' => [
                'mysqldata' => [
                    'driver' => 'local'
                ],
                'redisdata' => [
                    'driver' => 'local'
                ]
            ]
        ];

        $nodeContainer = [
            'build' => [
                'context' => './docker/node',
                'dockerfile' => 'Dockerfile'
            ],
            'image' => $this->appName.'/node',
            'volumes' => ['.:/var/www/html'],
            'networks' => [
                'fdnet'
            ],
            'container_name' => $this->appName .'_node'
        ];

        $hostQuestion = new Question(
            'Please enter the host address e.g. foo.test : '
        );

        $hostQuestion->setValidator(function ($answer) {
            if (!is_string($answer))
            {
                throw new \RuntimeException(
                    'Please set a hostname'
                );
            }

            return $answer;
        });

        $helper = $this->getHelper('question');

        $hostQuestion->setMaxAttempts(10);

        $hostQuestion = $helper->ask($input, $output, $hostQuestion);

        $hostName = $hostQuestion;
        
        $nginxContainer = [
            'build' => [
                'context' => './docker/nginx',
                'dockerfile' => 'Dockerfile'
            ],
            'image' => $this->appName.'/nginx',
            'volumes' => ['.:/var/www/html'],
            'ports' => ['80:80'],
            'networks' => [
                'fdnet',
                'reverseproxy_deferdie_reverse_proxy'
            ],
            'container_name' => $this->appName .'_nginx',
            'environment' => [
                'VIRTUAL_HOST='.$hostName,
                'VIRTUAL_PORT=80'
            ]
        ];
        
        $phpContainer = [
            'build' => [
                'context' => './docker/php',
                'dockerfile' => 'Dockerfile'
            ],
            'image' => $this->appName.'/php',
            'volumes' => ['.:/var/www/html'],
            'networks' => [
                'fdnet'
            ],
            'container_name' => $this->appName .'_php'
        ];
        
        $appContainer = [
            'build' => [
                'context' => './docker/app',
                'dockerfile' => 'Dockerfile'
            ],
            'image' => $this->appName.'/app',
            'volumes' => ['.:/var/www/html'],
            'networks' => ['fdnet'],
            'container_name' => $this->appName .'_app'
        ];

        // Nginx reverse proxy port
        $portQuestion = new Question(
            'Please set external port for nginx - This is just for the reverse proxy, you can still access your site via foo.test : ',
            '8888'
        );

        $portQuestion->setValidator(function ($answer) {
            if (!is_string($answer))
            {
                throw new \RuntimeException(
                    'Please set a port'
                );
            }

            return $answer;
        });

        $helper = $this->getHelper('question');

        $portQuestion->setMaxAttempts(10);

        $portQuestion = $helper->ask($input, $output, $portQuestion);;

        $nginxContainer['ports'] = ["$portQuestion:80"];

        // Set default containers
        $build['services']['app'] = $appContainer;
        $build['services']['php'] = $phpContainer;
        $build['services']['nginx'] = $nginxContainer;
        $build['services']['node'] = $nodeContainer;
        
        
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Would you like mysql?',
            array('yes', 'no'),
            0
        );

        $question->setErrorMessage('option %s is invalid.');

        $answer = $helper->ask($input, $output, $question);

        if($answer == 'yes')
        {
            $build['services']['mysql'] = $this->mysqlConfig();

                $portQuestion = new Question(
                'Please set external port for mysql - to access this database in Workbench, Heidi etc, please use this port : ',
                '3307'
            );

            $portQuestion->setValidator(function ($answer) {
                if (!is_string($answer))
                {
                    throw new \RuntimeException(
                        'Please set a port'
                    );
                }

                return $answer;
            });

            $portQuestion = $helper->ask($input, $output, $portQuestion);;

            $build['services']['mysql']['ports'] = ["$portQuestion:3306"];
        }

        $question = new ChoiceQuestion(
            'Would you like redis?',
            array('yes', 'no'),
            0
        );

        $question->setErrorMessage('option %s is invalid.');

        $answer = $helper->ask($input, $output, $question);
        
        if($answer == 'yes')
        {
            $build['services']['redis'] = $this->redisConfig();
        }

        // Set the docker-compose file
        $yamlFile = fopen($this->workfolder.$this->directorySeperator().'docker-compose.yml', "w") or die("Unable to open file!");

        fwrite($yamlFile, Yaml::dump($build, 20, 2));
        
        fclose($yamlFile);
    }

    // Get Mysql config
    private function mysqlConfig()
    {
        return [
            'image' => 'mysql:5.7',
            'ports' => [
                "3306:3306"
            ],
            'environment' => [
                'MYSQL_ROOT_PASSWORD' => 'secret',
                'MYSQL_DATABASE' => 'homestead',
                'MYSQL_USER' => 'homestead',
                'MYSQL_PASSWORD' => 'secret',
            ],
            'volumes' => [
                'mysqldata:/var/lib/mysql'
            ],
            'networks' => [
                'fdnet'
            ],
            'container_name' => $this->appName .'_mysql'
        ];
    }

    // Get Redis config
    private function redisConfig()
    {
        return [
            'image' => 'redis:alpine',
            'volumes' => [
                'redisdata:/data'
            ],
            'networks' => [
                'fdnet'
            ],
            'container_name' => $this->appName .'_redis'
        ];
    }
   
    // Directory directory
    private function directorySeperator()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return '\\';
        } else {
            return '/';
        }
    }

    private function makeDir($target, $folder)
    {
        if(!is_dir($target .$this->directorySeperator(). $folder))
        {
            mkdir($target .$this->directorySeperator(). $folder);
        }
    }
}