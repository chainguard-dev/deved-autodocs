<?php

namespace App\Command\Update;

use Minicli\Command\CommandController;
use Minicli\Stencil;

class PipelinesController extends CommandController
{
    public function handle(): void
    {
        $markdown = getenv('YAMLDOCS_OUTPUT') ?: __DIR__ . '/../../../workdir/markdown/melange-pipelines';
        $destination = getenv('YAMLDOCS_COPY') ?: __DIR__ . '/../../../../edu/content/open-source/melange/melange-pipelines';
        $this->getPrinter()->info("Copying files to $destination...");
        shell_exec("cp -R $markdown $destination");
        $this->getPrinter()->success("Finished.");
    }
}