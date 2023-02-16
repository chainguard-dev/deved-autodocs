<?php

namespace App;

class ReadmeReader
{
    public static function getContent(string $path): string
    {
        $content = file_get_contents($path);

        $content = str_ireplace("# $content", "", $content);

        $content = preg_replace('/<!--(.*)-->(.*)<!--(.*)-->/Uis', '', $content);

        return $content;
    }
}
