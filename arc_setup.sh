#!/bin/bash
echo -e "This script will perform several operations:\n- downloading Composer\n- installing dependencies\n- Running Yii initialization\n- And setting file permissions."
read -p "Continue? (y or n) " con

if [ $con != "y" ]; then
	echo "Exiting!"
	exit 0
fi

# 1: download composer, 
echo -e 'Downloading Composer...\n'

EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)"
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
then
    >&2 echo 'ERROR: Invalid installer signature'
    rm composer-setup.php
    exit 1
fi

php composer-setup.php
rm composer-setup.php

# 2: install dependencies

echo -e 'Installing dependencies...\n'

php composer.phar install

# 3: initializing Yii
echo -e 'Initializing Yii...\n'

./init 

# 14: set permissions

read -p "What group does apache run as? (default is usually apache) " apachegroup
read -p "Really apply permissions for $apachegroup ? (y or n) " yorn

if [ $yorn == "y" ]; then
echo -e "Applying permissions for $apachegroup...\n"

chgrp -R $apachegroup .
chmod -R g+w .
chmod g-w .git/objects/pack/*
find -type d -exec chmod g+s {} +

else

echo "Permissions cancelled. Please apply them manually."

fi

cat <<-EOF
Basic setup is complete, but there's still several things you probably need to do. 
Refer to the README.md for a more comprehensive list, but generally:

- Setup VirtualHost directives for the api and dashboard
- Setup a database and database user
- Run RBAC migrations
- Run our migrations
- Set local parameters
EOF

