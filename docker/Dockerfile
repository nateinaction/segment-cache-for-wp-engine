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

# Install Composer
COPY bin/install-composer.sh /tmp/
RUN mkdir /var/www/.composer/ && chown www-data:www-data /var/www/.composer/ \
    && bash /tmp/install-composer.sh

# Install WP Test Harness
RUN bin/install-wp-test-harness.sh

# Install WP CLI
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod 755 /usr/local/bin/wp \
    && mkdir /var/www/.wp-cli/

# Install stop-emails mu plugin
COPY docker/bin/stop-emails.php /var/www/html/wp-content/mu-plugins/

# Set permissions on WordPress and WordPress Test Lib directories
RUN chown -R www-data:www-data /var/www/ /tmp/wordpress-tests-lib/
