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
auth_file="${destination}authenticate_${backup_time}.json"
curl -d "client_id=111571658651-psq6kb2ncjftmf244q7o9ine6hu5clg6.apps.googleusercontent.com&scope=https://www.googleapis.com/auth/drive.file" https://oauth2.googleapis.com/device/code >> $auth_file

# (6') required jq
required_jq="jq"
required_jq_ok=$(dpkg-query -W --showformat='${Status}\n' required_jq|grep "install ok installed")
echo Checking for $required_jq: $required_jq_ok

# (6) set up mysqldump variables
device_code=${/usr/bin/jq '.device_code' $auth_file}
user_code=${/usr/bin/jq '.user_code' $auth_file}
verification_url=${/usr/bin/jq '.verification_url' $auth_file}
