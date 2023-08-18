<?php

namespace App\Service;

use App\Builder\ImageReferenceBuilder;
use App\DataFeed;
use Exception;
use Minicli\App;
use Minicli\FileNotFoundException;
use Yamldocs\BuilderService;

class AutodocsService extends BuilderService
{
    public string $cacheDir;
    public array $dataFeeds;

    public static string $CACHE_IMAGES = 'images-tags';

    /**
     * @throws Exception
     */
    public function load(App $app): void
    {
        parent::load($app);

        $this->cacheDir = envconfig('YAMLDOCS_CACHE', $app->config->cacheDir);

        /** @var ImageReferenceBuilder $imageBuilder */
        $imageBuilder = $this->getBuilder('images-reference');

        foreach ($imageBuilder->builderOptions['pages'] as $pageClass) {
            $page = new $pageClass;
            $page->load($app, $this);
            $imageBuilder->registerPage($page);
        }

        $this->loadDataFeeds([self::$CACHE_IMAGES]);
    }

    /**
     * @throws Exception
     */
    public function getImageVariants(string $imageName): array
    {
        $variants = [];
        foreach (glob ($this->cacheDir . "/$imageName*.json") as $imageCache) {
            $tagName = str_replace("$imageName-", '', basename($imageCache));
            $tagName = str_replace(".json", '', $tagName);

            try {
                $imageVariant = $this->getImageSpecs($imageName, $tagName);
            } catch (Exception $e) {
                echo $e->getMessage();
                continue;
            }

            if (count($imageVariant)) {
                $variants[$tagName] = $imageVariant;
            }
        }

        return $variants;
    }

    /**
     * @throws Exception
     */
    public function getImageSpecs(string $imageName, string $tagName): array
    {
        $imageSpecs = $this->getDataFeed($imageName . '-' . $tagName);

        return $imageSpecs->json;
    }

    public function getImagesList(): array
    {
        if (!key_exists(self::$CACHE_IMAGES, $this->dataFeeds)) {
             throw new Exception("Could not find any images in cached data.");
        }

        return $this->dataFeeds[self::$CACHE_IMAGES]->json;
    }

    /**
     * @throws Exception
     */
    public function getImageTags(string $imageName): array
    {
        $imagesList = $this->getImagesList();

        foreach ($imagesList as $image) {
            if ($image['repo']['name'] === $imageName) {
                return $image['tags'];
            }
        }

        return [];
    }

    /**
     * @throws Exception
     */
    public function loadDataFeeds(array $keys): void
    {
        foreach ($keys as $key)
        {
            $this->dataFeeds[$key] = $this->getDataFeed($key);
        }
    }

    /**
     * @throws Exception
     */
    public function getDataFeed(string $feedName): DataFeed
    {
        $feed = new DataFeed($feedName);

        try {
            $feed->loadFile($this->cacheDir . "/" . $feedName . '.json');
        } catch (FileNotFoundException $e) {
            throw new Exception("File not found: " . $this->cacheDir . "/" . $feedName . '.json');
        }

        return $feed;
    }

    public function boot(): void
    {
        //
    }
}
