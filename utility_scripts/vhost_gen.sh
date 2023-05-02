#!/bin/bash
# This script generates VirtualHost directive files for the API and dashboard.
# If you need anything more than the bare minimum setup, you'll probably need something more elaborate.

read -p "What is the base url (ie thinkhealthymedium.com)? " baseurl

read -p "What is the api subdomain? " apisub

read -p "What is the dashboard subdomain? " dashsub

read -p "What is the bath to the root application directory (ie /var/vhosts/arc-core)? " baseapp

read -p "Would you like to create http (port 80) as well? (y or n) " createhttp


apiurl="$apisub.$baseurl"
dashurl="$dashsub.$baseurl"

apiapp="$baseapp/frontend/web"
dashapp="$baseapp/backend/web"

echo -e "\n\nCreating VirtualHost directives:\n\nAPI:\n- ServerName: $apiurl\n- DocumentRoot: $apiapp\n\nDashboard:\n- ServerName: $dashurl\n- DocumentRoot: $dashapp"

if [ $createhttp == "y" ]; then
	echo -e '\nAlso creating http directives.\n'
fi

conffile="$dashurl.conf"

read -p "Confirm creating file $conffile ? (y or n) " confirmcreate

if [ $confirmcreate == "y" ]; then
echo "Writing to $conffile..."

cat > $conffile <<-EOF

# Custom environment variable that masks the client IP address.
SetEnvIf Remote_Addr "((?:\d{1,3}\.){3})\d{1,3}" MASKED_IP_ADDR=$1XXX


<IfModule log_config_module>
    LogFormat "%{MASKED_IP_ADDR}e %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"" redacted
</IfModule>
# $dashsub Dashboard
<VirtualHost *:443>
        ServerName $dashurl
        DocumentRoot $dashapp

        <Directory $dashapp >
                Options -Indexes +FollowSymLinks -MultiViews
		AllowOverride All
		Require all granted
        </Directory>
		ErrorLogFormat "[%{u}t] [%-m:%l] [pid %P:tid %T] %7F: %E: [client\ %{MASKED_IP_ADDR}e] %M% ,\ referer\ %{Referer}i"
        CustomLog /var/log/httpd/$dashsub-access.log redacted
        ErrorLog /var/log/httpd/$dashsub-error.log

        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel error

</VirtualHost>

# $apisub API
<VirtualHost *:443>
        ServerName $apiurl
        DocumentRoot $apiapp

        <Directory $apiapp >
                Options -Indexes +FollowSymLinks -MultiViews
                AllowOverride All
                Require all granted
        </Directory>
		ErrorLogFormat "[%{u}t] [%-m:%l] [pid %P:tid %T] %7F: %E: [client\ %{MASKED_IP_ADDR}e] %M% ,\ referer\ %{Referer}i"
        CustomLog /var/log/httpd/$apisub-access.log redacted
        ErrorLog /var/log/httpd/$apisub-error.log

        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel debug
</VirtualHost>

EOF

if [ $createhttp == "y" ]; then
	

cat >> $conffile <<-EOF
# And non-ssl versions, because you can't redirect POST requests

<VirtualHost *:80>
        ServerName $dashurl
        DocumentRoot $dashapp

        <Directory $dashapp >
                Options -Indexes +FollowSymLinks -MultiViews
		AllowOverride All
		Require all granted
        </Directory>
		ErrorLogFormat "[%{u}t] [%-m:%l] [pid %P:tid %T] %7F: %E: [client\ %{MASKED_IP_ADDR}e] %M% ,\ referer\ %{Referer}i"
        CustomLog /var/log/httpd/$dashsub-access.log redacted
        ErrorLog /var/log/httpd/$dashsub-error.log

        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel error

</VirtualHost>

<VirtualHost *:80>
        ServerName $apiurl
        DocumentRoot $apiapp

        <Directory $apiapp >
                Options -Indexes +FollowSymLinks -MultiViews
                AllowOverride All
                Require all granted
        </Directory>
		ErrorLogFormat "[%{u}t] [%-m:%l] [pid %P:tid %T] %7F: %E: [client\ %{MASKED_IP_ADDR}e] %M% ,\ referer\ %{Referer}i"
        CustomLog /var/log/httpd/$apisub-access.log redacted
        ErrorLog /var/log/httpd/$apisub-error.log

        # Possible values include: debug, info, notice, warn, error, crit,
        # alert, emerg.
        LogLevel debug
</VirtualHost>

EOF

echo -e "Done!\n"
fi

else
	echo -e "Canceled file creation."
fi