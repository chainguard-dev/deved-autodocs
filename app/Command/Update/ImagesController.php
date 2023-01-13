<?php

namespace App\Command\Update;

use Minicli\Command\CommandController;
use Minicli\Stencil;

class ImagesController extends CommandController
{
    public function handle(): void
    {
        $markdown = getenv('YAMLDOCS_OUTPUT') ?: __DIR__ . '/../../../workdir/markdown/images/reference';
        $destination = getenv('YAMLDOCS_COPY') ?: __DIR__ . '/../../../../edu/content/chainguard/chainguard-images';
        $this->getPrinter()->info("Copying files to $destination...");
        shell_exec("cp -R $markdown $destination");
        $this->getPrinter()->success("Finished.");
    }
}