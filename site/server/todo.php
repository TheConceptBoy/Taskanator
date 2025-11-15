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

            if($stmt = mysqli_prepare($conn, "SELECT columns.id, title FROM columns WHERE columns.todo_id = ? ORDER BY column_order ASC ") ){
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
                    if ($stmt_note = mysqli_prepare($conn, "SELECT id, note_text, checked FROM notes WHERE column_id = ? ORDER BY note_order ASC")){
                        mysqli_stmt_bind_param($stmt_note, "i", $col_id);
                        mysqli_stmt_execute($stmt_note);
                        mysqli_stmt_bind_result($stmt_note, $note_id, $note_text, $checked);
                        $notes = [];
                        while(mysqli_stmt_fetch($stmt_note)){
                            $note = [];
                            $note["note_id"] = $note_id;
                            $note["note_text"] = $note_text;
                            $note["note_checked"] = $checked;
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
            $board_id = $_POST["board_id"];

            // CHECK BOARD AND COLUMN OWNERSHIP HERE!

            if($stmt = mysqli_prepare($conn, "INSERT INTO notes (column_id, note_text) VALUES (?,'New Text')")){
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

            if($stmt = mysqli_prepare($conn, "UPDATE notes SET note_text = ? WHERE id = ?")){
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

        case "set_note_order":
            $note_order_list = $_POST["note_order"];
            $board_id = $_POST["board_id"];

            if($stmt = mysqli_prepare($conn, "UPDATE notes SET note_order = ? WHERE id = ?")){
                
                mysqli_stmt_bind_param($stmt, "ii", $note_order, $note_id);
                $processed = 0;
                foreach($note_order_list as $note){
                    $note_id = $note[1];
                    $note_order = $note[0];
                    if (mysqli_stmt_execute($stmt)){
                        $processed += 1;
                    }
                }

                if ($processed > 0){
                    $json_response -> msg_text = "Note Order Updated";
                    $json_response -> msg_code = "note_order_updated";
                    $json_response -> msg_type = "good";
                    $json_response -> num = $processed;

                }else{
                    $json_response -> msg_text = "Note Order Update Failed";
                    $json_response -> msg_code = "note_order_update_fail";
                    $json_response -> msg_type = "bad";
                    $json_response -> error = mysqli_error($conn);
                    $json_response -> num = $processed;
                    // $json_response -> data = [$note_id, $new_text];
                }
                mysqli_stmt_close($stmt);
                exit(json_encode($json_response));
            }

            break;


        case "set_column_order":
            $order_list = $_POST["order"];
            $board_id = $_POST["board_id"];

            if($stmt = mysqli_prepare($conn, "UPDATE columns SET column_order = ? WHERE columns.id = ?")){
                
                mysqli_stmt_bind_param($stmt, "ii", $order, $id);
                $processed = 0;
                foreach($order_list as $column){
                    $id = $column[1];
                    $order = $column[0];
                    if (mysqli_stmt_execute($stmt)){ $processed += 1; }
                }

                if ($processed > 0){
                    $json_response -> msg_text = "Column Order Updated";
                    $json_response -> msg_code = "column_order_updated";
                    $json_response -> msg_type = "good";
                    $json_response -> num = $processed;

                }else{
                    $json_response -> msg_text = "Column Order Update Failed";
                    $json_response -> msg_code = "column_order_update_fail";
                    $json_response -> msg_type = "bad";
                    $json_response -> error = mysqli_error($conn);
                    $json_response -> num = $processed;
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
            
        case "delete_column":
            $column_id = $_POST["column_id"];

            // check ownership HERE

            // get todo lists
            if($stmt = mysqli_prepare($conn, "DELETE FROM columns WHERE id = ? ") ){
                mysqli_stmt_bind_param($stmt, "i", $column_id);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0){}
                    $json_response -> msg_text = "Column Deleted";
                    $json_response -> msg_code = "column_delete_success";
                    $json_response -> msg_type = "good";
                } else {
                    $json_response -> msg_text = "Failed to Delete Column";
                    $json_response -> msg_code = "failed_column_delete";
                    $json_response -> msg_type = "bad";

                }
                mysqli_stmt_close($stmt);

                exit(json_encode($json_response));
            

            break;
        
        case "delete_note":
            $note_id = $_POST["note_id"];
            $board_id = $_POST["board_id"];

            // check ownership HERE

            // get todo lists
            if($stmt = mysqli_prepare($conn, "DELETE FROM notes WHERE id = ? ") ){
                mysqli_stmt_bind_param($stmt, "i", $note_id);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0){}
                    $json_response -> msg_text = "Note Deleted";
                    $json_response -> msg_code = "note_delete_success";
                    $json_response -> msg_type = "good";
                } else {
                    $json_response -> msg_text = "Failed to Delete Note";
                    $json_response -> msg_code = "failed_note_delete";
                    $json_response -> msg_type = "bad";

                }
                mysqli_stmt_close($stmt);

                exit(json_encode($json_response));
            

            break;

                
        case "check_note":
            $note_id = $_POST["note_id"];
            $complete = $_POST["complete"] == "true" ? 1 : 0;
            $board_id = $_POST["board_id"];

            // check ownership HERE

            // get todo lists
            if($stmt = mysqli_prepare($conn, "UPDATE notes SET checked = ? WHERE id = ? ") ){
                mysqli_stmt_bind_param($stmt, "ii", $complete, $note_id);
                mysqli_stmt_execute($stmt);
                if (mysqli_stmt_affected_rows($stmt) > 0){}
                    $json_response -> msg_text = "Note Updated";
                    $json_response -> msg_code = "note_updated";
                    $json_response -> msg_type = "good";
                } else {
                    $json_response -> msg_text = "Failed to Update Note";
                    $json_response -> msg_code = "failed_update_note";
                    $json_response -> msg_type = "bad";

                }
                mysqli_stmt_close($stmt);

                exit(json_encode($json_response));
            

            break;
        
    }

    
}


?>