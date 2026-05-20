#!/bin/sh

mkdir -p /var/www/html/public/assets/images/uploads

chown -R www-data:www-data \
/var/www/html/public/assets/images/uploads

chmod -R 775 \
/var/www/html/public/assets/images/uploads

php-fpm