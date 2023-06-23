<?php

use App\Service\AutodocsService;
use App\Service\CacheService;
use App\Service\ImageDiscoveryService;

test('autodocs builder service is registered and loaded.', function () {
    $app = getApp();

    $this->assertInstanceOf(AutodocsService::class, $app->builder);
});

test('cache service is registered and loaded.', function () {
    $app = getApp();

    $this->assertInstanceOf(CacheService::class, $app->cache);
});

test('imageDiscovery service is registered and loaded.', function () {
    $app = getApp();

    $this->assertInstanceOf(ImageDiscoveryService::class, $app->imageDiscovery);
});