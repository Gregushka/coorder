FROM php:8.4-fpm

# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    # Dependencies for mbstring
    libonig-dev \ 
    # Dependencies for GD
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# 2. Configure GD extension 
# This tells PHP how to find the libraries for JPEG and FreeType
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# 3. Install PHP extensions
# Added 'exif' and 'mbstring' here
RUN docker-php-ext-install \
    pdo_pgsql \
    zip \
    intl \
    curl \
    opcache \
    mbstring \
    gd \
    exif

# Force PHP-FPM to listen on all interfaces
RUN echo "listen = 0.0.0.0:9000" > /usr/local/etc/php-fpm.d/zz-docker-listen.conf

# Install Symfony CLI
COPY --link --from=ghcr.io/symfony-cli/symfony-cli:latest /usr/local/bin/symfony /usr/local/bin/symfony
    
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /srv/app