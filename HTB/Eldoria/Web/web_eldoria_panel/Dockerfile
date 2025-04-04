# Use PHP-FPM as base
FROM php:8.1-rc-fpm-bookworm

# Copy flag
COPY flag.txt /flag.txt

# Install system dependencies needed for Composer, Chrome, your app, plus nginx & supervisor
RUN apt-get update && apt-get install -y --no-install-recommends \
	unzip \
	git \
	curl \
	wget \
	gnupg \
	libnss3 \
	libxss1 \
	libasound2 \
	libglib2.0-0 \
	libgbm-dev \
	fonts-liberation \
	libappindicator3-1 \
	xdg-utils \
	libcurl4 \
	nginx \
	chromium \
	supervisor \
	python3 \
    python3-pip \
	&& rm -rf /var/lib/apt/lists/*

# Add chromium to PATH
ENV PATH="/usr/lib/chromium:${PATH}"

# Install the PHP sockets extension
RUN docker-php-ext-install sockets

# Copy Composer from official Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy php.ini
COPY conf/php.ini /usr/local/etc/php/php.ini

# Copy your application code
COPY challenge /app
RUN chown -R www-data:www-data /app \
	&& chmod -R 777 /app
RUN chown -R www-data:www-data /var/www/ \
	&& chmod -R 777 /var/www/
	
# Install PHP dependencies using Composer
RUN composer install --no-dev --optimize-autoloader

# Install pip packages
RUN pip3 install --no-cache-dir -r /app/src/bot/requirements.txt --break-system-packages

# Copy Nginx config (see below) to override the default config
COPY conf/nginx.conf /etc/nginx/sites-enabled/default

# Copy Supervisor config (see below)
COPY conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port 80 (Nginx default)
EXPOSE 80

# Start supervisord
COPY --chown=root entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

