<?php

namespace App\Command\Build;

use App\Service\AutodocsService;
use Exception;
use Minicli\Command\CommandController;
use Minicli\FileNotFoundException;

class ImagesController extends CommandController
{
    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function handle(): void
    {
        /** @var AutodocsService $autodocs */
        $autodocs = $this->getApp()->autodocs;
        $imagesList = $autodocs->getImagesList();
        $ignoreImages = ['alpine-base', 'k3s-images', 'sdk', 'spire'];

        $imagesBuilder = $autodocs->getBuilder('images-reference');
        $this->out("Using $imagesBuilder->diffSourcePath as Diff Source.\n");

        if ($this->hasParam('image')) {
            $imagesBuilder->buildDocsForImage($this->getParam('image'));
            return;
        }

        //build index
        $imagesBuilder->savePage($imagesBuilder->outputPath . '/_index.md', $imagesBuilder->getIndexPage(
            "Chainguard Images Reference",
            "Chainguard Images Reference Docs",
            "Reference docs for Chainguard Images"
        ));

        foreach ($imagesList as $image) {
            if (in_array($image['repo']['name'], $ignoreImages)) {
                continue;
            }
            $imageName = $image['repo']['name'];
            $this->info("Building docs for the $imageName image...");
            $imagesBuilder->buildDocsForImage($imageName);
        }

        $imagesBuilder->saveChangelog();
        $this->info("Latest changes saved to $imagesBuilder->lastUpdatePath.");
        $this->info("Changelog saved to $imagesBuilder->changelogPath.");
    }
}
