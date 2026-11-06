#!/bin/bash
echo "$(date '+%Y-%m-%d %H:%M:%S') - Running app:export-appropo-unstock" >> /var/www/html/api-soluval/var/log/cron.log
docker exec -u www-data www-api bash -c "php bin/console app:export-appropo-unstock" >> /var/www/html/api-soluval/var/log/cron.log 2>&1
if [ $? -ne 0 ]; then
  echo "La tâche app:export-appropo-unstock a échoué le $(date '+%Y-%m-%d %H:%M:%S')" | mail -s "Echec cron app:export-appropo-unstock" dev.inzerty@groupevitaminet.com
fi
