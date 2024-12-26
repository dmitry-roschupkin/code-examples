#!/usr/bin/env bash

case $1 in
"disable"   )
    rm /etc/php/7.3/apache2/conf.d/20-xdebug.ini
    rm /etc/php/7.3/cli/conf.d/20-xdebug.ini
    systemctl restart apache2.service
    ;;
"enable"    )
    ln -s /etc/php/mods-available/xdebug.ini /etc/php/7.3/apache2/conf.d/20-xdebug.ini
    ln -s /etc/php/mods-available/xdebug.ini /etc/php/7.3/cli/conf.d/20-xdebug.ini
    systemctl restart apache2.service
    ;;
*       )
    echo "Something wrong! Usage: xdebug enable|disable"
    ;;
esac