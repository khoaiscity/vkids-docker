#!/bin/bash

# (1.1) set up backup filename
backup_time=$(date +%Y-%m-%d-%H%M%S)
destination=/home/admin/vkids/database/

# (1.2) set up mysqldump variables
database_server=127.0.0.1
user=root
pass=23F8EMetB3u4wUH4
database=db_vkids_migrate

file="${destination}${database}_${backup_time}.sql"

# (2) in case you run this more than once a day, remove the previous version of the file

# -p, --parents no error if existing, make parent directories as needed
mkdir -p $destination
# (3) do the mysql database backup (dump)
/usr/bin/mysqldump --opt --protocol=TCP --user=${user} --password=${pass} --host=${database_server} ${database} > ${file}

# (4) gzip the mysql database dump file
gzip $file

# (5) auth
client_id="111571658651-pub8irvb4cloj7nh57pnvo0a1jp7beqq.apps.googleusercontent.com"
verify_string="client_id=${client_id}&scope=https://www.googleapis.com/auth/drive.file"
auth_file="${destination}authenticate_${backup_time}.json"
curl -d $verify_string https://oauth2.googleapis.com/device/code >> $auth_file

# (6') required jq
required_jq="jq"
required_jq_ok=$(dpkg-query -W --showformat='${Status}\n' $required_jq|grep "install ok installed")
echo Checking for $required_jq: $required_jq_ok

# (6) set up mysqldump variables
device_code=$(/usr/bin/jq -r '.device_code' $auth_file)
user_code=$(/usr/bin/jq -r '.user_code' $auth_file)
verification_url=$(/usr/bin/jq -r '.verification_url' $auth_file)

echo $device_code
echo $user_code
echo $verification_url

access_file="${destination}access_${backup_time}.json"
client_secret="yeV6dHoeV7i7Mf-G7nRqVHWE"
curl -d client_id=$client_id -d client_secret=$client_secret -d device_code=$device_code -d grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Adevice_code https://accounts.google.com/o/oauth2/token >> $access_file

#
# rm $auth_file
# rm $access_file
