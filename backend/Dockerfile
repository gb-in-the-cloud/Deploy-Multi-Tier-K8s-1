# Pinned to a PHP release that includes the PHP2Shell fixes (CVE-2026-109618/109619)
FROM php:8.4.27-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

# Serve from /var/www/html/public and send every request to index.php
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && a2enmod rewrite \
    && printf '<Directory /var/www/html/public>\n  AllowOverride None\n  FallbackResource /index.php\n</Directory>\nServerTokens Prod\nServerSignature Off\n' \
       > /etc/apache2/conf-enabled/app.conf

COPY public/ /var/www/html/public/
EXPOSE 80
