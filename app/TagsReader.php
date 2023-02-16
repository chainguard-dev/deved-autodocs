<?php

namespace App;

use Minicli\Curly\Client;

class TagsReader
{
    public static string $API = "https://storage.googleapis.com/chainguard-images-build-outputs/summary";

    /**
     * @throws \Exception
     */
    public static function getTags(string $image): array
    {
        $client = new Client();
        $endpoint = self::$API . '/' . $image . '.json';
        $response = $client->get($endpoint);

        if ($response['code'] != 200) {
            throw new \Exception("API Error code " . $response['code']);
        }

        return json_decode($response['body'], true);
    }
}
