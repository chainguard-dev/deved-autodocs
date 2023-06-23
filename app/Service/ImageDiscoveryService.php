<?php

namespace App\Service;

use App\Chainctl;
use Minicli\App;
use Minicli\ServiceInterface;
use Symfony\Component\Yaml\Yaml;

class ImageDiscoveryService implements ServiceInterface
{
    public CacheService $cache;
    public array $imageRepos;

    public static string $CACHE_IMAGES_METADATA = "images";
    public static string $CACHE_CHAINCTL = "chainctl";
    public static string $CHAINCTL_DEFAULT_GROUP="720909c9f5279097d847ad02a2f24ba8f59de36a";
    public static string $CHAINCTL_CACHE_IMAGES = "CHAINCTL_CACHE_IMAGES";
    public static string $CHAINCTL_CACHE_TAGS = "CHAINCTL_CACHE_TAGS";

    public function boot(): void
    {
        //
    }

    public function load(App $app): void
    {
        $this->cache = $app->cache;
        $this->cache->createCache(self::$CACHE_IMAGES_METADATA);
        $this->cache->createCache(self::$CACHE_CHAINCTL);
    }

    /**
     * @throws \Exception
     */
    public function getRepoInfo(string $image)
    {
        if (empty($this->imageRepos)) {
            $this->loadImagesRepos();
        }

        return $this->imageRepos[$image];
    }

    /**
     * @throws \Exception
     */
    public function getImageTagsInfo(string $image): array
    {
        $imagesTags = $this->getImagesTags();

        foreach ($imagesTags as $imageInfo) {
            if ($imageInfo['repo']['name'] === $image) {
                return $imageInfo['tags'];
            }
        }

        return [];
    }

    /**
     * Obtains metadata based on image YAML definitions
     * @throws \Exception
     */
    public function getImageMetaData(string $image, bool $useCache = true): array
    {
        $cachedImageData = $this->cache->getCache(self::$CACHE_IMAGES_METADATA);

        if ($useCache) {
            $cached_content = $cachedImageData->getCachedUnlessExpired($image);

            if ($cached_content !== null) {
                return json_decode($cached_content, true);
            }
        }

        $imageData = $this->fetchImageMetadata($image);
        $cachedImageData->save(json_encode($imageData), $image);

        return $imageData;
    }

    /**
     * @throws \Exception
     */
    public function loadImagesRepos(): void
    {
        $imageData = $this->getImagesRepos();

        foreach ($imageData['items'] as $image) {
            $name = $image['name'];
            $this->imageRepos[$name] = [
                'id' => $image['id'],
                'name' => $name,
                'catalogTier' => !empty($image['catalogTier']) ? (string) $image['catalogTier'] : 'STANDARD'
            ];
        }
    }

    /**
     * @throws \Exception
     */
    public function getImagesRepos(bool $useCache = true)
    {
        $cachedChainctlData = $this->cache->getCache(self::$CACHE_CHAINCTL);

        if ($useCache) {
            $cached_content = $cachedChainctlData->getCachedUnlessExpired(self::$CHAINCTL_CACHE_IMAGES);

            if ($cached_content !== null) {
                return json_decode($cached_content, true);
            }
        }

        $imageData = $this->fetchImagesRepos();
        $cachedChainctlData->save($imageData, self::$CHAINCTL_CACHE_IMAGES);

        return json_decode($imageData, true);
    }


    /**
     * @throws \Exception
     */
    public function getImagesTags(bool $useCache = true)
    {
        $cachedTagsData = $this->cache->getCache(self::$CACHE_CHAINCTL);

        if ($useCache) {
            $cached_content = $cachedTagsData->getCachedUnlessExpired(self::$CHAINCTL_CACHE_TAGS);

            if ($cached_content !== null) {
                return json_decode($cached_content, true);
            }
        }

        $imageData = $this->fetchImagesTags();
        $cachedTagsData->save($imageData, self::$CHAINCTL_CACHE_TAGS);

        return json_decode($imageData, true);
    }

    /**
     * @throws \Exception
     */
    private function fetchImagesRepos(): string
    {
        return Chainctl::getImagesList();
    }

    /**
     * Fires Chainctl to obtain information about all images and tags currently available.
     * @throws \Exception
     */
    private function fetchImagesTags(): string
    {
        return Chainctl::getImagesTagsList();
    }

    /**
     * Fetch metadata information from yaml files
     * @param string $image
     * @return array
     */
    private function fetchImageMetadata(string $image): array
    {
        $imagePath = $image;
        $globalOptions = [];
        $imageConfig = Yaml::parseFile($imagePath . '/image.yaml');
        $variants = [];

        //check for global options
        if (is_file($imagePath . '/../../globals.yaml')) {
            $globals = Yaml::parseFile($imagePath . '/../../globals.yaml');
            $globalOptions = $globals['options'] ?? [];
        }

        foreach ($imageConfig['versions'] as $variant) {
            $config = $variant['apko']['config'];
            $variantName = basename($config, '.apko.yaml');
            $variants[$variantName] = Yaml::parseFile($imagePath . "/$config");

            if (isset($variant['apko']['subvariants'])) {
                //image has subvariants
                foreach ($variant['apko']['subvariants'] as $subvariant) {
                    //unfurl subvariant options
                    $subvariantName = $variantName . $subvariant['suffix'];
                    $variants[$subvariantName] = $variants[$variantName];

                    $extraOptions = isset($imageConfig['options'])
                        ? array_merge($globalOptions, $imageConfig['options'])
                        : $globalOptions;

                    foreach ($subvariant['options'] as $option) {
                        if (!isset($extraOptions[$option])) {
                            continue;
                        }

                        if (isset($extraOptions[$option]['contents']['packages']['add'])) {
                            $variants[$subvariantName]['contents']['packages'] = array_merge(
                                $variants[$subvariantName]['contents']['packages'],
                                $extraOptions[$option]['contents']['packages']['add']
                            );
                        }

                        if (isset($extraOptions[$option]['entrypoint'])) {
                            $variants[$subvariantName]['entrypoint'] = $extraOptions[$option]['entrypoint'];
                        }
                    }
                }
            }

        }

        return $variants;
    }
}