<?php

namespace App\Page;

use Minicli\App;

interface ReferencePage
{
    public function load(App $app): void;

    public function getContent(string $image): string;

    public function getSaveName(string $image): string;
}