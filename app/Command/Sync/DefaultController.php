<?php

namespace App\Command\Sync;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $this->info("Syncs generated docs to other locations.", true);
        $this->info("Example:");
        $this->info("./autodocs sync images");
    }
}
