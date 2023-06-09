<?php

namespace App\Command\Build;

use App\Builder\ImageReferenceBuilder;
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
        /** @var AutodocsService $builderService */
        $builderService = $this->getApp()->builder;
        /** @var ImageReferenceBuilder $imagesBuilder */
        $imagesBuilder = $builderService->getBuilder('images-reference');

        $this->out("Using $imagesBuilder->diffSourcePath as Diff Source.\n");

        if ($this->hasParam('image')) {
            $imagesBuilder->buildDocsForImage($imagesBuilder->sourcePath . '/' . $this->getParam('image'));
            return;
        }

        $imagesBuilder->buildImageDocs();
        $imagesBuilder->saveChangelog();
        $this->info("Latest changes saved to $imagesBuilder->lastUpdatePath.");
        $this->info("Changelog saved to $imagesBuilder->changelogPath.");
    }
}
