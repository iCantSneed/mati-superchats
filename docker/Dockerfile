FROM php:8.3-apache

COPY composer.dev.json /root/composer-dev/composer.json
COPY composer.psalm.json /root/composer-psalm/composer.json
RUN \
  (curl -sS https://getcomposer.org/installer | php) && mv composer.phar /usr/local/bin/composer && rm -f composer-setup.php && \
  (curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash) && \
  apt install -y git libxml2-dev libzip-dev mariadb-client symfony-cli unzip zip && \
  docker-php-ext-install ftp intl opcache pdo_mysql sockets sysvmsg sysvsem xml zip && \
  pecl install ast && docker-php-ext-enable ast && \
  a2enmod rewrite && \
  cd /root/composer-dev && composer install && \
  cd /root/composer-psalm && composer install
