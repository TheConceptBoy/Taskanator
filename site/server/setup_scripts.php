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

require("db_login.php");


if (isset($_POST["task"])){
    $task = $_POST["task"];

    switch($task){

        case "register_master_user":
        

            $new_master_user_email = $_POST["master_user_email"];
            $new_master_pass = $_POST["master_password"];
            $new_master_fname = $_POST["master_fname"];
            $new_master_lname = $_POST["master_lname"];





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
        
        case "set_properties":
            $property_list = $_POST["property_list"];

            if ($stmt = mysqli_prepare($conn, "UPDATE settings SET value = ? WHERE property = ?")){
                foreach($property_list as $prop){
                    $property_name = $prop[0];
                    $property_value = $prop[1];
                    mysqli_stmt_bind_param($stmt, "ss", $property_value, $property_name);
                    mysqli_stmt_execute($stmt);
                }
                
                if (mysqli_stmt_affected_rows($stmt) == sizeof($property_list)){
                    $json_response -> msg_text = mysqli_stmt_affected_rows($stmt) ."/".sizeof($property_list) ." properties set";
                    $json_response -> msg_code = "settings_set";
                    $json_response -> msg_type = "good";
                }else if (mysqli_stmt_affected_rows($stmt) > 0){
                    $json_response -> msg_text = "Some properties failed to record: " . mysqli_stmt_affected_rows($stmt)."/". sizeOf($property_list);
                    $json_response -> msg_code = "failed_to_record_some_settings";
                    $json_response -> msg_type = "warning";
                }else{
                    $json_response -> msg_text = "Some properties failed to record: " . mysqli_stmt_affected_rows($stmt)."/". sizeOf($property_list);
                    $json_response -> msg_code = "failed_to_record_settings";
                    $json_response -> msg_type = "bad";
                }

                mysqli_stmt_close($stmt);
            }else{
                $json_response -> msg_text = "Failed to initiate property setting ";
                $json_response -> msg_code = "failed_to_record_settings";
                $json_response -> msg_type = "bad";
            }

            exit(json_encode($json_response));

            break;


        case "ip_filter_submit":
            $ip_list = $_POST["ip_list"];


            $success_insert = 0;

            mysqli_query($conn, "TRUNCATE TABLE ip_filter;");

            if($stmt = mysqli_prepare($conn, "INSERT INTO ip_filter (ip, title, description) VALUES (?,?,?)")){
                foreach($ip_list as $ip_array){
                    // $ip_array = $ip_list[0];

                    $ip = $ip_array[0];
                    $title = $ip_array[1];
                    $desc = $ip_array[2];

                    mysqli_stmt_bind_param($stmt, "sss",  $ip, $title, $desc);
                    mysqli_stmt_execute($stmt);
                    if (mysqli_stmt_affected_rows($stmt) > 0){ $success_insert += 1; }
                }
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
        
        case "finish":

            # move the setup page out of the directory]
            rename('./setup.html', './../setup.html');

            $json_response -> msg_text = "Setup Finished. Setup File Disabled.";
            $json_response -> msg_code = "setuo_finished";
            $json_response -> msg_type = "good";
        

            exit(json_encode($json_response));

            break;
        }
}






?>