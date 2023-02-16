<?php

namespace App\Command\Notify;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $this->getPrinter()->info("Example:", true);
        $this->getPrinter()->info("./autodocs notify slack message=\"your message\"");
    }
}
