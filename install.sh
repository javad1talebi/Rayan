#!/bin/bash
echo "Install Rayan Syrvey"

echo "Rayan Survey Version 1"
sleep 1


echo "َAdd Columns To Asterisk DATABASE"
echo "------------Create DB-----------------"
#echo -n "Enter the MySQL root password: "
#read rootpw

###Fetch DB root PASSWORD
rootpw=$(sed -ne 's/.*mysqlrootpwd=//gp' /etc/issabel.conf)


mysql -uroot -p$rootpw asterisk < Rayandb.sql
echo "DataBase Created Sucsessfully"
sleep 1

echo "------------Delete OLD Folders-----------------"
rm -rf /var/www/html/Rayan_voip
rm -rf /var/lib/asterisk/sounds/complaints

echo "------------Create complaints Folders-----------------"
mkdir -p /var/lib/asterisk/sounds/complaints
chmod -R 777 /var/lib/asterisk/sounds/complaints
chown asterisk:asterisk /var/lib/asterisk/sounds/complaints



echo "Copy Survey Folder and Set Permissions"
echo "------------Copy Files-----------------"
yes | cp -avr Rayan_voip/ /var/www/html/ > /dev/null
chmod -R 777 /var/www/html/Rayan_voip
chmod -R 777 /var/www/html/Rayan_voip/Module
chmod -R 777 /var/www/html/Rayan_voip/pages
chmod -R 777 /var/www/html/Rayan_voip/plugins
chmod -R 777 /var/www/html/Rayan_voip/assets
chmod -R 777 /var/www/html/Rayan_voip/build
echo "Web Files have Moved Sucsessfully"
sleep 1

echo "Copy Uploads Folder"
yes | cp uploads/* /var/lib/asterisk/sounds/custom/ > /dev/null
echo "Voice files copied successfully"


echo "Copy survey.php and Set Permissions"
echo "-------------Copy Files----------------"
yes | cp -rf survey.php /var/lib/asterisk/agi-bin
chmod 777 /var/lib/asterisk/agi-bin/survey.php
chown asterisk:asterisk /var/lib/asterisk/agi-bin/survey.php

yes | cp -rf PlayAgentInfo.php /var/lib/asterisk/agi-bin
chmod 777 /var/lib/asterisk/agi-bin/PlayAgentInfo.php
chown asterisk:asterisk /var/lib/asterisk/agi-bin/PlayAgentInfo.php


yes | cp -rf say.conf /etc/asterisk
chmod 777 /etc/asterisk/say.conf
chown asterisk:asterisk /etc/asterisk/say.conf

yes | cp -rf -avr pr/ /var/lib/asterisk/sounds > /dev/null
chmod -R 777 /var/lib/asterisk/sounds/pr
chown -R asterisk:asterisk /var/lib/asterisk/sounds/pr


echo "Asterisk Files have Moved Sucsessfully"
sleep 1


echo "Add dialplan code to extensions_custom.conf"
echo "-------------Extension Custom----------------"
sed -i '/\[from\-internal\-custom\]/a include \=\> survey' /etc/asterisk/extensions_custom.conf
echo "" >> /etc/asterisk/extensions_custom.conf
cat <<'EOF' >> /etc/asterisk/extensions_custom.conf
[survey]
exten => 4455,1,Set(__AGENT=${CONNECTEDLINE(num)})
exten => 4455,2,AGI(survey.php,${QUEUENUM})
EOF
echo "Dialplans have Set Sucsessfully"
sleep 1

echo "Add QAGI Variable to globals_custom.conf"
echo "-------------Extension Custom----------------"
echo "" >> /etc/asterisk/globals_custom.conf
echo "QAGI=PlayAgentInfo.php" >> /etc/asterisk/globals_custom.conf

echo "-------------Issabel Menu----------------"
issabel-menumerge menu.xml
echo "Issabel Menu is Created Sucsessfully"
sleep 1

echo "-------------Add Apache Alias for Sounds----------------"
cat <<'EOF' >> /etc/httpd/conf/httpd.conf

# Rayan Survey Sounds Alias
Alias /sounds /var/lib/asterisk/sounds
<Directory /var/lib/asterisk/sounds>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
echo "Apache Alias Added Sucsessfully"
sleep 1

echo "-------------Apache Restart----------------"
service httpd restart
echo "Apache has Restarted Sucsessfully"
sleep 1

echo "-------------Reload Asterisk Dialplan----------------"
asterisk -rx "dialplan reload"
echo "Dialplan Reloaded Successfully"
sleep 1

echo "-------------Reload AMPortal----------------"
amportal a reload
echo "AMPortal Reloaded Successfully"
sleep 1

echo "-----------FINISHED (Rayan)-----------"
