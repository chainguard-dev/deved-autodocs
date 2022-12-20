<?php

namespace App\Command\Update;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $this->getPrinter()->info("Copies generated docs to other locations.", true);
        $this->getPrinter()->info("Example:");
        $this->getPrinter()->info("./autodocs copy images");
    }

}