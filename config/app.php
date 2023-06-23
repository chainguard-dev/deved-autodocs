<?php
declare(strict_types=1);

return [
    "app_name" => "AutoDocs: Chainguard Images\nType ./autodocs help for commands",
    'app_path' => [
        __DIR__.'/../app/Command',
        '@minicli/command-help'
    ],
    'theme' => '',
    'debug' => true,

    "imagesReference"  => [
        # Where to look for the image yaml files
        "source" => __DIR__ . "/workdir/yaml/images",

        # Where to output generated markdown
        "output" => __DIR__ . "/workdir/markdown/images/reference",

        # Where to look for current markdown files to generate a changelog (new images added)
        "diffSource" => __DIR__ . "/workdir/markdown/images/reference",

        # Autodocs templates
        "templates" => __DIR__ . "/workdir/templates",

        # Changelog Location
        "changelog" => "changelog.md",

        # This file keeps only the latest entry of the changelog
        "lastUpdate" => "last-update.md"
    ],
];
