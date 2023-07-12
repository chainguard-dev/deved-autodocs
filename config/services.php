<?php

declare(strict_types=1);

use App\Service\AutodocsService;
use App\Service\CacheService;
use App\Service\ImageDiscoveryService;

return [
    'services' => [
        'autodocs' => AutodocsService::class
    ],
];
