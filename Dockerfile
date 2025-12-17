# Use a specific PHP version if you need it, e.g., php:8.2-fpm
FROM php:8.4-fpm

# 1. Install system dependencies
# libpq-dev: for PostgreSQL
# libicu-dev: for the 'intl' extension
# libzip-dev: for the 'zip' extension (composer needs this)
# unzip & git: utility for composer
# 2. Update and clean up in a single command layer
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libzip-dev \
	libcurl4-openssl-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*


# Install PHP extensions required by Symfony and PostgreSQL
# Note: pdo is usually installed by default, but pdo_pgsql needs the libpq-dev package
RUN docker-php-ext-install pdo_pgsql zip intl curl opcache

# Force PHP-FPM to listen on all interfaces (0.0.0.0)
# This is crucial for Nginx in another container to connect.
RUN echo "listen = 0.0.0.0:9000" > /usr/local/etc/php-fpm.d/zz-docker-listen.conf

COPY --link \
    --from=ghcr.io/symfony-cli/symfony-cli:latest \
    /usr/local/bin/symfony /usr/local/bin/symfony
	
# 3. (Optional but Recommended) Install Composer globally - should already be there
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Force PHP-FPM to listen on all interfaces
RUN echo "listen = 0.0.0.0:9000" > /usr/local/etc/php-fpm.d/zz-docker-listen.conf	

WORKDIR /srv/app
