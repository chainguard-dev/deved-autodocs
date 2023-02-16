<?php

namespace App\Command\Build;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $this->getPrinter()->info("Example:", true);
        $this->getPrinter()->info("./autodocs build images");
    }
}
