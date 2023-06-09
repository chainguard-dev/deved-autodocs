<?php

namespace App\Command\Build;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $this->info("Example:", true);
        $this->info("./autodocs build images");
    }
}
