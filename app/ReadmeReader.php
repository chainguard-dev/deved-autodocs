<?php

namespace App;

use Minicli\Curly\Client;
use Minicli\FileNotFoundException;

class ReadmeReader
{
    /**
     * @throws FileNotFoundException
     */
    public static function getContent(string $path): string
    {
        if (!is_file($path)){
            throw new FileNotFoundException("README file not found.");
        }

        $content = file_get_contents($path);

        $content = str_ireplace("# $content", "", $content);

        return preg_replace('/<!--(.*)-->(.*)<!--(.*)-->/Uis', '', $content);
    }
}
