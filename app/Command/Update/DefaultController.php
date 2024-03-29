<?php

namespace App\Command\Update;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $this->info("Copies generated docs to other locations.", true);
        $this->info("Example:");
        $this->info("./autodocs copy images");
    }
}
