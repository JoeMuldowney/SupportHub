#!/bin/bash

BACKUP_DIR=/opt/backups
mkdir -p $BACKUP_DIR
DATE=$(date +%F-%H%M)

# Database backup
mysqldump \
  -h mysql \
  -u root \
  --skip-ssl \
  -p$(cat /run/secrets/db_root_pass) \
  ticket_system_db > $BACKUP_DIR/db-$(date +%F-%H%M).sql

# Images volume backup

tar czf $BACKUP_DIR/images-$DATE.tar.gz -C /var/lib/tickets/data/ .

# Cleanup old backups
find $BACKUP_DIR -type f -mtime +7 -delete
