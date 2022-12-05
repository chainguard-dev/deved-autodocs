<?php

namespace App\Builders\Melange;

use App\BuilderInterface;
use App\Document;
use App\Mark;
use Minicli\FileNotFoundException;
use Minicli\Stencil;

class PipelineReference implements BuilderInterface
{
    public string $templateDir;
    public Document $document;

    public function configure(array $options = []): void
    {
        $this->templateDir = $options['tplDir'] ?? __DIR__ . '/../../../templates';
    }

    /**
     * @param Document $document
     * @return string
     * @throws FileNotFoundException
     */
    public function getMarkdown(Document $document): string
    {
        $this->document = $document;
        $stencil = new Stencil($this->templateDir);

        return $stencil->applyTemplate('pipeline_reference_page', [
            'title' => $this->document->getTitle(),
            'description' => 'Reference docs for the ' . $this->document->getTitle() . ' melange pipeline',
            'content' => $this->getContentMarkdown($this->document->getTitle(), $this->document->yaml)
        ]);
    }

    public function getContentMarkdown(string $title, array $nodes): string
    {
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

        $content .= "\n" . $this->buildSectionContent($title, $this->document->getMeta('name') ?? $nodes['name'], $referenceTable, $dependencies);
        return $content;
    }

    public function buildSectionContent(string $title, string $description, string $referenceTable, string $dependencies): string
    {
        if ($dependencies === "") {
            $dependencies = "None";
        }

        if ($referenceTable === "") {
            $referenceTable = "This pipeline doesn't expect any input arguments.";
        }

        $example = "There are no examples available.";

        if ($this->document->getMeta('example')) {
            $example = "```yaml\n" . $this->document->getMeta('example') . "\n```\n";
        }

        return sprintf("%s\n\n### Dependencies\n%s\n\n### Reference\n%s\n\n### Example\n%s",
            $description,
            $dependencies,
            $referenceTable,
            $example
        );
    }
}