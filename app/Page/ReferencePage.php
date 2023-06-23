<?php

namespace App\Page;

use App\Service\AutodocsService;
use Minicli\App;

interface ReferencePage
{
    public function load(App $app, AutodocsService $autodocs): void;

    public function getContent(string $image): string;

    public function getSaveName(string $image): string;
}