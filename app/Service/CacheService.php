<?php

namespace App\Service;

use Minicli\App;
use Minicli\Minicache\FileCache;
use Minicli\ServiceInterface;

class CacheService implements ServiceInterface
{
    public array $cacheCollection;
    public string $cacheDir;

    public function load(App $app): void
    {
        $this->cacheDir = $app->config->cacheDir ?? __DIR__ . '/../../workdir/cache';
    }

    public function createCache(string $identifier): void
    {
        if (!is_dir($this->cacheDir . '/' . $identifier)) {
            mkdir($this->cacheDir . '/' . $identifier, 0755, true);
        }
        
        $this->cacheCollection[$identifier] = new FileCache($this->cacheDir . '/' . $identifier);
    }

    /**
     * @throws \Exception
     */
    public function getCache(string $identifier, bool $create = false): FileCache
    {
        if (!isset($this->cacheCollection[$identifier])) {
            if (!$create) {
                throw new \Exception("Cache storage $identifier is not registered.");
            }

            $this->createCache($identifier);
        }
        
        return $this->cacheCollection[$identifier];
    }

    public function clear(string $identifier = null)
    {
        if ($identifier) {
            foreach (glob($this->cacheDir . "/$identifier/*.json") as $filename) {
                unlink($filename);
            }
            return;
        }

        foreach (glob($this->cacheDir . "/*", GLOB_ONLYDIR) as $subDir)
        {
            $this->clear(basename($subDir));
        }
    }

}
