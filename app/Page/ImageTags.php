<?php

namespace App\Page;

use Minicli\Stencil;
use Yamldocs\Mark;

class ImageTags extends ImageReferencePage
{
    /**
     * @param string $image
     * @return string
     * @throws \Exception
     */
    public function getContent(string $image): string
    {
        return $this->stencil->applyTemplate('image_tags_page', [
            'title' => ucfirst($image) . ' Image Tags History',
            'description' => "Image Tags and History for the " . ucfirst($image) . " Chainguard Image",
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

    public function getTagsTable(string $image, array $onlyTags = []): string
    {
        try {
            $imageTags = $this->autodocs->getImageTags($image);
        } catch (\Exception $e) {
            return "";
        }

        usort($imageTags, [ImageTags::class, "orderTags"]);
        $imageTags = array_reverse($imageTags);
        $rows = [];

        foreach ($imageTags as $tag) {
            $now = new \DateTime();
            $update = new \DateTime($tag['lastUpdated']);
            $interval = $now->diff($update);

            //suppress tags older than 1 month
            if ($interval->m) {
                 continue;
            }

            //skip other tags when a set is provided
            if (count($onlyTags) AND !in_array($tag['name'], $onlyTags)) {
                continue;
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
