<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$json_response = new stdClass();


// only allow methods if setup.html is present in root
if (!file_exists("./setup.html")){
    $json_response -> msg_text = "Setup Already Finished. No Operation";
    $json_response -> msg_code = "no_setup";
    $json_response -> msg_type = "bad";
}


if (isset($_POST["task"])){
    $task = $_POST["task"];

    switch($task){
        case "inital_setup":
            $operation_count = 0;
            $success_count = 0;
            $json_response->messages = [];

            $username = $_POST["username"];
            $password = $_POST["password"];
            $dbname = $_POST["dbname"];

            $conn = mysqli_connect("localhost", $username, $password);
            if(!$conn){
                exit("Could Not connect to mysql");
            }

            $operation_count += 1;
            if ($stmt = mysqli_prepare($conn, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?" )){
                mysqli_stmt_bind_param($stmt, "s", $dbname);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) > 0){
                    $json_response->messages[] = "Database with this name is Already Present";
                }else{
                     // create database
                    $result = mysqli_query($conn, "CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    if ($result){$success_count += 1;}else{$json_response->messages[] = "Database Failed to Create";}
                    mysqli_stmt_close($stmt);
                    mysqli_close($conn);


                    # re-stablish connection now with a database
                    $conn = mysqli_connect("localhost", $username, $password, $dbname);
                    if(!$conn){
                        exit("Could Not connect to mysql");
                    }

                }
            }


            

           
            
            // create tables
            $operation_count += 1;
            try{
                $result = mysqli_query($conn, "CREATE TABLE userbase (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(128),
                    fname VARCHAR(255),
                    lname VARCHAR(255),
                    user_type VARCHAR(60) DEFAULT 'user', 
                    password VARCHAR(255),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");

                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'userbase' Table Failed to Create";
            }


            // create board
            $operation_count += 1;
            try{
                $result = mysqli_query($conn, "CREATE TABLE boards (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    board_name VARCHAR(128),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'boards' Table Failed to Create";
            }

            // create todo list table
            $operation_count += 1;
            try{
                $result = mysqli_query($conn, "CREATE TABLE todo_lists (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    list_name VARCHAR(128),
                    board_id int(255),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'boards' Table Failed to Create";
            }

            // create columns table
            $operation_count += 1;
            try {
                $result = mysqli_query($conn, "CREATE TABLE columns (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    board_id INT(128),
                    title VARCHAR(255),
                    column_order INT(255),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'columns' Table Failed to Create";
            }

            // create columns table
            $operation_count += 1;
            try {
                $result = mysqli_query($conn, "CREATE TABLE column_notes (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    column_id INT(128),
                    note_text TEXT,
                    note_order INT(255),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'columns' Table Failed to Create";
            }


            // create graphs list table
            $operation_count += 1;
            try{
                $result = mysqli_query($conn, "CREATE TABLE graphs (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    graph_name VARCHAR(128),
                    board_id int(255),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'boards' Table Failed to Create";
            }

            // create owneship table
            $operation_count += 1;
            try {
                $result = mysqli_query($conn, "CREATE TABLE ownership (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    board_id INT(255),
                    user_id INT(255),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'ownership' Table Failed to Create";
            }

            

            // create notes table
            $operation_count += 1;
            try{
                $result = mysqli_query($conn, "CREATE TABLE notes (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    column_id INT(128),
                    title VARCHAR(255),
                    description VARCHAR(2048),
                    note_color VARCHAR(255),
                    note_text VARCHAR(255),
                    note_order INT(255),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    update_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'notes' Table Failed to Create";
            }

            // create ip filter table
            $operation_count += 1;
            try{
                $result = mysqli_query($conn, "CREATE TABLE ip_filter (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    ip VARCHAR(32),
                    title VARCHAR(255),
                    description VARCHAR(2048)
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'ip_filter' Table Failed to Create";
            }

            // create settings filter table
            $operation_count += 1;
            try{
                $result = mysqli_query($conn, "CREATE TABLE settings (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    property VARCHAR(64),
                    value VARCHAR(64)
                )");

                // loop through to set up the following properties
                $properties = [
                    ["ip_filter", false]
                ];
                

                if ($stmt = mysqli_prepare($conn, "INSERT INTO settings (property, value) VALUE (?, ?)")){
                    foreach($properties as $p){
                        $property_name = $p[0];
                        $property_value = $p[1];
                        mysqli_stmt_bind_param($stmt, "si", $property_name, $property_value );
                        mysqli_stmt_execute($stmt);
                    }
                }

                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'settings' Table Failed to Create";
            }


            $credentials = [];
            $credentials["db_username"] = $username;
            $credentials["db_password"] = $password;
            $credentials["db_dbname"] = $dbname;
            $file = fopen("../../credentials.json", "w");
            fwrite($file, json_encode($credentials));
            fclose($file);

            if ($success_count == $operation_count){
                $json_response -> msg_text = "Database and Tables Created Successfully";
                $json_response -> msg_code = "initialized";
                $json_response -> msg_type = "good";
            }else{
                $compount_errors = "";
                foreach($json_response->messages as $msg){
                    $compount_errors .= $msg . "<br>";
                }

                $json_response -> msg_text = "Initial Steps Failed: <br>" . $compount_errors;
                $json_response -> msg_code = "failed_to_initialize";
                $json_response -> msg_type = "bad";
                $json_response -> steps_completed = $success_count . "/" . $operation_count;

            }

            exit(json_encode($json_response));


            // if ($stmt = mysqli_prepare($conn, "CREATE DATABASE ?")){
            //     mysqli_stmt_bind_param($stmt, "s",  $dbname);
            //     mysqli_stmt_execute($stmt);
            //     mysqli_stmt_close($stmt);
            // }

            break;


            $ip_list = $_POST["ip_list"];


            $success_insert = 0;

            if($stmt = mysqli_prepare($conn, "INSERT INTO ip_filter (ip, title, description) VALUES (?,?,?)")){
                // foreach($ip_list as $ip_array){
                    $ip_array = $ip_list[0];

                    $ip = $ip_array[0];
                    $title = $ip_array[1];
                    $desc = $ip_array[2];

                    mysqli_stmt_bind_param($stmt, "sss",  $ip, $title, $desc);
                    mysqli_stmt_execute($stmt);
                    if (mysqli_stmt_affected_rows($stmt) > 0){ $success_insert += 1; }
                // }
            }
            mysqli_stmt_close($stmt);

            
            if ($success_insert == sizeOf($ip_list)){
                $json_response -> msg_text = $success_insert." ip exceptions recorded";
                $json_response -> msg_code = "ip_recorded";
                $json_response -> msg_type = "good";
            }else{
                $json_response -> msg_text = "Some ips failed to record: " . $success_insert."/". sizeOf($ip_list);
                $json_response -> msg_code = "failed_to_record_ips";
                $json_response -> msg_type = "bad";
            }

            exit(json_encode($json_response));

            break;
    }
}






?>