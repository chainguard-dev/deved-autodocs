<?php

declare(strict_types=1);

use App\Service\AutodocsService;
use App\Service\CacheService;
use App\Service\ImageDiscoveryService;

return [
    'services' => [
        'cache' => CacheService::class,
        'imageDiscovery' => ImageDiscoveryService::class,
        'builder' => AutodocsService::class
    ],
];
