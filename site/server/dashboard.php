<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$json_response = new stdClass();

session_start( [ 'cookie_lifetime' => 86400, 'gc_maxlifetime' => 86400 ] );



require("db_login.php");


if ( !isset($_POST["task_type"]) ){
    $json_response -> msg_text = "Failed: Missing Task";
    $json_response -> msg_code = "missing_task";
    $json_response -> msg_type = "bad";
    exit(json_encode($json_response));

}else{

    $task = $_POST["task_type"];
    $user_id = $_SESSION["user_id"];

    switch($task){

        case "get_projects":
            if($stmt = mysqli_prepare($conn, "SELECT boards.id, board_name FROM boards LEFT JOIN ownership ON board_id = boards.id WHERE ownership.user_id = ? ") ){
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $board_id, $board_name);

                $board_array = [];
                while(mysqli_stmt_fetch($stmt)){
                    $b_info=[];
                    $b_info["board_id"] = $board_id;
                    $b_info["board_name"] = $board_name;

                    $board_array[] = $b_info;
                }

                $json_response -> msg_text = "Projects Loaded";
                $json_response -> msg_code = "projects_load_success";
                $json_response -> msg_type = "good";
                $json_response -> boards = $board_array;

                exit(json_encode($json_response));


            }else{
                $json_response -> msg_text = "Failed to Fetch Projects";
                $json_response -> msg_code = "failed_project_load";
                $json_response -> msg_type = "bad";
                $json_response -> boards = $board_array;

                exit(json_encode($json_response));
            }

            break;

        case "create_project":
            $proj_title = $_POST["title"];

            if($stmt = mysqli_prepare($conn, "INSERT INTO boards (board_name) VALUES (?)")){
                mysqli_stmt_bind_param($stmt, "s", $proj_title);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $new_id = mysqli_stmt_insert_id($stmt);
                    mysqli_stmt_close($stmt);

                    # record ownership
                    if($stmt = mysqli_prepare($conn, "INSERT INTO ownership (board_id, user_id ) VALUES (?, ?)")){
                        mysqli_stmt_bind_param($stmt, "ii", $new_id, $user_id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }


                    $json_response -> msg_text = "Project Created";
                    $json_response -> msg_code = "project_made";
                    $json_response -> msg_type = "good";
                    $json_response -> new_project_id = $new_id;

                }else{
                    $json_response -> msg_text = "Project Creation Failed";
                    $json_response -> msg_code = "project_failed";
                    $json_response -> msg_type = "bad";
                }



                
            }else{
                $json_response -> msg_text = "Project Creation Failed: Can't Prep";
                $json_response -> msg_code = "project_failed_prep";
                $json_response -> msg_type = "bad";
            }

            
            exit(json_encode($json_response));
            break;
    }

    
}


?>