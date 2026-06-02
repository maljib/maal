#!/bin/bash

DB_NAME="wordlist"
BACKUP_DIR=$HOME/maljib
BACKUP_NOW="$(dirname $(realpath "$0"))/backup_db.sh now"
TIME=$(date "+%Y-%m-%d %H:%M:%S")

if [ -z $1 ]; then
    echo "Usage: $0 yyyymmdd[_HHMMSS]"
    exit 1
fi
FILE_NAME="${DB_NAME}_$1.sql.gz"

FILE_TO_LOAD="$BACKUP_DIR/$FILE_NAME"
if [ ! -f $FILE_TO_LOAD ]; then
    echo "$TIME - $FILE_TO_LOAD file not found."
    exit 1
fi

read -p "Remote DB host: " DB_HOST
DB_HOST=${DB_HOST// /}
if [ -z $DB_HOST ]; then
    gunzip -c $FILE_TO_LOAD | sudo mysql -u root $DB_NAME
    if [ $? -ne 0 ]; then
        echo "Failed to restore the development database from $FILE_TO_LOAD."
        exit 1
    fi
else
    echo "This will overwrite $DB_NAME database from $FILE_NAME file."
    read -p "Are you sure? (yes/no): " CONFIRM
    if [ "$CONFIRM" != "yes" ]; then
        echo "Restore cancelled."
        exit 1
    fi
    echo "$TIME - Backing up current database ..."
    $BACKUP_NOW
    if [ $? -ne 0 ]; then
        echo "Failed to back up current database."
        exit 1
    fi
    echo "$TIME - Restoring ..."
    ssh -i ~/.ssh/maljib.key ubuntu@$DB_HOST \
        "gunzip | sudo mysql -u root $DB_NAME" < $FILE_TO_LOAD
    if [ $? -ne 0 ]; then
        echo "Failed to restore from $FILE_TO_LOAD."
        exit 1
    fi
    echo "$TIME - Done."
fi