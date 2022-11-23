<?php

namespace App\Builders\Melange;

use App\BuilderInterface;
use App\Mark;
use Minicli\Stencil;

class PipelineReference implements BuilderInterface
{
    public $templateDir;

    public function configure(array $options = []): void
    {
        $this->templateDir = $options['tplDir'] ?? __DIR__ . '/../../../templates';
    }

    /**
     * @param string $title
     * @param string $description
     * @param array $nodes
     * @param array $meta
     * @return string
     * @throws \Minicli\FileNotFoundException
     */
    public function getMarkdown(string $title, string $description, array $nodes, array $meta = []): string
    {
        $stencil = new Stencil($this->templateDir);

        return $stencil->applyTemplate('pipeline_reference_page', [
            'title' => $title,
            'description' => $description,
            'content' => $this->getContentMarkdown($title, $description, $nodes, $meta)
        ]);
    }

    public function getContentMarkdown(string $title, string $description, array $nodes, array $meta = []): string
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
                        $default = $default ? "true" : "falss";
                    }
                    $inputDescription .= " Default is set to `$default` .";
                }

                $table[] = [$inputName, $inputDescription];
            }
        }
        if (count($table)) {
            $referenceTable = Mark::table($table, ['Input', 'Description']);
        }

        if (count($needs['packages'])) {
            foreach ($needs['packages'] as $dependency) {
                $dependencies .= "- $dependency\n";
            }
        }

        $content .= "\n" . $this->buildSectionContent($title, $nodes['name'], $referenceTable, $dependencies);
        return $content;
    }

    public function buildSectionContent(string $title, string $description, string $referenceTable, string $dependencies): string
    {
        return sprintf("## %s\n%s\n\n### Dependencies\n%s\n\n### Reference\n%s", $title, $description, $dependencies, $referenceTable);
    }
}