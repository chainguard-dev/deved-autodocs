<?php

namespace App\Command\Cache;

use App\Service\CacheService;
use Minicli\Command\CommandController;

class ClearController extends CommandController
{
    public function handle(): void
    {
        /** @var CacheService $cache */
        $cache = $this->getApp()->cache;
        $cache->clear($this->getParam('name'));

        $this->success("Cache cleared.");
    }
}
