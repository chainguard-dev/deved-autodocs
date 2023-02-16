<?php

namespace App\Command\Build;

use App\ImageOverview;
use Minicli\Command\CommandController;
use Minicli\Stencil;

class ImagesController extends CommandController
{
    protected Stencil $stencil;

    /**
     * @throws \Minicli\FileNotFoundException
     */
    public function handle(): void
    {
        $source = getenv('YAMLDOCS_SOURCE') ?: __DIR__ . '/../../../workdir/yaml/images';
        $output = getenv('YAMLDOCS_OUTPUT') ?: __DIR__ . '/../../../workdir/markdown/images/reference';

        $tplDir = __DIR__ . '/../../../templates';
        $this->stencil = new Stencil($tplDir);

        if (!is_dir($output)) {
            mkdir($output, 0777, true);
        }

        //Build reference index
        $this->saveFile($output . '/_index.md', $this->stencil->applyTemplate('_index_page', [
            'title' => "Chainguard Images Reference",
            'description' => "Chainguard Images Reference Docs",
            'content' => "Reference docs for Chainguard Images"
        ]));

        if ($this->hasParam('image')) {
            $this->buildImageDocs($source . '/' . $this->getParam('image'), $output);
            return;
        }

        foreach (glob($source . '/*') as $image) {
            $this->buildImageDocs($image, $output);
        }
    }

    public function buildImageDocs(string $image, string $outputDir): void
    {
        $overview = new ImageOverview($image);
        $outputDir = $outputDir . '/' . basename($image);
        $title = basename($image);

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        //Build image index
        $this->saveFile($outputDir . '/_index.md', $this->stencil->applyTemplate('_index_page', [
            'title' => $title,
            'description' => "Chainguard Images Reference: $title",
            'content' => "Reference docs for the $title Chainguard Image"
        ]));

        //Build overview page
        $this->saveFile($outputDir . '/overview.md', $this->stencil->applyTemplate('image_reference_page', [
            'title' => "Image Overview: $title",
            'description' => "Overview: $title Chainguard Images",
            'content' => $overview->getContent()
        ]));

        //Build provenance page
        $this->saveFile($outputDir . '/provenance_info.md', $this->stencil->applyTemplate('image_provenance_page', [
            'title' => $title,
            'description' => "Provenance information for $title Chainguard Images"
        ]));

        $this->getPrinter()->info("Saved image pages: $outputDir");
    }

    /**
     * @param string $outputFile
     * @param string $content
     * @return void
     */
    public function saveFile(string $outputFile, string $content): void
    {
        file_put_contents($outputFile, $content);
    }
}
