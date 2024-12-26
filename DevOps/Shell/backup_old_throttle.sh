#!/bin/bash

#Usage:
#with option -c(--compression) you can take compressed backup (values can be 'tpgz' or 'xbstream')
#with option -l (--locale) backups are taken on local storage (/opt) else on mounted remote catalog wia smb, this catalog must be already mounted
#with option -i (--incremental) you can take incremental backup, for wtis action there must be file *.lsn with last LSN in previouse backup

MYSQL_USER="MYSQL_USER_EXAMPLE"
MYSQL_PASS="MYSQL_PASS_EXAMPLE"
log="/var/log/backup/backup.log"
PREF=/mnt/data
TYPE=full
PARALLEL=1
workDir=/opt/backup
COMPRESSION=no_compression
localPath=/mnt/data
remotePath=/mnt

tablesFile="$workDir/tables"

#parse arguments
TEMP=`getopt -o u:p:c:li --long user:,pass:,compression:,local,parallel:,incremental  -- "$@"`

if [ $? != 0 ] ; then echo "Terminating..." >&2 ; exit 1 ; fi

eval set -- "$TEMP"

while true; do
  case "$1" in
    -u | --user ) MYSQL_USER="$2"; shift 2 ;;
    -p | --pass ) MYSQL_PASS="$2"; shift 2;;
    -c | --compression ) COMPRESSION="$2"; shift 2 ;;
    -l | --local ) PREF="/mnt/data"; shift ;;
    -i | --incremental ) TYPE="inc"; shift ;;
    --parallel ) PARALLEL="$2"; shift 2 ;;

    -- ) shift; break ;;
    * ) break ;;
  esac
done

lastPath=$PREF/backup/last
incPath=$PREF/backup/inc
filename=`date +%Y-%m-%d_%H-%M-%S`
lsndir=$PREF/backup
lsnfile=$PREF/backup/xtrabackup_checkpoints

fullThrottle=200
incThrottle=40

