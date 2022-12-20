FROM cgr.dev/chainguard/php:latest-glibc-dev AS builder
COPY . /app
RUN cd /app && \
    composer install --no-progress --no-dev --prefer-dist && \
    chown -R php.php /app

FROM cgr.dev/chainguard/php:latest-glibc
COPY --from=builder /app /app

ENTRYPOINT [ "php", "/app/autodocs" ]

