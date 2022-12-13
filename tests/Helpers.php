<?php

use Minicli\App;
use Minicli\Command\CommandCall;

function getCommandsPath(): array
{
    return [
        __DIR__ . '/../app/Command',
        '@minicli/command-help'
    ];
}

function getApp(): App
{
    $config = [
        'app_path' => getCommandsPath()
    ];

    return new App($config);
}

function getProdApp(): App
{
    $config = [
        'app_path' => getCommandsPath(),
        'debug' => false
    ];

    return new App($config);
}

function getCommandCall(array $parameters = null): CommandCall
{
    return new CommandCall(array_merge(['minicli'], $parameters));
}
