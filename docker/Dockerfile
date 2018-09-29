# The wordpress:php7.2 image includes a pretty obtrusive entrypoint
# FROM php:7.2-apache
FROM wordpress:php7.2

# Install apt dependencies
RUN apt-get update && apt-get install -qq -y --fix-missing --no-install-recommends \
    build-essential \
    git \
    less \
    libcairo2-dev \
    libgif-dev \
    libjpeg-dev \
    libyaml-dev \
    mysql-client \
    pkg-config \
    subversion \
    sudo \
    unzip \
    vim \
    wget \
    zip \
    ;

# Install xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Set perms on site and bin dirs
RUN chown -R www-data:www-data /var/www/ /usr/local/bin/

# Install Composer
COPY bin/install-composer.sh /tmp/
RUN bash /tmp/install-composer.sh
