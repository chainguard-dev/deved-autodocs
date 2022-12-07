#!/usr/bin/env bash
output="workdir/markdown/melange-pipelines"

[ ! -d "$output" ] && mkdir -p "$output"

for f in ./workdir/yaml/pipelines/*; do
    if [ -d "$f" ] && [ $(basename $f) != "_meta" ]; then
        # Will not run if no directories are available
        echo "Building pipeline docs for $f"
        [ ! -d "$output/$(basename $f)" ] && mkdir -p "$output/$(basename $f)"
        ./vendor/bin/yamldocs build docs source=$f output="$output/$(basename $f)" builder=melange-pipeline
    fi
done

# Build top-level pipelines
./vendor/bin/yamldocs build docs source=./workdir/yaml/pipelines output=$output builder=melange-pipeline
