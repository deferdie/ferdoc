<?php

namespace Deferdie\Docker\Console\Containers;

use  Deferdie\Docker\Console\Containers\ContainerInterface;

class NginxContainer implements ContainerInterface
{
    protected $appName = null;

    protected $hostName = null;

    public function __construct($appName, $hostName)
    {
        $this->appName = $appName;
        $this->hostName = $hostName;
    }

    public function build()
    {
        return [
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
                'VIRTUAL_HOST='.$this->hostName,
                'VIRTUAL_PORT=80'
            ]
        ];
    }
}