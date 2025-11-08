<?php
    $json_login_respionse = new stdClass();

    
    $file = fopen("../../credentials.json", "r");
    $dbdata = json_decode(fread($file, filesize("../../credentials.json")), true);
    fclose($file);

    // now connect to the database
    $db_username = $dbdata['db_username'];
    $db_pass = $dbdata['db_password'];
    $db_name = $dbdata['db_dbname'];
    
    $conn = mysqli_connect("localhost", $db_username, $db_pass, $db_name);
    if(!$conn){
        $json_login_respionse -> msg_text = $success_insert." ip exceptions recorded";
        $json_login_respionse -> msg_code = "ip_recorded";
        $json_login_respionse -> msg_type = "good";
        exit(json_encode($json_login_respionse));
    }

?>