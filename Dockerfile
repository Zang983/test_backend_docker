FROM php:8.2-apache

# Install dependencies, incluant OpenSSL
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    libfreetype6-dev \
    libicu-dev \
    libjpeg-dev \
    libpng-dev \
    libpq-dev \
    libzip-dev \
    unzip \
    openssl && \
    rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    gd \
    intl \
    opcache \
    pdo_pgsql \
    zip

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create JWT keys
RUN mkdir -p config/jwt && \
    openssl genrsa -out config/jwt/private.pem 2048 && \
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

# Set permissions
RUN chown -R www-data:www-data /var/www/html
