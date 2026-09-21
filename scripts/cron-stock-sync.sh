#!/bin/bash
PERIOD=${1:-2min}
echo "$(date '+%Y-%m-%d %H:%M:%S') - [Période $PERIOD] - Lancement de la synchronisation" >> /var/www/html/api-soluval/var/log/cron.log
/usr/bin/docker exec -u www-data www-api bash -c "php bin/console app:stock:sync --period=$PERIOD" >> /var/www/html/api-soluval/var/log/prod.log 2>&1