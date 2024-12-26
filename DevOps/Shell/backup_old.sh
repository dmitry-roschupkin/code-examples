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

function backup_full {
    echo "removing old data from local directory"
    rm -R $localPath/backup/last/*
    rm -R $localPath/backup/inc/*
	case $COMPRESSION in
		'tpgz')
			echo 'using tpgzCompression'
            innobackupex --extra-lsndir=$lsndir --user=$MYSQL_USER --pass=$MYSQL_PASS --parallel=$PARALLEL --stream=tar ./ | pigz - > $lastPath/$filename.tar.gz
            if [ $PREF == $localPath ];then
    	        cp $lastPath/$filename.tar.gz /mnt/backup/last
            fi
		;;
		'xbstream')
			echo 'using xbstream'
			innobackupex --extra-lsndir=$lsndir --user=$MYSQL_USER --pass=$MYSQL_PASS --stream=xbstream --compress --compress-threads=$PARALLEL ./ > $lastPath/$filename.xbstream
            if [ $PREF == $localPath ];then
		        cp $lastPath/$filename.xbstream /mnt/backup/last
            fi
		;;
		*)
			echo "full backup with no compression"
			innobackupex --extra-lsndir=$lsndir --user=$MYSQL_USER --pass=$MYSQL_PASS --parallel=$PARALLEL --no-timestamp $lastPath/$filename
            if [ $PREF == $localPath ];then
		        cp -r $lastPath/$filename /mnt/backup/last
            fi
	esac
}

function backup_inc {
	lsn=`ssh remote@backup.project.com "cat /var/ftp/backup-production/db/xtrabackup_checkpoints" | grep "to_lsn" $lsnfile  | awk {'print $3'}`
	if [ -n "`echo $lsn | sed 's/[0-9]//g'`" ];then
        echo "Wrong LSN or it doesn't exist, exiting now"
        exit
	else
		case $COMPRESSION in
			'xbstream')
				echo 'using xbstream'
				innobackupex --databases="CALC GENERAL" --extra-lsndir=$lsndir --user=$MYSQL_USER --password=$MYSQL_PASS --no-lock --parallel=$PARALLEL --incremental --incremental-lsn=$lsn --stream=xbstream --compress --compress-threads=$PARALLEL ./ > $incPath/$filename.xbstream
                if [ $PREF == $localPath ];then
			    cp -r $incPath/$filename.xbstream /mnt/backup/inc
                fi
			;;
			*)
				echo "using no compression"
				innobackupex --databases="CALC GENERAL" --extra-lsndir=$lsndir --user=$MYSQL_USER --password=$MYSQL_PASS --no-lock --parallel=$PARALLEL --incremental --incremental-lsn=$lsn --no-timestamp $incPath/$filename
                if [ $PREF == "$localPath" ];then
		        cp -r $incPath/$filename /mnt/backup/inc
                fi
		esac
	fi
}

function mount_backup {
    echo "Checking if backup share already mounted"
    mountStatus=$(mount -l | grep "/mnt/backup" | grep -c "backup.project.com")
    if [ "$mountStatus" -ne 0 ];then
	 echo "Backup share already mounted"
    else
        sshfs -o IdentityFile=/root/.ssh/nopass_rsa remote@backup.project.com:/var/ftp/backup-production/db /mnt/backup
        # Checking if mount was success
        if [ $? -eq 0 ]; then
           echo "Mount success! ----------------------------------------"
        else
           echo "[ERROR]Something went wrong with the mount..., exiting..."
           exit 1
        fi
    fi
}

{

ulimit -n 16384

if [ ! -f /var/run/backup.lock ] && ! pgrep -f innobackupex ;then
    date > /var/run/backup.lock
else
    echo " $(date +%Y-%m-%d_%H-%M-%S) Backup script already running, exiting!"
    exit 0
fi

echo " Mounting backup directory, located on backup server"
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
			echo "entered argument is incorrect, exiting now"
			exit
esac

cp $lsnfile /mnt/backup

#Removing lock file
rm /var/run/backup.lock
umount /mnt/backup

echo "----------------- FINISH BACKUP $(date +%Y-%m-%d_%H-%M-%S)--------------------"
} 2>&1 | tee -a $log
