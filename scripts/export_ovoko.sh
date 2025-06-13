#!/bin/bash
echo "$(date '+%Y-%m-%d %H:%M:%S') - Running app:export-ovoko" >> /var/www/html/api-soluval/var/log/cron.log
docker exec www-api bash -c "php bin/console app:export-ovoko" >> /var/www/html/api-soluval/var/log/cron.log 2>&1
if [ $? -ne 0 ]; then
  echo "La tâche app:export-ovoko a échoué le $(date '+%Y-%m-%d %H:%M:%S')" | mail -s "Echec cron app:export-ovoko" dev.inzerty@groupevitaminet.com
fi