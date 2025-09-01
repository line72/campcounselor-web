# Multi-stage Dockerfile for Camp Counselor Laravel Application

# Build stage for frontend assets
FROM node:20-slim AS frontend-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install Node.js dependencies (including dev dependencies for build)
RUN npm ci

# Copy source files needed for build
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./
COPY resources/ ./resources/

# Build production assets
RUN npm run build

# Production stage
FROM debian:bookworm-slim

# Install system dependencies and add PHP repository
RUN apt-get update && apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release \
    && curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /usr/share/keyrings/sury-php.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/sury-php.list \
    && apt-get update && apt-get install -y \
    apache2 \
    php8.2 \
    php8.2-cli \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-sqlite3 \
    php8.2-redis \
    php8.2-memcached \
    php8.2-curl \
    php8.2-dom \
    php8.2-exif \
    php8.2-fileinfo \
    php8.2-gd \
    php8.2-intl \
    php8.2-mbstring \
    php8.2-opcache \
    php8.2-pdo \
    php8.2-tokenizer \
    php8.2-xml \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-ctype \
    libapache2-mod-php8.2 \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers env

# Configure PHP for better error reporting in Docker
RUN echo 'log_errors = On' >> /etc/php/8.2/apache2/php.ini \
    && echo 'error_log = /var/log/apache2/php_errors.log' >> /etc/php/8.2/apache2/php.ini \
    && echo 'display_errors = Off' >> /etc/php/8.2/apache2/php.ini \
    && echo 'display_startup_errors = Off' >> /etc/php/8.2/apache2/php.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy built frontend assets from build stage
COPY --from=frontend-builder /app/public/build ./public/build

# Create default .env file from example
RUN cp docker.env.example .env

# Install PHP dependencies (production only)
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Configure Apache virtual host
RUN echo '<VirtualHost *:80>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html/public\n\
    \n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
        \n\
        # Laravel pretty URLs\n\
        RewriteEngine On\n\
        RewriteCond %{REQUEST_FILENAME} !-f\n\
        RewriteCond %{REQUEST_FILENAME} !-d\n\
        RewriteRule ^(.*)$ index.php [QSA,L]\n\
    </Directory>\n\
    \n\
    # Security headers\n\
    Header always set X-Content-Type-Options nosniff\n\
    Header always set X-Frame-Options DENY\n\
    Header always set X-XSS-Protection "1; mode=block"\n\
    \n\
    # Environment variables (APP_NAME is hardcoded in config)\n\
    PassEnv APP_ENV\n\
    PassEnv APP_KEY\n\
    PassEnv APP_DEBUG\n\
    PassEnv APP_TIMEZONE\n\
    PassEnv APP_URL\n\
    PassEnv DB_CONNECTION\n\
    PassEnv DB_HOST\n\
    PassEnv DB_PORT\n\
    PassEnv DB_DATABASE\n\
    PassEnv DB_USERNAME\n\
    PassEnv DB_PASSWORD\n\
    PassEnv LOG_CHANNEL\n\
    PassEnv LOG_LEVEL\n\
    \n\
    # Trust reverse proxy headers for HTTPS termination\n\
    SetEnvIf X-Forwarded-Proto "https" HTTPS=on\n\
    SetEnvIf X-Forwarded-For "^.*" REMOTE_ADDR=%{HTTP:X-Forwarded-For}\n\
    \n\
    # Custom error and access logs\n\
    ErrorLog ${APACHE_LOG_DIR}/campcounselor_error.log\n\
    CustomLog ${APACHE_LOG_DIR}/campcounselor_access.log combined\n\
    \n\
    # PHP error logging\n\
    php_admin_value log_errors On\n\
    php_admin_value error_log ${APACHE_LOG_DIR}/php_errors.log\n\
</VirtualHost>' > /etc/apache2/sites-available/campcounselor.conf

# Disable default site and enable our app
RUN a2dissite 000-default && a2ensite campcounselor

# Create startup script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "=== Camp Counselor Docker Container Starting ==="\n\
\n\
# Ensure .env file exists\n\
if [ ! -f .env ]; then\n\
    echo "Creating .env file from docker.env.example..."\n\
    cp docker.env.example .env\n\
fi\n\
\n\
# Generate app key if not provided\n\
if [ -z "$APP_KEY" ]; then\n\
    echo "Generating application key..."\n\
    php artisan key:generate --force --no-interaction\n\
else\n\
    echo "Using provided APP_KEY"\n\
fi\n\
\n\
# Clear and cache config for production\n\
echo "Optimizing Laravel for production..."\n\
php artisan config:clear\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Note: Database migrations must be run manually\n\
echo "Remember to run migrations: docker exec <container> php artisan migrate"\n\
\n\
# Set proper permissions again (in case of volume mounts)\n\
echo "Setting file permissions..."\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache\n\
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache\n\
\n\
echo "=== Starting Apache HTTP Server ==="\n\
# Start Apache in foreground\n\
exec apache2ctl -D FOREGROUND' > /usr/local/bin/start-campcounselor.sh \
    && chmod +x /usr/local/bin/start-campcounselor.sh

# Set default environment variables (APP_NAME is hardcoded in config/app.php)
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_TIMEZONE=UTC \
    APP_URL=http://localhost \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/database/database.sqlite \
    LOG_CHANNEL=single \
    LOG_LEVEL=info

# Create SQLite database file
RUN touch /var/www/html/database/database.sqlite \
    && chown www-data:www-data /var/www/html/database/database.sqlite

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start the application
CMD ["/usr/local/bin/start-campcounselor.sh"]
