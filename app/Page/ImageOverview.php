<?php

namespace App\Page;

use Yamldocs\Mark;
use App\ReadmeReader;
use App\TagsReader;

class ImageOverview implements ReferencePage
{
    public function code($str): string
    {
        return sprintf("`%s`", $str);
    }

    /**
     * @param string $image
     * @return string
     */
    public function getContent(string $image): string
    {
        $readme = ReadmeReader::getContent($image . '/README.md');
        try {
            $imageMeta = TagsReader::getTags(basename($image));
        } catch (\Exception $e) {
            return "";
        }

        $reference = '`' . $imageMeta['status'] . '` ' .  '[' . $imageMeta['ref'] . '](https://github.com/chainguard-images/images/tree/main/images/' . basename($image) . ')';

        $rows = [];
        foreach ($imageMeta['tags'] as $tag) {
            $rows[] = [
                '`' . $tag['primary'] . '`',
                implode(', ', array_map(array($this, 'code'), $tag['dynamic']['resolved']))
            ];
        }

        $tagsTable = Mark::table($rows, ['Tags', 'Aliases']);

        return $reference . "\n" . $tagsTable . "\n" . $readme;
    }

    public function getSaveName(string $image): string
    {
        return 'overview.md';
    }
}
