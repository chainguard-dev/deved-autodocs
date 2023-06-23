<?php

namespace App\Page;

use App\Service\ImageDiscoveryService;
use Minicli\App;
use Minicli\Stencil;
use Yamldocs\Mark;

class ImageTags implements ReferencePage
{
    public ImageDiscoveryService $imageDiscovery;
    public Stencil $stencil;

    /**
     * @throws \Exception
     */
    public function load(App $app): void
    {
        $this->imageDiscovery = $app->imageDiscovery;
        $this->stencil = new Stencil($app->config->templatesDir);
    }

    public function code($str): string
    {
        return sprintf("`%s`", $str);
    }

    /**
     * @param string $image
     * @return string
     * @throws \Exception
     */
    public function getContent(string $image): string
    {
        return $this->stencil->applyTemplate('image_tags_page', [
            'title' => ucfirst(basename($image)) . ' Image Tags History',
            'description' => "Image Tags and History for the " . ucfirst(basename($image)) . " Chainguard Image",
            'content' => $this->getTagsTable($image),
        ]);
    }

    public function orderTags(array $tag1, array $tag2): int
    {
        $date1 = new \DateTime($tag1['lastUpdated']);
        $date2 = new \DateTime($tag2['lastUpdated']);

        if ($date1 == $date2) {
            return 0;
        }

        return ($date1 < $date2) ? -1 : 1;
    }

    public function getTagsTable(string $image): string
    {
        try {
            $imageMeta = $this->imageDiscovery->getImageTagsInfo(basename($image));
        } catch (\Exception $e) {
            return "";
        }

        usort($imageMeta, [ImageTags::class, "orderTags"]);
        $imageMeta = array_reverse($imageMeta);
        $rows = [];

        foreach ($imageMeta as $tag) {
            $now = new \DateTime();
            $update = new \DateTime($tag['lastUpdated']);
            $interval = $now->diff($update);

            //suppress tags older than 1 month
            if ($interval->m) {
                 break;
            }

            $rows[] = [
                $this->code($tag['name']),
                $this->getElapsedTime($interval),
                $this->code($tag['digest'])
            ];
        }

        return Mark::table($rows, ['Tag', 'Last Updated', 'Digest']);
    }
  
    public function getElapsedTime(\DateInterval $interval): string
    {
        if ($interval->d) {
            $x = $interval->d > 1 ? 's' : '';
            return "$interval->d day$x ago";
        }

        $x = $interval->h > 1 ? 's' : '';
        return "$interval->h hour$x ago";
    }

    public function getSaveName(string $image): string
    {
        return 'tags_history.md';
    }
}
