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

        case "get_todo_content":
            $todo_id = $_POST["todo_id"];
            $board_id = $_POST["board_id"];

            if($stmt = mysqli_prepare($conn, "SELECT columns.id, title FROM columns WHERE columns.todo_id = ? ") ){
                mysqli_stmt_bind_param($stmt, "i", $todo_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);
                mysqli_stmt_bind_result($stmt, $col_id, $col_title);

                $column_array = [];
                while(mysqli_stmt_fetch($stmt)){
                    $cinfo=[];
                    $c_info["col_id"] = $col_id;
                    $c_info["col_title"] = $col_title;

                    // load notes for each column
                    if ($stmt_note = mysqli_prepare($conn, "SELECT id, note_text FROM column_notes WHERE column_id = ?")){
                        mysqli_stmt_bind_param($stmt_note, "i", $col_id);
                        mysqli_stmt_execute($stmt_note);
                        mysqli_stmt_bind_result($stmt_note, $note_id, $note_text);
                        $notes = [];
                        while(mysqli_stmt_fetch($stmt_note)){
                            $note = [];
                            $note["note_id"] = $note_id;
                            $note["note_text"] = $note_text;
                            $notes[] = $note;
                        }
                        $c_info["notes"] = $notes;
                        mysqli_stmt_close($stmt_note);
                    }

                    $column_array[] = $c_info;
                }

                $json_response -> msg_text = "ToDo Content Loaded";
                $json_response -> msg_code = "todo_column_load_success";
                $json_response -> msg_type = "good";
                $json_response -> columns = $column_array;

                exit(json_encode($json_response));


            }else{
                $json_response -> msg_text = "Failed to Fetch ToDo Content";
                $json_response -> msg_code = "failed_todo_load";
                $json_response -> msg_type = "bad";
                $json_response -> boards = $board_array;

                exit(json_encode($json_response));
            }

            break;

        case "create_column":
            $todo_id = $_POST["todo_id"];
            $board_id = $_POST["board_id"];

            if($stmt = mysqli_prepare($conn, "INSERT INTO columns (todo_id) VALUES (?)")){
                mysqli_stmt_bind_param($stmt, "i", $todo_id);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $new_id = mysqli_stmt_insert_id($stmt);
                    mysqli_stmt_close($stmt);

                    $json_response -> msg_text = "Column Created";
                    $json_response -> msg_code = "column_created";
                    $json_response -> msg_type = "good";
                    $json_response -> new_column_id = $new_id;

                }else{
                    $json_response -> msg_text = "Column Creation Failed";
                    $json_response -> msg_code = "column_failed";
                    $json_response -> msg_type = "bad";
                }
                
            }else{
                $json_response -> msg_text = "Column Creation Failed: Can't Prep";
                $json_response -> msg_code = "column_failed_prep";
                $json_response -> msg_type = "bad";
            }

            
            exit(json_encode($json_response));
            break;
        
        case "create_note":
            $col_id = $_POST["col_id"];
            $todo_id = $_POST["todo_id"];
            $board_id = $_POST["board_id"];

            // CHECK BOARD AND COLUMN OWNERSHIP HERE!

            if($stmt = mysqli_prepare($conn, "INSERT INTO column_notes (column_id, note_text) VALUES (?,'New Text')")){
                mysqli_stmt_bind_param($stmt, "i", $col_id);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $new_id = mysqli_stmt_insert_id($stmt);
                    mysqli_stmt_close($stmt);

                    $json_response -> msg_text = "Note Created";
                    $json_response -> msg_code = "note_created";
                    $json_response -> msg_type = "good";
                    $json_response -> new_note_id = $new_id;

                }else{
                    $json_response -> msg_text = "Note Creation Failed";
                    $json_response -> msg_code = "note_failed";
                    $json_response -> msg_type = "bad";
                }
                
            }else{
                $json_response -> msg_text = "Note Creation Failed: Can't Prep";
                $json_response -> msg_code = "note_failed_prep";
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

        case "update_note":
            $note_id = $_POST["note_id"];
            $new_text = $_POST["new_text"];

            if($stmt = mysqli_prepare($conn, "UPDATE column_notes SET note_text = ? WHERE id = ?")){
                mysqli_stmt_bind_param($stmt, "si", $new_text, $note_id);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $json_response -> msg_text = "Note Updated";
                    $json_response -> msg_code = "note_updated";
                    $json_response -> msg_type = "good";

                }else{
                    $json_response -> msg_text = "Note Update Failed";
                    $json_response -> msg_code = "note_update_fail";
                    $json_response -> msg_type = "bad";
                    // $json_response -> data = [$note_id, $new_text];
                }
                mysqli_stmt_close($stmt);
                exit(json_encode($json_response));
            }

            break;

        case "update_column_title":
            $column_id = $_POST["column_id"];
            $new_text = $_POST["new_text"];

            if($stmt = mysqli_prepare($conn, "UPDATE columns SET title = ? WHERE id = ?")){
                mysqli_stmt_bind_param($stmt, "si", $new_text, $column_id);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $json_response -> msg_text = "Column Updated";
                    $json_response -> msg_code = "column_updated";
                    $json_response -> msg_type = "good";

                }else{
                    $json_response -> msg_text = "Column Update Failed";
                    $json_response -> msg_code = "column_update_fail";
                    $json_response -> msg_type = "bad";
                    // $json_response -> data = [$note_id, $new_text];
                }
                mysqli_stmt_close($stmt);
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
            if($stmt = mysqli_prepare($conn, "SELECT graphs.id, graph_name FROM graphs LEFT JOIN boards ON graphs.board_id = boards.id WHERE boards.id = ? ") ){
                mysqli_stmt_bind_param($stmt, "i", $board_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $graph_id, $graph_name);

                $project_graph_lists = [];
                while(mysqli_stmt_fetch($stmt)){
                    $g_info=[];
                    $g_info["graph_id"] = $graph_id;
                    $g_info["graph_name"] = $graph_name;

                    $project_graph_lists[] = $g_info;
                }

                mysqli_stmt_close($stmt);
                
                # record into response
                $json_response -> graphs = $project_graph_lists;
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
        
        case "delete_todo_list":
            $todo_id = $_POST["todo_id"];
            $board_id = $_POST["board_id"];

            // CHECK BOARD ID OWHERSHIP HERE

            if($stmt = mysqli_prepare($conn, "DELETE FROM todo_lists WHERE todo_lists.id = ? AND board_id = ?")){
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
                    // $json_response -> data = [$todo_id,  $board_id];
                }

                exit(json_encode($json_response));
            }

            break;

        case "delete_graph":
            $todo_id = $_POST["todo_id"];
            $board_id = $_POST["board_id"];

            // CHECK BOARD ID OWHERSHIP HERE

            if($stmt = mysqli_prepare($conn, "DELETE FROM graphs WHERE graphs.id = ? AND board_id = ?")){
                mysqli_stmt_bind_param($stmt, "ii", $todo_id, $board_id);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $json_response -> msg_text = "Graph List Deleted";
                    $json_response -> msg_code = "graph_removed";
                    $json_response -> msg_type = "good";

                }else{
                    $json_response -> msg_text = "Failed to Delete Graph";
                    $json_response -> msg_code = "graph_delete_failed";
                    $json_response -> msg_type = "bad";
                    // $json_response -> data = [$todo_id,  $board_id];
                }

                exit(json_encode($json_response));
            }

            break;


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

        case "create_graph":
            $graph_title = $_POST["title"];
            $board_id = $_POST["board_id"];

            if($stmt = mysqli_prepare($conn, "INSERT INTO graphs (graph_name, board_id) VALUES (?, ?)")){
                mysqli_stmt_bind_param($stmt, "si", $graph_title, $board_id);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0){
                    $new_id = mysqli_stmt_insert_id($stmt);
                    mysqli_stmt_close($stmt);

                    $json_response -> msg_text = "Graph List Created";
                    $json_response -> msg_code = "graph_made";
                    $json_response -> msg_type = "good";
                    $json_response -> new_id = $new_id;

                }else{
                    $json_response -> msg_text = "Graph Creation Failed";
                    $json_response -> msg_code = "graph_failed";
                    $json_response -> msg_type = "bad";
                }
            }else{
                $json_response -> msg_text = "Graph Create Failed: Can't Prep";
                $json_response -> msg_code = "graph_failed_prep";
                $json_response -> msg_type = "bad";
            }
            
            exit(json_encode($json_response));
            break;
    }

    
}


?>