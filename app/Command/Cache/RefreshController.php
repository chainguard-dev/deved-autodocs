<?php

namespace App\Command\Cache;

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
        $imagesDiscovery->getImagesTags(false);
        $this->info("Warming up image repos cache...");
        $imagesDiscovery->getImagesRepos(false);

        $this->success("Cache updated.");
    }
}
