<?php

namespace App\Page;

use Yamldocs\Mark;
use Symfony\Component\Yaml\Yaml;

class ImageSpecs implements ReferencePage
{
    public string $imageName;
    public string $imagePath;
    public array $imageConfig = [];
    public array $variants = [];
    public array $globalOptions = [];

    public function buildDataMatrix(string $image): void
    {
        $this->imagePath = $image;
        $this->imageName = basename($this->imagePath);
        $this->imageConfig = $this->loadYaml($this->imagePath . '/image.yaml');
        //check for global options
        if (is_file($this->imagePath . '/../../globals.yaml')) {
            $globals = $this->loadYaml($this->imagePath . '/../../globals.yaml');
            $this->globalOptions = $globals['options'] ?? [];
        }
        $variants = [];

        foreach ($this->imageConfig['versions'] as $variant) {
            $config = $variant['apko']['config'];
            $variantName = basename($config, '.apko.yaml');
            $variants[$variantName] = $this->loadYaml($this->imagePath . "/$config");

            if (isset($variant['apko']['subvariants'])) {
                //image has subvariants
                foreach ($variant['apko']['subvariants'] as $subvariant) {
                    //unfurl subvariant options
                    $subvariantName = $variantName . $subvariant['suffix'];
                    $variants[$subvariantName] = $variants[$variantName];

                    $extraOptions = isset($this->imageConfig['options'])
                        ? array_merge($this->globalOptions, $this->imageConfig['options'])
                        : $this->globalOptions;

                    foreach ($subvariant['options'] as $option) {
                        if (!isset($extraOptions[$option])) {
                            continue;
                        }

                        if (isset($extraOptions[$option]['contents']['packages']['add'])) {
                            $variants[$subvariantName]['contents']['packages'] = array_merge(
                                $variants[$subvariantName]['contents']['packages'],
                                $extraOptions[$option]['contents']['packages']['add']
                            );
                        }

                        if (isset($extraOptions[$option]['entrypoint'])) {
                            $variants[$subvariantName]['entrypoint'] = $extraOptions[$option]['entrypoint'];
                        }
                    }
                }
            }

        }

        $this->variants = $variants;
    }

    public function loadYaml(string $path): array
    {
        return Yaml::parseFile($path);
    }

    public function getContent(string $image): string
    {
        $this->buildDataMatrix($image);

        $content = "";
        $headers = [''];
        $columns[] = [
            'Default User',
            'Entrypoint',
            'CMD',
            'Workdir',
            'Has apk?',
            'Has a shell?',
        ];

        $packages = [];
        $variants = [];

        foreach ($this->variants as $variant => $config) {
            $headers[] = $variants[] = $variant;
            $columns[] = [
                $this->getDefaultUser($config),
                $this->getEntrypoint($config),
                isset($config['cmd']) ? '`' . $config['cmd'] . '`' : "not specified",
                isset($config['work-dir']) ? '`' . $config['work-dir'] . '`' : "not specified",
                $this->hasApk($config),
                $this->hasShell($config),
            ];

            //build packages array
            foreach ($config['contents']['packages'] as $deps)
            {
                $packages[$deps][] = $variant;
            }
        }

        $content .= $this->getVariantsSection($variants, $columns, $headers);
        $content .= $this->getDependenciesSection($packages, $headers);

        return $content;
    }

    public function getPackagesList(array $yamlConfig): string
    {
        return implode('<br/>', $yamlConfig['contents']['packages']);
    }

    public function getEntrypoint(array $yamlConfig): string
    {
        $entrypoint = "not specified";
        if (isset($yamlConfig['entrypoint']['command'])) {
            $entrypoint = '`' . $yamlConfig['entrypoint']['command'] . '`';
        }

        if (isset($yamlConfig['entrypoint']['type']) && $yamlConfig['entrypoint']['type'] === "service-bundle") {
            $entrypoint = "Service Bundle";
        }

        return $entrypoint;
    }

    public function hasApk(array $yamlConfig): string
    {
        return (
            in_array('apk-tools', $yamlConfig['contents']['packages']) ||
            in_array('wolfi-base', $yamlConfig['contents']['packages'])) ?
            "yes" : "no";
    }

    public function hasShell(array $yamlConfig): string
    {
        return (
            in_array('busybox', $yamlConfig['contents']['packages']) ||
            in_array('bash', $yamlConfig['contents']['packages']) ||
            in_array('wolfi-base', $yamlConfig['contents']['packages'])) ?
            "yes" : "no";
    }

    public function getDefaultUser(array $yamlConfig): string
    {
        if (!isset($yamlConfig['accounts']['users']) ||
            !isset($yamlConfig['accounts']['run-as']) ||
            $yamlConfig['accounts']['run-as'] == 0
        ) {
            return '`root`';
        }

        $uid = $yamlConfig['accounts']['run-as'];
        if (is_string($uid)) {
            return "`$uid`";
        }

        $runAs = "";

        //locate user
        foreach ($yamlConfig['accounts']['users'] as $user) {
            if ($user['uid'] == $uid) {
                $runAs = $user['username'];
                break;
            }
        }

        return "`$runAs`";
    }

    public function getVariantsSection(array $variants, array $columns, array $headers): string
    {
        $content = "## Variants Compared\n";

        //check variants
        $number = (sizeof($variants) === 1) ? "one public variant" : sizeof($variants) . " public variants";

        $content .= sprintf("The **%s** Chainguard Image currently has %s: %s",
            $this->imageName,
            $number,
            "\n\n- `" . implode("`\n- `", $variants) . "`\n\n"
        );

        $content .= "The table has detailed information about each of these variants.\n\n";

        $tableRows = [];
        for ($i = 0; $i < sizeof($columns[0]); $i++) {
            $row = [];
            for ($j = 0; $j < sizeof($columns); $j++) {
                $row[] = $columns[$j][$i];
            }
            $tableRows[] = $row;
        }

        $content .= Mark::table($tableRows, $headers);

        return $content;
    }

    public function getDependenciesSection(array $packages, array $headers): string
    {
        $content = "\n## Image Dependencies\n";
        $content .= "The table shows package distribution across all variants.\n\n";

        $tableRows = [];
        $row = [];
        foreach ($packages as $name => $package) {
            $row[] = '`' . $name . '`';
            for ($i = 1; $i < sizeof($headers); $i++) {
                $row[] = in_array($headers[$i], $package) ? "X" : " ";
            }
            $tableRows[] = $row;
            $row = [];
        }
        $content .= Mark::table($tableRows, $headers);

        return $content;
    }

    public function getSaveName(string $image): string
    {
        return 'image_specs.md';
    }
}
