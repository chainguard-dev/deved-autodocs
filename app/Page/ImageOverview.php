<?php

namespace App\Page;

use App\Service\AutodocsService;
use Minicli\App;
use Minicli\FileNotFoundException;
use App\ReadmeReader;

class ImageOverview extends ImageReferencePage
{
    public ImageTags $imageTags;
    public string $sourcePath;

    public static string $DEFAULT_REGISTRY='cgr.dev/chainguard';

    public function load(App $app, AutodocsService $autodocs): void
    {
        parent::load($app, $autodocs);

        $this->sourcePath = $this->imagesBuilder->sourcePath;
        $this->imageTags = new ImageTags();
        $this->imageTags->load($app, $autodocs);
    }

    /**
     * @param string $image
     * @return string
     * @throws \Exception
     */
    public function getContent(string $image): string
    {
        try {
            $readme = ReadmeReader::getContent($this->sourcePath . '/' . $image . '/README.md');
        } catch (FileNotFoundException $exception) {
            $readme = $this->stencil->applyTemplate('default_overview', [
                'title' => $image
            ]);
        }

        $reference = '[' . self::$DEFAULT_REGISTRY . '/' . $image . ']' . '(https://github.com/chainguard-images/images/tree/main/images/' . $image . ')';
        $table = $this->imageTags->getTagsTable($image, ['latest', 'latest-dev']);

        $content = $reference . "\n\n" . $table. "\n" . $readme;

        return $this->stencil->applyTemplate('image_reference_page', [
            'title' => "Image Overview: $image",
            'description' => "Overview: $image Chainguard Image",
            'content' => $content,
        ]);
    }

    public function getSaveName(string $image): string
    {
        return 'overview.md';
    }
}