function backup_full {
    echo "$(date +%Y-%m-%d_%H-%M-%S) remove old data from local directory"
    rm -R $localPath/backup/last/*
    rm -R $localPath/backup/inc/*
    case $COMPRESSION in
        'tpgz')
            echo "$(date +%Y-%m-%d_%H-%M-%S) using tpgzCompression"
            innobackupex --extra-lsndir=$lsndir --throttle=$fullThrottle --user=$MYSQL_USER --pass=$MYSQL_PASS --parallel=$PARALLEL --stream=tar ./ | pigz - > $lastPath/$filename.tar.gz
            if [ $PREF == $localPath ];then
                echo "$(date +%Y-%m-%d_%H-%M-%S) start backup copy"
                cp $lastPath/$filename.tar.gz /mnt/backup/last
                echo "$(date +%Y-%m-%d_%H-%M-%S) end backup copy"
            fi
        ;;
        'xbstream')
            echo "$(date +%Y-%m-%d_%H-%M-%S) using xbstream"
            innobackupex --extra-lsndir=$lsndir --throttle=$fullThrottle--user=$MYSQL_USER --pass=$MYSQL_PASS --stream=xbstream --compress --compress-threads=$PARALLEL ./ > $lastPath/$filename.xbstream
            if [ $PREF == $localPath ];then
                echo "$(date +%Y-%m-%d_%H-%M-%S) start backup copy"
                cp $lastPath/$filename.xbstream /mnt/backup/last
                echo "$(date +%Y-%m-%d_%H-%M-%S) end backup copy"
            fi
        ;;
        *)
            echo "$(date +%Y-%m-%d_%H-%M-%S) full backup with no compression"
            innobackupex --extra-lsndir=$lsndir --throttle=$fullThrottle --user=$MYSQL_USER --pass=$MYSQL_PASS --parallel=$PARALLEL --no-timestamp $lastPath/$filename
            if [ $PREF == $localPath ];then
                echo "$(date +%Y-%m-%d_%H-%M-%S) start backup copy"
                cp -r $lastPath/$filename /mnt/backup/last
                echo "$(date +%Y-%m-%d_%H-%M-%S) end backup copy"
            fi
    esac
}

function backup_inc {
    lsn=`ssh remote@backup.project.com "cat /var/ftp/backup-production/db/xtrabackup_checkpoints" | grep "to_lsn" $lsnfile  | awk {'print $3'}`
    if [ -n "`echo $lsn | sed 's/[0-9]//g'`" ];then
        echo "Wrong LSN or it doesn't exist, exiting now"
        exit
    else

        echo "Create tables file use tables_filte.sql"
        mysql -u$MYSQL_USER -p$MYSQL_PASS < tables_filter.sql -s --skip-column-names > $tablesFile


        case $COMPRESSION in
            'xbstream')
                echo "$(date +%Y-%m-%d_%H-%M-%S) using xbstream"
                innobackupex --tables-file=$tablesFile --throttle=$incThrottle --extra-lsndir=$lsndir --user=$MYSQL_USER --password=$MYSQL_PASS --no-lock --parallel=$PARALLEL --incremental --incremental-lsn=$lsn --stream=xbstream --compress --compress-threads=$PARALLEL ./ > $incPath/$filename.xbstream
                if [ $PREF == $localPath ];then
                        echo "$(date +%Y-%m-%d_%H-%M-%S) start backup copy"
                        cp -r $incPath/$filename.xbstream /mnt/backup/inc
                        echo "$(date +%Y-%m-%d_%H-%M-%S) end backup copy"
                fi
            ;;
            *)
                echo "$(date +%Y-%m-%d_%H-%M-%S) using no compression"
                innobackupex --tables-file=$tablesFile --throttle=$incThrottle --extra-lsndir=$lsndir --user=$MYSQL_USER --password=$MYSQL_PASS --no-lock --parallel=$PARALLEL --incremental --incremental-lsn=$lsn --no-timestamp /mnt/backup/inc/$filename
#                if [ $PREF == "$localPath" ];then
#                    echo "$(date +%Y-%m-%d_%H-%M-%S) start backup copy"
#                    cp -r $incPath/$filename /mnt/backup/inc
#                    echo "$(date +%Y-%m-%d_%H-%M-%S) end backup copy"
#                fi
        esac
    fi
}

function mount_backup {
    echo "$(date +%Y-%m-%d_%H-%M-%S) Checking if backup share already mounted"
    mountStatus=$(mount -l | grep "/mnt/backup" | grep -c "backup.project.com")
    if [ "$mountStatus" -ne 0 ];then
        echo "$(date +%Y-%m-%d_%H-%M-%S) Backup share already mounted"
    else
        sshfs -o nonempty -o IdentityFile=/root/.ssh/nopass_rsa remote@backup.project.com:/var/ftp/backup-production/db /mnt/backup
        # Checking if mount was success
        if [ $? -eq 0 ]; then
           echo "$(date +%Y-%m-%d_%H-%M-%S) Mount success! ----------------------------------------"
        else
           echo "$(date +%Y-%m-%d_%H-%M-%S) [ERROR]Something went wrong with the mount..., exiting..."
           exit 1
        fi
    fi
}

{

ulimit -n 16384

if [ $TYPE == 'full' ];then
    echo "$(date +%Y-%m-%d_%H-%M-%S) remove lock file"
    #Removing lock file
    rm /var/run/backup.lock
fi

if [ ! -f /var/run/backup.lock ] && ! pgrep -f innobackupex ;then
    date > /var/run/backup.lock
else
    echo " $(date +%Y-%m-%d_%H-%M-%S) Backup script already running, exiting!"
    exit 0
fi

echo "$(date +%Y-%m-%d_%H-%M-%S) Mounting backup directory, located on backup server"
mount_backup

echo "------------------- RUN BACKUP $(date +%Y-%m-%d_%H-%M-%S)---------------------"

cd $workDir

case $TYPE in
        'inc')
            backup_inc
        ;;
        'full')
            backup_full
        ;;
        *)
            echo "$(date +%Y-%m-%d_%H-%M-%S) entered argument is incorrect, exiting now"
            exit
esac

echo "$(date +%Y-%m-%d_%H-%M-%S) copy lsnfile"
cp $lsnfile /mnt/backup

echo "$(date +%Y-%m-%d_%H-%M-%S) remove lock file"
#Removing lock file
rm /var/run/backup.lock

echo "$(date +%Y-%m-%d_%H-%M-%S) umount /mnt/backup"
umount /mnt/backup

echo "----------------- FINISH BACKUP $(date +%Y-%m-%d_%H-%M-%S)--------------------"
} 2>&1 | tee -a $log
