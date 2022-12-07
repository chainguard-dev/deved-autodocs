#!/usr/bin/env bash

for f in ./workdir/yaml/pipelines/*; do
    if [ -d "$f" ] && [ $(basename $f) != "_meta" ]; then
        # Will not run if no directories are available
        echo "Building pipeline docs for $f"
        ./vendor/bin/yamldocs build docs source=$f output="./workdir/markdown/melange-pipelines/$(basename $f)" builder=melange-pipeline
    fi
done

# Build top-level pipelines
./vendor/bin/yamldocs build docs source=./workdir/yaml/pipelines output=./workdir/markdown/melange-pipelines builder=melange-pipeline
