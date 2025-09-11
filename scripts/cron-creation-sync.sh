#!/bin/bash
PERIOD=${1:-15min}
echo "$(date '+%Y-%m-%d %H:%M:%S') - [Période $PERIOD] - Lancement de la synchronisation des nouvelles pièces" >> /var/www/html/api-soluval/var/log/cron.log
/usr/bin/docker exec www-api bash -c "php bin/console app:creation:sync --period=$PERIOD" >> /var/www/html/api-soluval/var/log/prod.log 2>&1
