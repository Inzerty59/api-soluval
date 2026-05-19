#!/bin/bash
PERIOD=${1:-15min}
echo "$(date '+%Y-%m-%d %H:%M:%S') - [Sync Photos] Lancement synchronisation photos - Période: $PERIOD" >> /var/www/html/api-soluval/var/log/cron.log
docker exec www-api bash -c "php bin/console app:sync-photos --period=$PERIOD" >> /var/www/html/api-soluval/var/log/cron.log 2>&1
if [ $? -ne 0 ]; then
  echo "$(date '+%Y-%m-%d %H:%M:%S') - [Sync Photos] ❌ ERREUR lors de la synchronisation" >> /var/www/html/api-soluval/var/log/cron.log
  echo "La tâche app:sync-photos a échoué le $(date '+%Y-%m-%d %H:%M:%S')" | mail -s "Echec cron app:sync-photos" dev.inzerty@groupevitaminet.com
else
  echo "$(date '+%Y-%m-%d %H:%M:%S') - [Sync Photos] ✅ SYNCHRO DES PHOTOS RÉUSSIE" >> /var/www/html/api-soluval/var/log/cron.log
fi
