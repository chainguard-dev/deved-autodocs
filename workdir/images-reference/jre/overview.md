---
title: "Image Overview: jre"
type: "article"
description: "Overview: jre Chainguard Images"
date: 2022-11-01T11:07:52+02:00
lastmod: 2022-11-01T11:07:52+02:00
draft: false
images: []
menu:
  docs:
    parent: "images-reference"
weight: 600
toc: true
---

`stable` [cgr.dev/chainguard/jre](https://github.com/chainguard-images/images/tree/main/images/jre)
| Tags             | Aliases                                                                       |
|------------------|-------------------------------------------------------------------------------|
| `latest`         | openjdk-jre-17, openjdk-jre-17.0, openjdk-jre-17.0.6, openjdk-jre-17.0.6-r0   |
| `openjdk-jre-11` | openjdk-jre-11, openjdk-jre-11.0, openjdk-jre-11.0.18, openjdk-jre-11.0.18-r0 |



Java JRE image using OpenJDK via [Adoptium Temurin](https://adoptium.net/en-GB/temurin/) sources.

## Get It!

The image is available on `cgr.dev`:

```
docker pull cgr.dev/chainguard/jre:latest
```
## Use it

Create a simple Java class

```sh
cat >HelloWolfi.java <<EOL
class HelloWolfi
{
    public static void main(String args[])
    {
        System.out.println("Hello Wolfi users!");
    }
}
EOL
```

Next create a multistage Dockerfile and add the Java class

```sh
cat >Dockerfile <<EOL
FROM cgr.dev/chainguard/jdk:openjdk-17

COPY HelloWolfi.java /home/build/
RUN /usr/lib/jvm/openjdk/bin/javac HelloWolfi.java

FROM cgr.dev/chainguard/jre:openjdk-jre-17

COPY --from=0 /home/build/HelloWolfi.class /app/
CMD ["HelloWolfi"]
EOL
```

Build the image

```sh
docker build -t my-simple-java-app .
```

Run the image
```sh
docker run my-simple-java-app
```
