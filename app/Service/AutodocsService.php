<?php

namespace App\Service;

use App\Builder\ImageReferenceBuilder;
use Minicli\App;
use Yamldocs\BuilderService;

class AutodocsService extends BuilderService
{
    /**
     * @throws \Exception
     */
    public function load(App $app): void
    {
        parent::load($app);

        /** @var ImageReferenceBuilder $imageBuilder */
        $imageBuilder = $this->getBuilder('images-reference');

        foreach ($imageBuilder->builderOptions['pages'] as $page) {
            $imageBuilder->registerPage(new $page);
        }
    }
}