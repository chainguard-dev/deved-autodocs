<?php

namespace App\Page;

use App\Builder\ImageReferenceBuilder;
use App\Service\AutodocsService;
use Minicli\App;
use Minicli\Stencil;

abstract class ImageReferencePage implements ReferencePage
{
    public AutodocsService $autodocs;
    public Stencil $stencil;
    public ImageReferenceBuilder $imagesBuilder;

    /**
     * @throws \Exception
     */
    public function load(App $app, AutodocsService $autodocs): void
    {
        $this->autodocs = $autodocs;
        $this->imagesBuilder = $this->autodocs->getBuilder('images-reference');
        $this->stencil = new Stencil($this->imagesBuilder->templatesDir);
        $this->stencil->fallbackTo([$app->config->templatesDir]);
    }

    public function code($str): string
    {
        return sprintf("`%s`", $str);
    }
}
