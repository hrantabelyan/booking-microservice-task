# USE THE FPM VARIANT HERE
FROM php:8.4-fpm-bookworm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    redis-tools \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure and Install PHP extensions
RUN docker-php-ext-install -j2 \
    pdo_pgsql \
    zip \
    intl \
    pcntl \
    sockets \
    && pecl install redis \
    && docker-php-ext-enable redis

# Copy application files
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy and set entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
