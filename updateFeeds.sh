#!/usr/bin/env sh

export OUTPUT_PATH=workdir/cache
export REGISTRY_URL=cgr.dev/chainguard
export IMAGES_GROUP=720909c9f5279097d847ad02a2f24ba8f59de36a

echo "Getting images tags list...";
chainctl img ls -ojson --group ${IMAGES_GROUP} > ${OUTPUT_PATH}/images-tags.json 2>&1
echo "Finished.";
