<?php

namespace App\Command\Notify;

use Minicli\Command\CommandController;

class DefaultController extends CommandController
{
    public function handle(): void
    {
        $this->info("Example:", true);
        $this->info("./autodocs notify slack message=\"your message\"");
    }
}
