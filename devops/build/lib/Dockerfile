FROM fozzyua/docker-php-openswoole-base-image:v1.0.1-php8.4.5-openswoole-25.2.0

WORKDIR /var/www/lib

COPY --chown=1000:1000 ./ /var/www/lib/

ENTRYPOINT ["/bin/sh", "-c"]