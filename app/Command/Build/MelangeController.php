<?php

namespace App\Command\Build;

use Minicli\Command\CommandController;

class MelangeController extends CommandController
{
    public function handle(): void
    {
        $source = getenv('YAMLDOCS_SOURCE') ?: __DIR__ . '/../../../workdir/yaml/pipelines';
        $output = getenv('YAMLDOCS_OUTPUT') ?: __DIR__ . '/../../../workdir/markdown/melange-pipelines';
        $yamldocs = __DIR__ . '/../../../vendor/erikaheidi/yamldocs/bin/yamldocs';

        foreach (glob($source . '/*') as $path) {
            if (is_dir($path) AND basename($path) !== "_meta") {
                $outputDir = $output . '/' . basename($path);
                if (!is_dir($outputDir)) {
                    mkdir($outputDir);
                }

                echo shell_exec("$yamldocs build docs source=$path output=$outputDir builder=melange-pipeline");
            }
        }
        echo shell_exec("$yamldocs build docs source=$source output=$output");
    }
}