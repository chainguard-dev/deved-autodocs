<?php

namespace App\Command\Cache;

use App\Service\CacheService;
use App\Service\ImageDiscoveryService;
use Minicli\Command\CommandController;
use Minicli\Exception\CommandNotFoundException;

class RefreshController extends CommandController
{
    /**
     * @throws CommandNotFoundException
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->getApp()->runCommand(['autodocs', 'cache', 'clear']);

        /** @var ImageDiscoveryService $imagesDiscovery */
        $imagesDiscovery = $this->getApp()->imageDiscovery;
        $this->info("Warming up image tags cache...");
        if ($this->hasParam('import')) {
            $this->info("Importing cache from existing JSON...");
            $file = $this->getParam('import');
            if (!is_file($file)) {
                throw new \Exception("Import file $file not found.");
            }

            /** @var ImageDiscoveryService $images */
            $images = $this->getApp()->imageDiscovery;
            $cache = $images->cache->getCache(ImageDiscoveryService::$CACHE_CHAINCTL);
            $cache->save(file_get_contents($file), ImageDiscoveryService::$CHAINCTL_CACHE_TAGS);

            $this->success("Cache updated from input json.");
            return;
        }
        $imagesDiscovery->getImagesTags(false);

        $this->success("Cache updated.");
    }
}
