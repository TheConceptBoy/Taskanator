<?php

$response = new stdClass();

if (!file_exists("credentials.json")){
    $response -> msg_text = "No Database Credentials found";
    $response -> msg_code = "no_db_cred";
    $response -> msg_type = "bad";
    exit(json_encode($response));
}


?>