<?php
    $continue = 0; 
    $handle = fopen("../go-space/conf/app.ini", "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
// echo "<LI>Line : $line";

            if (trim($line) == "[database]") {
                $continue = 1; 
            }

            if ($continue == 0) {
                continue;
            }

            $data = explode("=", $line);

            if (trim($data[0]) == "Host" && !$DB_HOST) {
                $DB_HOST = trim($data[1]);
// echo "<LI>DB_HOST - $DB_HOST";                
            }elseif (trim($data[0]) == "DB_PORT" && !$DB_PORT) {
                $DB_PORT = trim($data[1]);
// echo "<LI>DB_PORT - $DB_PORT";                
            }elseif (trim($data[0]) == "Name" && !$DB_DATABASE) {
                $DB_DATABASE = trim($data[1]);
// echo "<LI>DB_DATABASE - $DB_DATABASE";                
            }elseif (trim($data[0]) == "User" && !$DB_USERNAME) {
                $DB_USERNAME = trim($data[1]);
// echo "<LI>DB_USERNAME - $DB_USERNAME";                                
            }elseif (trim($data[0]) == "Password" && !$DB_PASSWORD) {
                $DB_PASSWORD = trim($data[1]);
// echo "<LI>DB_PASSWORD - $DB_PASSWORD";                
            }elseif (trim($data[0]) == "APP_LOCAL_URL") {
                $APP_LOCAL_URL = trim($data[1]);
            }elseif (trim($data[0]) == "APP_ENV") {
                $APP_ENV = trim($data[1]);
            }
        }
        fclose($handle);

    } else {
            echo "<LI>Error";
    } 

    $DBLink = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
    
    
    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    mysqli_query($DBLink, "SET NAMES 'utf8'");    
?>