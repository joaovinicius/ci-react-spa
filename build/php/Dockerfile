FROM php:8.1-apache

# Set timezone
ENV TZ=${TZ:-UTC}
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

ARG UID=1000
ARG GID=1000

# Criar usuário com o mesmo UID/GID do host
RUN usermod -u ${UID} www-data && groupmod -g ${GID} www-data

RUN apt-get update && apt-get install -y \
        zip \
        libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libicu-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) mysqli pdo pdo_mysql opcache zip gd intl dom \
    && a2enmod rewrite \
    && a2enmod headers

# Start Apache in the foreground
# Instalar Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configurar permissões corretas
RUN chown -R www-data:www-data /var/www/html

# Copy php settings
COPY ./php.ini ${PHP_INI_DIR}/conf.d/99-php.ini

COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]