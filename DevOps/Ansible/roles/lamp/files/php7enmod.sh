#!/usr/bin/env bash

SCRIPT_NAME=${0##*/}

mods=$@
apache_mods="/etc/php/7.3/apache2/conf.d/"
cli_mods="/etc/php/7.3/cli/conf.d/"
mods_available="/etc/php/7.3/mods-available/"
priority="20-"



function usage {
    echo "Usage: ${SCRIPT_NAME} module_name [ module_name_2 ]"
    exit 1
}

if [ -z "$mods" ]; then
    usage
fi
for mod in $mods; do
    if [ ! -f "$mods_available$mod.ini" ]; then
        echo "ERROR: there is no such file - $mods_available$mod.ini"
        break
    fi
    if [ -L "$apache_mods$priority$mod.ini" ]
    then
        echo "module is already enabled - $mod"
    else
        ln -s $mods_available$mod.ini $apache_mods$priority$mod.ini
        ln -s $mods_available$mod.ini $cli_mods$priority$mod.ini
        if [ $? -eq 0 ]; then
            echo "SUCCESS: you need to restart apache2 to apply the changes"
            echo "execute \"systemctl restart apache2.service\" in console"
        fi
    fi
done
