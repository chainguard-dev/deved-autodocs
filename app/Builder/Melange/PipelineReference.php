<?php

namespace App\Builder\Melange;

use Yamldocs\Builder\DefaultBuilder;
use Yamldocs\Document;
use Yamldocs\Mark;
use Minicli\FileNotFoundException;
use Minicli\Stencil;
use Minicli\Config;

class PipelineReference extends DefaultBuilder
{
    public string $sourcePath;
    public string $outputPath;
    public string $pipelineOutput;
    public Stencil $stencil;

    public function configure(Config $config, array $builderOptions = []): void
    {
        parent::configure($config, $builderOptions);
        $this->sourcePath =  $this->builderOptions['source'];
        $this->outputPath = $this->builderOptions['output'];
        $this->stencil = new Stencil($this->templatesDir);
        $this->loadEnvOverwrites();
    }

    public function loadEnvOverwrites()
    {
        if (getenv('YAMLDOCS_PIPELINES_SOURCE')) {
            $this->sourcePath = getenv('YAMLDOCS_IMAGES_SOURCE');
        }

        if (getenv('YAMLDOCS_PIPELINES_OUTPUT')) {
            $this->outputPath = getenv('YAMLDOCS_PIPELINES_OUTPUT');
        }
    }

    /**
     * @throws FileNotFoundException
     */
    public function buildRecursive()
    {
        $this->savePage($this->outputPath . '/_index.md', $this->stencil->applyTemplate('_index_page', [
            'title' => "melange pipelines reference",
            'description' => "melange pipelines reference documentation",
            'content' => "Reference docs for melange pipelines"
        ]));

        foreach (glob($this->sourcePath . '/*', GLOB_ONLYDIR) as $pipeline) {
            $pipelineOutput = $this->outputPath . '/' . basename($pipeline);
            if (!is_dir($pipelineOutput)) {
                mkdir($pipelineOutput);
            }
            $this->buildPipelineDocs($pipeline, $pipelineOutput);
        }
        $this->buildPipelineDocs($this->sourcePath, $this->outputPath);
    }

    /**
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function buildPipelineDocs(string $pipelinesDir, string $outputPath): void
    {
        foreach (glob($pipelinesDir . '/*.yaml') as $pipeline) {
            $content = $this->getMarkdown(new Document($pipeline));
            $this->savePage( $outputPath. '/' . str_replace("yaml", "md", basename($pipeline)), $content);
        }
    }

    /**
     * @param Document $document
     * @return string
     * @throws FileNotFoundException
     */
    public function getMarkdown(Document $document): string
    {
        return $this->stencil->applyTemplate('pipeline_reference_page', [
            'title' => $document->getTitle(),
            'description' => 'Reference docs for the ' . $document->getTitle() . ' melange pipeline',
            'content' => $this->buildContent($document)
        ]);
    }

    public function buildContent(Document $document): string
    {
        $nodes = $document->yaml;
        $needs = $nodes['needs'] ?? null;
        $inputs = $nodes['inputs'] ?? null;
        $content = "";
        $referenceTable = "";
        $dependencies = "";
        $table = [];

        if ($inputs) {
            foreach ($inputs as $key => $item) {
                $inputName = "`$key`";
                $inputDescription = str_replace("\n", "", $item['description']);

                if (isset($item['required']) && $item['required'] == 'true') {
                    $inputName .= '*';
                }

                if (isset($item['default'])) {
                    $default = $item['default'];
                    if (is_bool($default)) {
                        $default = $default ? "true" : "false";
                    }
                    $inputDescription .= " Default is set to `$default`";
                }

                $table[] = [$inputName, $inputDescription];
            }
        }
        if (count($table)) {
            $referenceTable = Mark::table($table, ['Input', 'Description']);
        }

        if (isset($needs['packages']) && count($needs['packages'])) {
            foreach ($needs['packages'] as $dependency) {
                $dependencies .= "- $dependency\n";
            }
        }

        if ($dependencies === "") {
            $dependencies = "None";
        }

        if ($referenceTable === "") {
            $referenceTable = "This pipeline doesn't expect any input arguments.";
        }

        $example = "There are no examples available.";

        if ($document->getMeta('example')) {
            $example = "```yaml\n" . $document->getMeta('example') . "\n```\n";
        }

        $content .= "\n" . sprintf(
            "%s\n\n### Dependencies\n%s\n\n### Reference\n%s\n\n### Example\n%s",
            $document->getMeta('name'),
            $dependencies,
            $referenceTable,
            $example
        );

        return $content;
    }

    public function savePage(string $filePath, string $content): void
    {
        file_put_contents($filePath, $content);
    }
}
