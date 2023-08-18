<?php

namespace App\Page;

use Minicli\FileNotFoundException;

class ImageProvenance extends ImageReferencePage
{
    /**
     * @throws FileNotFoundException
     */
    public function getContent(string $image): string
    {
        return $this->stencil->applyTemplate('image_provenance_page', [
            'title' => $image,
            'description' => "Provenance information for $image Chainguard Image"
        ]);
    }

    public function getName(): string
    {
        return 'provenance';
    }

    public function getSaveName(string $image): string
    {
        return 'provenance_info.md';
    }
}
