FROM php:8.3-apache

RUN \
  (curl -sS https://getcomposer.org/installer | php) && mv composer.phar /usr/local/bin/composer && rm -f composer-setup.php && \
  (curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash) && \
  apt install -y git libxml2-dev libzip-dev symfony-cli unzip zip && \
  docker-php-ext-install intl opcache pdo_mysql sockets sysvsem xml zip && \
  pecl install apcu ast && docker-php-ext-enable apcu ast && \
  a2enmod rewrite && \
  composer global require --dev friendsofphp/php-cs-fixer psalm/plugin-symfony vimeo/psalm
