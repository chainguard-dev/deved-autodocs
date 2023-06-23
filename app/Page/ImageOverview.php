<?php

namespace App\Page;

use App\Service\AutodocsService;
use App\Service\ImageDiscoveryService;
use Minicli\App;
use Minicli\Stencil;
use App\ReadmeReader;

class ImageOverview implements ReferencePage
{
    public ImageDiscoveryService $imageDiscovery;
    public Stencil $stencil;

    public static string $DEFAULT_REGISTRY='cgr.dev/chainguard';

    /**
     * @throws \Exception
     */
    public function load(App $app, AutodocsService $autodocs): void
    {
        $this->imageDiscovery = $app->imageDiscovery;
        $this->stencil = new Stencil($app->config->templatesDir);
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
        $readme = ReadmeReader::getContent($image . '/README.md');
        $reference = '[' . self::$DEFAULT_REGISTRY . '/' . basename($image) . ']' . '(https://github.com/chainguard-images/images/tree/main/images/' . basename($image) . ')';

        $content = $reference . "\n" . $readme;

        return $this->stencil->applyTemplate('image_reference_page', [
            'title' => "Image Overview: " . ucfirst(basename($image)),
            'description' => "Overview: " . ucfirst(basename($image)) . " Chainguard Image",
            'content' => $content,
        ]);
    }

    public function getSaveName(string $image): string
    {
        return 'overview.md';
    }
}
