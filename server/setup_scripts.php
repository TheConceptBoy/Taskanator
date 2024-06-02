<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$json_response = new stdClass();

if (isset($_POST["task"])){
    $task = $_POST["task"];

    switch($task){
        case "inital_setup":
            $success_count = 0;
            $json_response->messages = [];

            $username = $_POST["username"];
            $password = $_POST["password"];
            $dbname = $_POST["dbname"];

            $conn = mysqli_connect("localhost", $username, $password);
            if(!$conn){
                exit("Could Not connect to mysql");
            }

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
                    mysqli_close($conn);

                }
            }
            


           
            

            // now connect to the database
            $conn = mysqli_connect("localhost", $username, $password, $dbname);
            if(!$conn){
                exit("Could Not connect to mysql");
            }

            
            // create tables
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
            try{
                $result = mysqli_query($conn, "CREATE TABLE boards (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    board_name VARCHAR(128),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'boards' Table Failed to Create";
            }

            // create owneship table
            try {
                $result = mysqli_query($conn, "CREATE TABLE ownership (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    board_id INT(255),
                    user_id INT(255),
                    board_name VARCHAR(128) DEFAULT 'owner',
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'ownership' Table Failed to Create";
            }

            // create columns table
            try {
                $result = mysqli_query($conn, "CREATE TABLE columns (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    board_id INT(128),
                    title VARCHAR(255),
                    column_order INT(255),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'columns' Table Failed to Create";
            }

            // create columns table
            try{
                $result = mysqli_query($conn, "CREATE TABLE notes (
                    id INT(255) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    column_id INT(128),
                    title VARCHAR(255),
                    description VARCHAR(2048),
                    note_color VARCHAR(255),
                    note_text VARCHAR(255),
                    note_order INT(255),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                $success_count += 1;
            } catch(Exception $e){
                $json_response->messages[] = "'notes' Table Failed to Create";
            }


            $credentials = [];
            $credentials["db_username"] = $username;
            $credentials["db_password"] = $password;
            $credentials["db_dbname"] = $dbname;
            $file = fopen("credentials.json", "w");
            fwrite($file, json_encode($credentials));
            fclose($file);

            if ($success_count == 6){
                $json_response -> msg_text = "Initial Creation Successfull";
                $json_response -> msg_code = "initialized";
                $json_response -> msg_type = "good";
            }else{
                $json_response -> msg_text = "Initial Steps Failed";
                $json_response -> msg_code = "failed_to_initialize";
                $json_response -> msg_type = "bad";
            }

            exit(json_encode($json_response));


            // if ($stmt = mysqli_prepare($conn, "CREATE DATABASE ?")){
            //     mysqli_stmt_bind_param($stmt, "s",  $dbname);
            //     mysqli_stmt_execute($stmt);
            //     mysqli_stmt_close($stmt);
            // }

            break;

        case "register_master_user":

            $new_master_user_email = $_POST["master_user_email"];
            $new_master_pass = $_POST["master_password"];
            $new_master_fname = $_POST["master_fname"];
            $new_master_lname = $_POST["master_lname"];

            $file = fopen("credentials.json", "r");
            $dbdata = json_decode(fread($file, filesize("credentials.json")), true);

            // now connect to the database
            $db_username = $dbdata['db_username'];
            $db_pass = $dbdata['db_password'];
            $db_name = $dbdata['db_dbname'];
            
            $conn = mysqli_connect("localhost", $db_username, $db_pass, $db_name);
            if(!$conn){
                exit("Could Not connect to mysql");
            }




            // make sure email is valid formatted
            if (!filter_var($new_master_user_email, FILTER_VALIDATE_EMAIL)) {
                $json_response -> msg_text = "Master User Email is malformed";
                $json_response -> msg_code = "bad_email_formatting";
                $json_response -> msg_type = "bad";
                exit(json_encode( $json_response ));
            }


            // make sure password is "secure"
            $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/'; 
            if (!preg_match($pattern, $new_master_pass)) { 
                $json_response -> msg_text = "Passoword Insuffecient";
                $json_response -> msg_code = "bad_password";
                $json_response -> msg_type = "bad";
                exit(json_encode( $json_response ));
            }

            // make sure no other master user exists already
            if($stmt = mysqli_prepare($conn, "SELECT id FROM userbase WHERE user_type = 'master' ")){
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $json_response -> msg_text = "Master User already registered";
                    $json_response -> msg_code = "existing_master_user";
                    $json_response -> msg_type = "bad";
                    exit(json_encode( $json_response ));
                }
            }
            mysqli_stmt_close($stmt);



            // make sure a user with this email does not already exist
            if($stmt = mysqli_prepare($conn, "SELECT id FROM userbase WHERE email = ? ")){
                mysqli_stmt_bind_param($stmt, "s", $new_master_user_email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $json_response -> msg_text = "User with this email already registered";
                    $json_response -> msg_code = "email_duplicate_user";
                    $json_response -> msg_type = "bad";
                    exit(json_encode( $json_response ));
                }
            }
            mysqli_stmt_close($stmt);



            // make a record of the user into the userbase database
            if ($stmt = mysqli_prepare($conn, "INSERT INTO userbase (email, password, fname, lname, user_type) VALUES (?, ?, ?, ?, 'master')")){
               $hashed_password = password_hash($new_master_pass, PASSWORD_DEFAULT);
               mysqli_stmt_bind_param($stmt, "ssss", $new_master_user_email, $hashed_password, $new_master_fname, $new_master_lname);
               mysqli_stmt_execute($stmt);
               if (mysqli_stmt_affected_rows($stmt) > 0){
                $json_response -> msg_text = "Master User Recorded";
                $json_response -> msg_code = "user_registered";
                $json_response -> msg_type = "good";
               }else{
                $json_response -> msg_text = "Failed to record master user";
                $json_response -> msg_code = "user_registration_failed";
                $json_response -> msg_type = "bad";
               }
            } else{
                $json_response -> msg_text = "Failed to prep master user record";
                $json_response -> msg_code = "user_registration_prep_failed";
                $json_response -> msg_type = "bad";
            }

            exit(json_encode( $json_response ));


            break;
    }
}






?>