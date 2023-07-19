<?php

namespace App\Command\Sync;

use App\Service\AutodocsService;
use Minicli\Command\CommandController;
use Minicli\Stencil;

class ImagesController extends CommandController
{
    public function handle(): void
    {
        $source = getenv('YAMLDOCS_DIFF_SOURCE') ?: __DIR__ . '/../../../../edu/content/chainguard/chainguard-images/reference';

        /** @var AutodocsService $autodocs */
        $autodocs = $this->getApp()->autodocs;
        $imagesMetadata = $autodocs->getImagesList();
        $retiredImages = ['alpine-base', 'k3s-images', 'sdk', 'spire', 'musl-dynamic'];

        $imagesList = [];
        foreach ($imagesMetadata as $images) {
            $imagesList[] = $images['repo']['name'];
        }

        foreach (glob($source . '/*', GLOB_ONLYDIR) as $image) {
            $imageName = basename($image);
            $this->info("Checking for $imageName image...");
            if (!in_array($imageName, $imagesList)) {
                $retiredImages[] = $imageName;
            }
        }
        $this->info("Retired Images:");
        print_r($retiredImages);
        $this->success("Finished.");
    }
}
