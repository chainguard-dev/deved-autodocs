<?php

use App\Builder\ImageReferenceBuilder;
use App\Service\AutodocsService;

test('autodocs builder has ImageReferenceBuilder loaded with pages.', function () {
    $app = getApp();
    /** @var AutodocsService $builder */
    $builder = $app->builder;
    /** @var ImageReferenceBuilder $imagesBuilder */
    $imagesBuilder = $builder->getBuilder('images-reference');
    $this->assertInstanceOf(ImageReferenceBuilder::class, $imagesBuilder);
    $this->assertCount(3, $imagesBuilder->referencePages);
});