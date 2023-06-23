<?php

namespace App\Service;

use App\Builder\ImageReferenceBuilder;
use Minicli\App;
use Yamldocs\BuilderService;
use Yamldocs\YamldocsConfig;

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

        foreach ($imageBuilder->builderOptions['pages'] as $pageClass) {
            $page = new $pageClass;
            $page->load($app, $this);
            $imageBuilder->registerPage($page);
        }
    }

    public function boot(): void
    {
        //
    }
}
