<?php

namespace App\Command\Build;

use App\Builder\Melange\PipelineReference;
use App\Service\AutodocsService;
use Minicli\Command\CommandController;

class MelangeController extends CommandController
{
    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        /** @var AutodocsService $builderService */
        $builderService = $this->getApp()->builder;
        /** @var PipelineReference $pipelineBuilder */
        $pipelineBuilder = $builderService->getBuilder('melange-pipelines');

        $this->info("Starting build...");
        $pipelineBuilder->buildRecursive();
        $this->info("Build finished with output saved to $pipelineBuilder->outputPath.");
    }
}
