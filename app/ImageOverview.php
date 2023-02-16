<?php

namespace App;

class ImageOverview
{
    public string $imagePath;

    public function __construct(string $image)
    {
        $this->imagePath = $image;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getContent(): string
    {
        $readme = ReadmeReader::getContent($this->imagePath . '/README.md');
        try {
            $imageMeta = TagsReader::getTags(basename($this->imagePath));
        } catch (\Exception $e) {
            return "";
        }

        $reference = '`' . $imageMeta['status'] . '` ' .  '[' . $imageMeta['ref'] . '](https://github.com/chainguard-images/images/tree/main/images/' . basename($this->imagePath) . ')';

        $rows = [];
        foreach ($imageMeta['tags'] as $tag) {
            $rows[] = [
                '`' . $tag['primary'] . '`',
                implode(', ', $tag['dynamic']['resolved'])
            ];
        }

        $tagsTable = Mark::table($rows, ['Tags', 'Aliases']);

        return $reference . "\n" . $tagsTable . "\n" . $readme;
    }
}