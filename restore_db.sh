#!/bin/bash

DB_NAME="wordlist"
BACKUP_DIR=$HOME/maljib
TIME=$(date "+%Y-%m-%d %H:%M:%S")

if [ -z $1 ]; then
    FILE_NAME="${DB_NAME}_$(date +%Y%m%d).sql.gz"
else
    FILE_NAME="${DB_NAME}_$1.sql.gz"
fi

FILE_TO_LOAD="$BACKUP_DIR/$FILE_NAME"
if [ ! -f $FILE_TO_LOAD ]; then
    echo "$TIME - $FILE_TO_LOAD file not found."
    exit 1
fi

echo "This will overwrite $DB_NAME database from $FILE_NAME file."
read -p "Are you sure? (yes/no): " CONFIRM
if [ "$CONFIRM" != "yes" ]; then
    echo "Restore cancelled."
    exit 1
fi

echo "$TIME - Restoring ..."
ssh -i ~/.ssh/maljib.key ubuntu@maljib.freeddns.org \
"gunzip | sudo mysql -u root $DB_NAME" < $FILE_TO_LOAD
if [ $? -ne 0 ]; then
    echo "Failed to restore from $FILE_TO_LOAD."
    exit 1
fi
echo "$TIME - Done."
