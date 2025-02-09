#!/bin/bash
BACKUP_DIR="/var/backups/mikhmon"
DATE=$(date +%Y%m%d)

# Backup database
mysqldump mikhmon > $BACKUP_DIR/db_$DATE.sql

# Backup WhatsApp session
tar -czf $BACKUP_DIR/wa_session_$DATE.tar.gz auth_info_baileys/

# Keep last 7 days only
find $BACKUP_DIR -mtime +7 -delete 