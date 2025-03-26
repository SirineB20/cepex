#!/usr/bin/env bash

cd /var/www/html
# Wait for MySQL to be ready
until mysql -h "$MYSQL_HOST" -P "$MYSQL_PORT" -u root -proot -e 'SELECT 1' >/dev/null 2>&1; do
  echo "Waiting for MySQL to be ready..."
  sleep 2
done
echo "MySQL is available."
# if [ $(mysql -h "$MYSQL_HOST" -N -s -u root -proot -e \
#         "select count(*) from information_schema.tables where \
#             table_schema='dpm' and table_name='batch';") -eq 1 ]; then
#         echo "Database dpm exist"
#     else
#         >&2 echo "Importing data into dpm"
#         mysql -h $MYSQL_HOST -u root -proot dpm < $(pwd)/dpm-infrastructure/environnement_dev/dpm.sql
#         >&2 echo "Import dpm has finished"
#     fi

composer install --no-interaction 

# /usr/sbin/apache2ctl -D FOREGROUND
php-fpm
