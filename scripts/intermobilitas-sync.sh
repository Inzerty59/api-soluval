#!/bin/bash
LOG_FILE="/var/www/html/api-soluval/var/log/intermobilitas-sync.log"
EMAIL="dev.inzerty@groupevitaminet.com"
MAX_RETRIES=3
RETRY_DELAY=60

echo "$(date '+%Y-%m-%d %H:%M:%S') - Starting TotalParts synchronization" >> "$LOG_FILE"

COUNT=1
SUCCESS=0

while [ $COUNT -le $MAX_RETRIES ]; do
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Attempt $COUNT/$MAX_RETRIES" >> "$LOG_FILE"
  
  docker exec -it www-api php bin/console app:intermobilitas:sync --delete-unavailable >> "$LOG_FILE" 2>&1
  
  EXIT_CODE=$?
  
  if [ $EXIT_CODE -eq 0 ]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Synchronization successful" >> "$LOG_FILE"
    SUCCESS=1
    break
  else
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Synchronization failed (exit code: $EXIT_CODE)" >> "$LOG_FILE"
    
    if [ $COUNT -lt $MAX_RETRIES ]; then
      echo "$(date '+%Y-%m-%d %H:%M:%S') - Retrying in $RETRY_DELAY seconds..." >> "$LOG_FILE"
      sleep $RETRY_DELAY
    fi
  fi
  
  COUNT=$((COUNT+1))
done

if [ $SUCCESS -eq 0 ]; then
  echo "TotalParts synchronization failed after $MAX_RETRIES attempts on $(date '+%Y-%m-%d %H:%M:%S'). Check log: $LOG_FILE" | \
    mail -s "TotalParts Sync Failed" "$EMAIL"
  echo "$(date '+%Y-%m-%d %H:%M:%S') - Alert email sent" >> "$LOG_FILE"
fi

echo "$(date '+%Y-%m-%d %H:%M:%S') - Synchronization process completed" >> "$LOG_FILE"
echo "---" >> "$LOG_FILE"

exit $EXIT_CODE
