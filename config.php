<?php
// Config source for images
$source = getenv('YAMLDOCS_IMAGES_SOURCE')
    ? getenv('YAMLDOCS_IMAGES_SOURCE')
    : __DIR__ . "/workdir/yaml/images";

$diffSource = getenv('YAMLDOCS_DIFF_SOURCE')
    ? getenv('YAMLDOCS_DIFF_SOURCE')
    : __DIR__ . "/workdir/markdown/images/reference";

// Where all files will be output
$output = getenv('YAMLDOCS_OUTPUT')
    ? getenv('YAMLDOCS_OUTPUT')
    : __DIR__ . "/workdir/markdown/images/reference";

return [
    "imagesReference"  => [
        # Where to look for the image yaml files
        "source" => $source,

        # Where to output generated markdown
        "output" => $output,

        # Where to look for current markdown files to generate a changelog (new images added)
        "diffSource" => $diffSource,

        # Autodocs templates
        "templates" => getenv('YAMLDOCS_TEMPLATES')
            ? getenv('YAMLDOCS_TEMPLATES')
            : __DIR__ . "/workdir/templates",

        # Changelog Location
        "changelog" => getenv('YAMLDOCS_CHANGELOG')
            ? getenv('YAMLDOCS_CHANGELOG')
            : $output .  "/changelog.md",

        # This file keeps only the latest entry of the changelog
        "lastUpdate" => getenv('YAMLDOCS_LAST_UPDATE')
            ? getenv('YAMLDOCS_LAST_UPDATE')
            : $output . "/last-update.md"
        ],
];
