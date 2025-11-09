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

        case "load_project_content":
            $success_count = 0;
            $project_graphs = [];
            $board_id = $_POST["board_id"];

            // check ownership HERE

            // get todo lists
            if($stmt = mysqli_prepare($conn, "SELECT todo_lists.id, list_name FROM todo_lists LEFT JOIN boards ON todo_lists.board_id = boards.id WHERE boards.id = ? ") ){
                mysqli_stmt_bind_param($stmt, "i", $board_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $list_id, $list_name);

                $project_todo_lists = [];
                while(mysqli_stmt_fetch($stmt)){
                    $l_info=[];
                    $l_info["todo_list_id"] = $list_id;
                    $l_info["todo_list_name"] = $list_name;

                    $project_todo_lists[] = $l_info;
                }

                mysqli_stmt_close($stmt);
                
                # record into response
                $json_response -> todo_lists = $project_todo_lists;
                $success_count += 1;

            }

            // get graphs
            if($stmt = mysqli_prepare($conn, "SELECT graph_lists.id, graph_name FROM graph_lists LEFT JOIN boards ON graph_lists.board_id = boards.id WHERE boards.id = ? ") ){
                mysqli_stmt_bind_param($stmt, "i", $board_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $graph_id, $graph_name);

                $project_graph_lists = [];
                while(mysqli_stmt_fetch($stmt)){
                    $g_info=[];
                    $g_info["board_id"] = $graph_id;
                    $g_info["board_name"] = $graph_name;

                    $project_graph_lists[] = $g_info;
                }

                mysqli_stmt_close($stmt);
                
                # record into response
                $json_response -> graph_lists = $project_graph_lists;
                $success_count += 1;

            }

            if ($success_count == 2){
                $json_response -> msg_text = "Projects Content Loaded";
                $json_response -> msg_code = "project_load_success";
                $json_response -> msg_type = "good";
            }
            else if($success_count == 1){
                $json_response -> msg_text = "Some Projects Content Failed to Load";
                $json_response -> msg_code = "project_load_success";
                $json_response -> msg_type = "warning";
            }else{
                $json_response -> msg_text = "Failed to Fetch Projects";
                $json_response -> msg_code = "failed_project_load";
                $json_response -> msg_type = "bad";

            }

            exit(json_encode($json_response));

            break;

        case "create_project":
            
        case "create_todo_list":
            $proj_title = $_POST["title"];
            $board_id = $_POST["board_id"];

            if($stmt = mysqli_prepare($conn, "INSERT INTO todo_lists (list_name, board_id) VALUES (?, ?)")){
                mysqli_stmt_bind_param($stmt, "si", $proj_title, $board_id);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $new_id = mysqli_stmt_insert_id($stmt);
                    mysqli_stmt_close($stmt);

                    $json_response -> msg_text = "ToDo List Created";
                    $json_response -> msg_code = "todo_made";
                    $json_response -> msg_type = "good";
                    $json_response -> new_id = $new_id;

                }else{
                    $json_response -> msg_text = "ToDo list Creation Failed";
                    $json_response -> msg_code = "todo_failed";
                    $json_response -> msg_type = "bad";
                }



                
            }else{
                $json_response -> msg_text = "ToDo List Create Failed: Can't Prep";
                $json_response -> msg_code = "todo_failed_prep";
                $json_response -> msg_type = "bad";
            }

            
            exit(json_encode($json_response));
            break;

        case "delete_board":
            $board_id = $_POST["board_id"];
            if($stmt = mysqli_prepare($conn, "DELETE FROM boards WHERE id = ?")){
                mysqli_stmt_bind_param($stmt, "i", $board_id);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $json_response -> msg_text = "Project Deleted";
                    $json_response -> msg_code = "project_removed";
                    $json_response -> msg_type = "good";

                }else{
                    $json_response -> msg_text = "Failed to Delete Project";
                    $json_response -> msg_code = "project_delete_failed";
                    $json_response -> msg_type = "bad";
                }

                exit(json_encode($json_response));
            }

            break;
        case "delete_todo_list":
            $todo_id = $_POST["todo_id"];
            $board_id = $_POST["board_id"];

            if($stmt = mysqli_prepare($conn, "DELETE FROM graph_list LEFT JOIN boards ON graph_list.board_id = boards.id WHERE graph_list.id = ? AND boards.id = ?")){
                mysqli_stmt_bind_param($stmt, "ii", $todo_id, $board_id);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $json_response -> msg_text = "Todo List Deleted";
                    $json_response -> msg_code = "todo_removed";
                    $json_response -> msg_type = "good";

                }else{
                    $json_response -> msg_text = "Failed to Delete Todo List";
                    $json_response -> msg_code = "todo_delete_failed";
                    $json_response -> msg_type = "bad";
                }

                exit(json_encode($json_response));
            }

            break;
    }

    
}


?>