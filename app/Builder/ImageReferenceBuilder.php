<?php

namespace App\Builder;

use App\Page\ReferencePage;
use Minicli\Config;
use Minicli\FileNotFoundException;
use Minicli\Stencil;
use Yamldocs\Builder\DefaultBuilder;

class ImageReferenceBuilder extends DefaultBuilder
{
    public array $referencePages;
    public string $sourcePath;
    public string $outputPath;
    public string $changelogPath;
    public string $lastUpdatePath;
    public string $diffSourcePath;
    protected Stencil $stencil;
    public array $newImages = [];

    public function configure(Config $config, array $builderOptions = []): void
    {
        parent::configure($config, $builderOptions);

        $this->sourcePath =  $this->builderOptions['source'];
        $this->outputPath = $this->builderOptions['output'];
        $this->diffSourcePath = $this->builderOptions['diffSource'] ?? $this->outputPath;
        $this->changelogPath = $this->builderOptions['changelog'];
        $this->lastUpdatePath = $this->builderOptions['lastUpdate'];
        $this->stencil = new Stencil($this->templatesDir);

        $this->loadEnvOverwrites();
    }

    public function loadEnvOverwrites()
    {
        if (getenv('YAMLDOCS_IMAGES_SOURCE')) {
            $this->sourcePath = getenv('YAMLDOCS_IMAGES_SOURCE');
        }

        if (getenv('YAMLDOCS_DIFF_SOURCE')) {
            $this->diffSourcePath = getenv('YAMLDOCS_DIFF_SOURCE');
        }

        if (getenv('YAMLDOCS_OUTPUT')) {
            $this->outputPath = getenv('YAMLDOCS_OUTPUT');
        }
    }

    /**
     * @throws FileNotFoundException
     */
    public function buildImageDocs(): void
    {
        $this->savePage($this->outputPath . '/_index.md', $this->getIndexPage(
            "Chainguard Images Reference",
            "Chainguard Images Reference Docs",
            "Reference docs for Chainguard Images"
        ));

        foreach (glob($this->sourcePath . '/*') as $image) {
            $this->buildDocsForImage($image);
        }
    }

    public function saveChangelog(): void
    {
        $changelog = $this->getChangelog();
        $this->savePage($this->lastUpdatePath, $changelog);
        $this->appendToFile($this->changelogPath, $changelog);
    }

    /**
     * @throws FileNotFoundException
     */
    public function buildDocsForImage(string $image): void
    {
        if (!is_dir($this->diffSourcePath . '/' . basename($image))) {
            $this->newImages[] = basename($image);
        }

        $savePath = $this->outputPath . '/' . basename($image);
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }

        $imageName = basename($image);
        $this->savePage($this->outputPath . '/_index.md', $this->getIndexPage(
            $imageName,
            "Chainguard Images Reference: $imageName",
            "Reference docs for the $imageName Chainguard Image"
        ));

        foreach ($this->referencePages as $referencePage)
        {
            $this->savePage(
                $savePath . '/' . $referencePage->getSaveName($image),
                $referencePage->getContent($image)
            );
        }

        $this->savePage($savePath . '/provenance_info.md', $this->getProvenancePage($image));
    }

    public function registerPage(ReferencePage $page): void
    {
        $this->referencePages[] = $page;
    }

    /**
     * @throws FileNotFoundException
     */
    public function getIndexPage(string $title, string $description, string $content): string
    {
        return $this->stencil->applyTemplate('_index_page', [
            'title' => "Chainguard Images Reference",
            'description' => "Chainguard Images Reference Docs",
            'content' => "Reference docs for Chainguard Images"
        ]);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getProvenancePage(string $image): string
    {
        return $this->stencil->applyTemplate('image_provenance_page', [
            'title' => $image,
            'description' => "Provenance information for $image Chainguard Images"
        ]);
    }

    public function savePage(string $filePath, string $content): void
    {
        file_put_contents($filePath, $content);
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

        $this->savePage($outputFile, $content . "\n\n" . $fileContent);
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
}
