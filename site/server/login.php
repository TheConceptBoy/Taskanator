<?php

require("db_login.php");



$json_response = new stdClass();

if (isset($_POST["task_type"])){

    $task_type = $_POST["task_type"];

    switch($task_type){
        case "login":
            
            $email = $_POST["email"];
            $pass = $_POST["password"];
            
            $user_stored_password = null;

            if($stmt = mysqli_prepare($conn, "SELECT id, fname, lname, password FROM userbase WHERE email = ?")){
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $user_id, $user_fname, $user_lname, $user_stored_password);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
                
                # account found
                if ($user_stored_password != null){
                    if ( password_verify($pass, $user_stored_password) ){
                        $json_response -> msg_text = "Login Success.";
                        $json_response -> msg_code = "login_success";
                        $json_response -> msg_type = "good";

                        session_start( [ 'cookie_lifetime' => 86400, 'gc_maxlifetime' => 86400 ] );
                        $_SESSION["user_id"] = $user_id;
                        $_SESSION["user_fname"] = $user_fname;
                        $_SESSION["user_lname"] = $user_lname;

                    }else{
                        $json_response -> msg_text = "Login Failed";
                        $json_response -> msg_code = "login_failed";
                        $json_response -> msg_type = "bad";
                    }
                }else{
                    $json_response -> msg_text = "Login Failed: Account Not found";
                    $json_response -> msg_code = "login_failed_no_account";
                    $json_response -> msg_type = "bad";
                }

            }else{
                $json_response -> msg_text = "Login Failed: Could not Prepare";
                $json_response -> msg_code = "login_failed_no_prep";
                $json_response -> msg_type = "bad";
            }
            break;

    }

    exit(json_encode($json_response));

    
} else{
    $json_response -> msg_text = "Login Failed: No Task Type";
    $json_response -> msg_code = "login_failed_no_task_type";
    $json_response -> msg_type = "bad";
    exit(json_encode($json_response));
}

?>