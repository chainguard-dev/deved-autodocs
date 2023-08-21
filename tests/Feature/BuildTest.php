<?php

use App\Builder\ImageReferenceBuilder;
use App\Service\AutodocsService;

test('autodocs builder has ImageReferenceBuilder loaded with pages.', function () {
    $app = getApp();
    /** @var AutodocsService $autodocs */
    $autodocs = $app->autodocs;
    /** @var ImageReferenceBuilder $imagesBuilder */
    $imagesBuilder = $autodocs->getBuilder('images-reference');
    $this->assertInstanceOf(ImageReferenceBuilder::class, $imagesBuilder);
    $this->assertCount(4, $imagesBuilder->referencePages);
});