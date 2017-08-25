#!/bin/bash

if [ $# -eq 0 ]; then
    echo "A target WP directory is reuired!"
    exit 1
fi

echo
echo "Switching site at '$1' from HTTPS to HTTP."
read -p "Are you sure (y/n)? " -n 1 -r
echo

if [[ $REPLY =~ ^[Yy]$ ]]; then
    curr=$(pwd)
    cd $1

    echo "Switching from HTTPS to HTTP..."
    exec /usr/local/bin/wp option update wordpress-https_ssl_admin 0
    
    site=$(/usr/local/bin/wp option get siteurl)
    fixeds=${site/https/http}
    exec /usr/local/bin/wp option update siteurl $fixeds
    
    home=$(/usr/local/bin/wp option get siteurl)
    fixedh=${home/https/http}
    exec /usr/local/bin/wp option update home $fixedh
    
else
    echo "Canceled"
fi

