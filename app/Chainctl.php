<?php

namespace App;

class Chainctl
{
    public static function getImagesList(): string
    {
        $command = sprintf("chainctl img repo ls -o%s", "json");
        $response = shell_exec($command);

        if ($response === null || str_contains($response, 'error')) {
            throw new \Exception("Error: you may need to authenticate with chainctl.");
        }

        return $response;
    }

    public static function getImagesTagsList(): string
    {
        $command = sprintf("chainctl img ls -o%s", "json");
        $response = shell_exec($command);
        if ($response === null || str_contains($response, 'error')) {
            throw new \Exception("Error: you may need to authenticate with chainctil.");
        }

        return $response;
    }
}
