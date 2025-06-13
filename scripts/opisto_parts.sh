#!/bin/bash
echo "$(date '+%Y-%m-%d %H:%M:%S') - Accessing opisto/parts" >> /var/www/html/api-soluval/var/log/cron.log

MAX_RETRIES=3
RETRY_DELAY=30
COUNT=1

while [ $COUNT -le $MAX_RETRIES ]; do
  curl -v https://api.soluval.fr/opisto/parts
  if [ $? -eq 0 ]; then
    break
  fi
  echo "Échec tentative $COUNT, nouvelle tentative dans $RETRY_DELAY secondes..." >> /var/www/html/api-soluval/var/log/cron.log
  sleep $RETRY_DELAY
  COUNT=$((COUNT+1))
done

if [ $COUNT -gt $MAX_RETRIES ]; then
  echo "La tâche opisto/parts a échoué après $MAX_RETRIES tentatives le $(date '+%Y-%m-%d %H:%M:%S')" | mail -s "Echec cron opisto/parts" dev.inzerty@groupevitaminet.com
fi