<?php

namespace App\Command\Build;

use App\ImageOverview;
use App\ImageSpecs;
use Minicli\Command\CommandController;
use Minicli\FileNotFoundException;
use Minicli\Stencil;

class ImagesController extends CommandController
{
    protected Stencil $stencil;
    public array $newImages = [];
    public string $diffSource;
    public string $changelog;
    public string $lastUpdate;

    /**
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $imagesConfig = $this->getApp()->config->imagesReference;
        $source = $imagesConfig['source'];
        $output = $imagesConfig['output'];
        $tplDir = $imagesConfig['templates'];

        $this->diffSource = $imagesConfig['diffSource'];
        $this->changelog = $imagesConfig['changelog'];
        $this->lastUpdate = $imagesConfig['lastUpdate'];
        $this->stencil = new Stencil($tplDir);

        if (!is_dir($output)) {
            mkdir($output, 0777, true);
        }

        $this->getPrinter()->out("Using $this->diffSource as Diff Source.\n");

        //Build reference index
        $this->saveFile($output . '/_index.md', $this->stencil->applyTemplate('_index_page', [
            'title' => "Chainguard Images Reference",
            'description' => "Chainguard Images Reference Docs",
            'content' => "Reference docs for Chainguard Images"
        ]));

        //Build docs for a single image
        if ($this->hasParam('image')) {
            $this->buildImageDocs($source . '/' . $this->getParam('image'), $output);
            return;
        }

        //Build docs for all images
        foreach (glob($source . '/*') as $image) {
            $this->buildImageDocs($image, $output);
        }

        $changes = $this->getChangelog();
        $this->saveFile($this->lastUpdate, $changes);
        $this->getPrinter()->info("Latest changes saved to $this->lastUpdate.");


        $this->appendToFile($this->changelog, $changes);
        $this->getPrinter()->info("Changelog saved to $this->changelog.");
    }

    /**
     * @return string
     */
    public function getChangelog(): string
    {
        $changelogContent = "## " . date('Y-m-d' . "\n\n");
        $changelogContent .= "Updated image reference docs.\n\n";
        if (count($this->newImages)) {
            $changelogContent .= "New images added:\n\n- ";
            $changelogContent .= implode("\n- ", $this->newImages);
        } else {
            $changelogContent .= "No new images added.";
        }

        return $changelogContent;
    }

    /**
     * @throws FileNotFoundException
     */
    public function buildImageDocs(string $image, string $outputDir): void
    {
        $overview = new ImageOverview($image);
        $outputDir = $outputDir . '/' . basename($image);
        $title = basename($image);

        if (!is_dir($this->diffSource . '/' . basename($image))) {
            $this->newImages[] = $title;
        }

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

        //Build specs page
        $specs = new ImageSpecs($image);
        $this->saveFile($outputDir . '/image_specs.md', $this->stencil->applyTemplate('image_specs_page', [
            'title' => $title,
            'description' => "Detailed specs for $title Chainguard Image Variants",
            'content' => $specs->getContent()
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

    /**
     * @param string $outputFile
     * @param string $content
     * @return void
     */
    public function appendToFile(string $outputFile, string $content): void
    {
        $fileContent = "";
        if (is_file($outputFile)) {
            $fileContent = file_get_contents($outputFile);
        }

        $this->saveFile($outputFile, $content . "\n\n" . $fileContent);
    }
}
