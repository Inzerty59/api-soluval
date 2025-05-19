#!/bin/bash
echo "$(date '+%Y-%m-%d %H:%M:%S') - Accessing opisto/parts" >> /var/www/html/api-soluval/var/log/cron.log
curl -s https://api.soluval.fr/opisto/parts >> /var/www/html/api-soluval/var/log/parts.log 2>&1
if [ $? -ne 0 ]; then
  echo "La tâche opisto/parts a échoué le $(date '+%Y-%m-%d %H:%M:%S')" | mail -s "Échec cron opisto/parts" florent.devynck@groupevitaminet.com,franck.depoorter@groupevitaminet.com
fi