<?php

namespace Deferdie\Docker\Console\Containers;

interface ContainerInterface
{
    /**
     * Return an array of the container required.
     *
     * @param  array  $array
     * @return mixed
     */
    public function build();
}