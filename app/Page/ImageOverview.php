<?php

namespace App\Page;

use App\Builder\ImageReferenceBuilder;
use App\Service\AutodocsService;
use Minicli\App;
use Minicli\FileNotFoundException;
use Minicli\Stencil;
use App\ReadmeReader;

class ImageOverview implements ReferencePage
{
    public Stencil $stencil;
    public ImageTags $imageTags;
    public string $sourcePath;

    public static string $DEFAULT_REGISTRY='cgr.dev/chainguard';

    /**
     * @throws \Exception
     */
    public function load(App $app, AutodocsService $autodocs): void
    {
        /** @var ImageReferenceBuilder $imagesBuilder */
        $imagesBuilder = $autodocs->getBuilder('images-reference');
        $this->stencil = new Stencil($imagesBuilder->templatesDir);
        $this->sourcePath = $imagesBuilder->sourcePath;
        $this->imageTags = new ImageTags();
        $this->imageTags->load($app, $autodocs);
    }

    public function code($str): string
    {
        return sprintf("`%s`", $str);
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
            'title' => "Image Overview: " . ucfirst($image),
            'description' => "Overview: " . ucfirst($image) . " Chainguard Image",
            'content' => $content,
        ]);
    }

    public function getSaveName(string $image): string
    {
        return 'overview.md';
    }
}
