<?php

namespace App\Page;

interface ReferencePage
{
    public function getContent(string $image): string;

    public function getSaveName(string $image): string;
}