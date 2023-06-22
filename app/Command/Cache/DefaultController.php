<?php

namespace App\Command\Cache;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $this->info("Example:", true);
        $this->info("./autodocs cache clear");
    }
}
