#!/usr/bin/env bash

SCRIPT_NAME=${0##*/}

mods=$@
apache_mods="/etc/php/7.3/apache2/conf.d/"
cli_mods="/etc/php/7.3/cli/conf.d/"
priority="20-"



function usage {
    echo "Usage: ${SCRIPT_NAME} module_name [ module_name_2 ]"
    exit 1
}

if [ -z "$mods" ]; then
    usage
fi
for mod in $mods; do
    if [ ! -L "$apache_mods$priority$mod.ini" ]; then
        echo "ERROR: there is no such file - $apache_mods$priority$mod.ini"
        break
    fi
    if [ ! -L "$cli_mods$priority$mod.ini" ]; then
        echo "ERROR: there is no such file - $cli_mods$priority$mod.ini"
        break
    fi
    rm $apache_mods$priority$mod.ini
    rm $cli_mods$priority$mod.ini
    if [ $? -eq 0 ]; then
        echo "SUCCESS: you need to restart apache2 to apply the changes"
        echo "execute \"systemctl restart apache2.service\" in console"
    fi
done
