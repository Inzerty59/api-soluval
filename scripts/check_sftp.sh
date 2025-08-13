#!/bin/bash

LOGFILE="/var/www/html/api-soluval/var/log/cron.log"

echo "$(date '+%Y-%m-%d %H:%M:%S') - Lancement du script check_sftp.sh" >> "$LOGFILE"

set -a
source /var/www/html/api-soluval/.env
set +a

HOST="$SFTP_HOST"
USER="$SFTP_USER"
PASS="$SFTP_PASS"
REMOTE_DIR="$SFTP_PATH"

lftp -u "$USER","$PASS" sftp://"$HOST" <<EOF
cls -1 $REMOTE_DIR
EOF

if [ $? -ne 0 ]; then
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Erreur lors de l'exécution de check_sftp.sh" >> "$LOGFILE"
else
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Fin du script check_sftp.sh" >> "$LOGFILE"
fi

echo "$(date '+%Y-%m-%d %H:%M:%S') - Lancement de la commande app:francecasse:sync" >> "$LOGFILE"
/usr/bin/docker exec www-api bash -c "php bin/console app:francecasse:sync" >> "$LOGFILE" 2>&1