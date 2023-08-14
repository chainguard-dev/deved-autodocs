<?php

namespace App;

use Minicli\FileNotFoundException;

class DataFeed
{
    public string $identifier;

    public string $data;

    public array $json = [];

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function loadFile(string $file): void
    {
        if (!is_file($file)) {
            throw new FileNotFoundException("Data feed file $file not found.");
        }

        $this->data = file_get_contents($file);
        if ($this->data) {
            $this->json = json_decode($this->data, true);
        }
    }

    public function load(string $data): void
    {
        $this->data = $data;
        $this->json = json_decode($this->data, true);
    }

    public function save(string $filePath): void
    {
        file_put_contents($filePath, $this->data);
    }
}
