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
    public Stencil $stencil;
    public array $newImages = [];

    public function configure(Config $config, array $builderOptions = []): void
    {
        $this->builderOptions = $builderOptions;

        $this->sourcePath =  envconfig('YAMLDOCS_IMAGES_SOURCE', $this->builderOptions['source']);
        $this->outputPath = envconfig('YAMLDOCS_OUTPUT', $this->builderOptions['output']);
        $this->diffSourcePath = envconfig('YAMLDOCS_DIFF_SOURCE', $this->builderOptions['diffSource'] ?? $this->outputPath);
        $this->changelogPath = envconfig('YAMLDOCS_CHANGELOG', $this->builderOptions['changelog']);
        $this->lastUpdatePath = envconfig('YAMLDOCS_LASTUPDATE', $this->builderOptions['lastUpdate']);
        $templatesDir = envconfig('YAMLDOCS_TEMPLATES', $this->builderOptions['templatesDir'] ?? $config->templatesDir);
        $this->setTemplatesDir($templatesDir, $config);
        $this->stencil = new Stencil($this->templatesDir);
        $this->stencil->fallbackTo([$config->templatesDir, __DIR__ . '/../../templates']);
    }

    public function saveChangelog(): void
    {
        $changelog = $this->getChangelog();
        try {
            if (!is_file($this->lastUpdatePath)) {
                if (touch($this->lastUpdatePath)) {
                    $this->savePage($this->lastUpdatePath, $changelog);
                }
            }

            if (!is_file($this->changelogPath)) {
                if (touch($this->changelogPath)) {
                    $this->appendToFile($this->changelogPath, $changelog);
                }
            }
        } catch (\Exception $exception) {
            return;
        }
    }

    /**
     * @throws FileNotFoundException
     */
    public function buildDocsForImage(string $image, string $pages = "all"): void
    {
        if (!is_dir($this->diffSourcePath . '/' . $image)) {
            $this->newImages[] = $image;
        }

        $savePath = $this->outputPath . '/' . $image;
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }

        $pagesArray = explode(',', $pages);
        $this->savePage($this->outputPath . '/' . $image . '/_index.md', $this->getIndexPage(
            $image,
            "Chainguard Images Reference: $image",
            "Reference docs for the $image Chainguard Image"
        ));

        foreach ($this->referencePages as $referencePage)
        {
            if ($pages === "all" || in_array($referencePage->getName(), $pagesArray)) {
                $this->savePage(
                    $savePath . '/' . $referencePage->getSaveName($image),
                    $referencePage->getContent($image)
                );
            }
        }
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
            'title' => $title,
            'description' => $description,
            'content' => $content
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
