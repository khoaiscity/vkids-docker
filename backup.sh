#!/bin/bash

# (1.1) set up backup filename
backup_time=$(date +%Y-%m-%d-%H%M%S)
destination=/home/admin/

# (1.2) set up mysqldump variables
database_server=127.0.0.1
user=root
pass=23F8EMetB3u4wUH4
database=db_vkids_migrate

file="${destination}${database}_${backup_time}.sql"

# (2) in case you run this more than once a day, remove the previous version of the file

# (3) do the mysql database backup (dump)
/usr/bin/mysqldump --opt --protocol=TCP --user=${user} --password=${pass} --host=${database_server} ${database} > ${file}
