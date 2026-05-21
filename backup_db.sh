#!/bin/bash

DB_NAME="wordlist"
BACKUP_DIR=$HOME/maljib
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_$(date +%Y%m%d).sql.gz"
TIME=$(date "+%Y-%m-%d %H:%M:%S")
RETENTION_DAYS=7

if [ $# -gt 0 ] || [ ! -f $BACKUP_FILE ]; then
    ssh -i $HOME/.ssh/maljib.key ubuntu@maljib.freeddns.org \
    "sudo mysqldump -u root --single-transaction $DB_NAME | gzip" > $BACKUP_FILE
    if [ $? -eq 0 ]; then
        find $BACKUP_DIR -name "${DB_NAME}_*.sql.gz" -mtime +$RETENTION_DAYS -delete
        if [ $? -ne 0 ]; then
            echo "$TIME - Failed to delete old backups."
        fi
    else
        echo "$TIME - Backup failed for $DB_NAME - $BACKUP_FILE not created."
        exit 1
    fi
fi
