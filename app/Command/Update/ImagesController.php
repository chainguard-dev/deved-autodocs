<?php

namespace App\Command\Update;

use Minicli\Command\CommandController;
use Minicli\Stencil;

class ImagesController extends CommandController
{
    public function handle(): void
    {
        //remove changelog / last update files before copying

        $markdown = getenv('YAMLDOCS_OUTPUT') ?: __DIR__ . '/../../../workdir/markdown/images/reference';
        unlink($markdown . '/changelog.md');
        unlink($markdown . '/last-update.md');
        $destination = getenv('YAMLDOCS_COPY') ?: __DIR__ . '/../../../../edu/content/chainguard/chainguard-images';
        $this->info("Copying files to $destination...");
        shell_exec("cp -R $markdown $destination");
        $this->success("Finished.");
    }
}
