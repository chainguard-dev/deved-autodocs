---
title: "helm Image Variants"
type: "article"
description: "Detailed specs for helm Chainguard Image Variants"
date: 2023-03-07T11:07:52+02:00
lastmod: 2023-03-07T11:07:52+02:00
draft: false
tags: ["Reference", "Chainguard Images", "Product"]
images: []
menu:
  docs:
    parent: "helm"
weight: 550
toc: true
---

This page shows detailed information about all available variants of the Chainguard **helm** Image.

## Variants Compared
The **helm** Chainguard Image currently has one public variant: 

- `latest`

The table has detailed information about each of these variants.

|              | latest          |
|--------------|-----------------|
| Default User | `nonroot`       |
| Entrypoint   | `/usr/bin/helm` |
| CMD          | `help`          |
| Workdir      | not specified   |
| Has apk?     | no              |
| Has a shell? | no              |

## Image Dependencies
The table shows package distribution across all variants.

|                          | latest |
|--------------------------|--------|
| `ca-certificates-bundle` | X      |
| `helm`                   | X      |
| `wolfi-baselayout`       | X      |
| `kubectl`                | X      |
