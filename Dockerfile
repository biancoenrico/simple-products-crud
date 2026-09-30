FROM webdevops/php-nginx:8.3

# nginx serve solo public/: con la radice su /app, .env e le chiavi sarebbero raggiungibili dal browser
ENV WEB_DOCUMENT_ROOT=/app/public
ENV WEB_DOCUMENT_INDEX=index.php

COPY docker/entrypoint.d/ /entrypoint.d/
COPY . /app

WORKDIR /app
RUN composer install --no-dev --optimize-autoloader --no-interaction
