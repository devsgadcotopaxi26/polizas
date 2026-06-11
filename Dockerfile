FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    python3 \
    python3-pip \
    python3-venv \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# --- AÑADE ESTO (Activar OpenSSL Legacy Provider para oscrypto) ---
RUN sed -i 's/^\[provider_sect\]/[provider_sect]\nlegacy = legacy_sect/g' /etc/ssl/openssl.cnf && \
    sed -i 's/^\[default_sect\]/[default_sect]\nactivate = 1\n[legacy_sect]\nactivate = 1/g' /etc/ssl/openssl.cnf

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Configure PHP settings for larger file uploads
RUN echo "upload_max_filesize = 64M\npost_max_size = 64M" > /usr/local/etc/php/conf.d/uploads.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install Laravel dependencies
RUN composer install --no-interaction --optimize-autoloader

# Install Python dependencies
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
COPY requirements.txt /var/www/requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

# Create system user to run composer and artisan commands
RUN useradd -G www-data,root -u 1000 -d /home/dev dev
RUN mkdir -p /home/dev/.composer && \
    chown -R dev:dev /home/dev

# Set permissions
RUN chown -R dev:www-data /var/www/storage /var/www/bootstrap/cache

USER dev

EXPOSE 9000
CMD ["php-fpm"]
