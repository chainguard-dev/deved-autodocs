<?php

use App\Service\AutodocsService;
use App\Service\CacheService;
use App\Service\ImageDiscoveryService;

test('autodocs builder service is registered and loaded.', function () {
    $app = getApp();

    $this->assertInstanceOf(AutodocsService::class, $app->autodocs);
});