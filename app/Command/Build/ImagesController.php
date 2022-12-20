<?php

namespace App\Command\Build;

use Minicli\Command\CommandController;
use Minicli\Stencil;

class ImagesController extends CommandController
{
    /**
     * @throws \Minicli\FileNotFoundException
     */
    public function handle(): void
    {
        $source = getenv('YAMLDOCS_SOURCE') ?: __DIR__ . '/../../../workdir/yaml/images';
        $output = getenv('YAMLDOCS_OUTPUT') ?: __DIR__ . '/../../../workdir/markdown/images';

        $tplDir = __DIR__ . '/../../../templates';
        $stencil = new Stencil($tplDir);

        foreach (glob($source . '/*') as $image) {
            $readme = file_get_contents($image . '/README.md');
            $outputFile = $output . '/' . basename($image) . '/index.md';
            if (!is_dir(dirname($outputFile))) {
                mkdir(dirname($outputFile), 0777, true);
            }

            $markdown = $stencil->applyTemplate('image_reference_page', [
                'title' => basename($image),
                'description' => 'Reference docs for the ' . basename($image) . ' Chainguard Image',
                'content' => $readme
            ]);

            file_put_contents($outputFile, $markdown);

            $this->getPrinter()->info("Saved image page: $outputFile");
        }
    }
}