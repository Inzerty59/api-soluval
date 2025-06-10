#!/bin/bash
echo "$(date '+%Y-%m-%d %H:%M:%S') - Running app:stock:sync" >> /var/www/html/api-soluval/var/log/cron.log
/usr/bin/docker exec www-api bash -c "php bin/console app:stock:sync" >> /var/www/html/api-soluval/var/log/prod.log 2>&1