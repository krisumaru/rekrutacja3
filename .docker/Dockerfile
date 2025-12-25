FROM php:8.4-apache

# Set the working directory inside the container
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update \
    && apt-get install -y \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        unzip \
        git \
        cron \
        nano \
        supervisor

# Install PHP extensions
RUN docker-php-ext-install \
    intl \
    pdo \
    pdo_pgsql \
    zip \
    pdo \
    pdo_mysql \
    opcache

# Install XDEBUG, APCU
RUN pecl install xdebug apcu
RUN docker-php-ext-enable xdebug apcu

# Install symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Install Composer CLI
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN echo "umask 0000" >> /root/.bashrc

# Start Supervisor, which will run cron -f by default.
CMD ["/usr/bin/supervisord"]
