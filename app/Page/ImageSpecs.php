<?php

namespace App\Page;

use App\Service\AutodocsService;
use App\Service\ImageDiscoveryService;
use Minicli\App;
use Minicli\Stencil;
use Yamldocs\Mark;

class ImageSpecs implements ReferencePage
{
    public string $imageName;
    public ImageDiscoveryService $imageDiscovery;
    public Stencil $stencil;

    public function load(App $app, AutodocsService $autodocs): void
    {
        $this->imageDiscovery = $app->imageDiscovery;
        $this->stencil = new Stencil($app->config->templatesDir);
    }

    /**
     * @throws \Exception
     */
    public function getContent(string $image): string
    {
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

        $this->imageName = basename($image);
        $packages = $variants = [];
        $variantsData = $this->imageDiscovery->getImageMetaData($image);

        foreach ($variantsData as $variant => $config) {
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

        return $this->stencil->applyTemplate('image_specs_page', [
            'title' => ucfirst(basename($image)),
            'description' => "Detailed information about the " . ucfirst(basename($image)) . "Chainguard Image variants",
            'content' => $content,
        ]);
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
        $content .= "\nCheck the [tags history page](/chainguard/chainguard-images/reference/" . $this->imageName . "/tags_history/) for the full list of available tags.";

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
