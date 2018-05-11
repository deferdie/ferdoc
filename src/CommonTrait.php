<?php

namespace Deferdie\Docker\Console;

use Symfony\Component\Process\Process;

trait CommonTrait
{
    // Gets the correct directory seperator for the OS
    public function directorySeperator()
    {
        return DIRECTORY_SEPARATOR;
    }

    public function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}