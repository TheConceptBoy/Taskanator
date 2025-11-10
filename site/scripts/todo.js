
window.addEventListener("load", ()=>{
    $("#todo_columns_list").sortable({
        placeholder: "col_sortable_placeholder",
        handle: ".col_drag_bar",
        tolerance: "pointer"
    });
})

function clear_columns(){
    var column_note_list = document.getElementById("todo_columns_list");
    column_note_list.innerHTML = "";
}


function ToDoListSetup(array_columns){ // called by dasboard.js when clicking on todo list tabloid 
    clear_columns()
    
    // popilate columns
    for(var c of array_columns){
        var column = add_column(c["col_id"], c["col_title"]);
        var column_note_list = column.querySelector(".note_list");
        for(n of c["notes"]){
            create_note(n["note_id"], n["note_text"], column_note_list);
        }
    }
}

function add_column_clicked(btn){
    btn.classList.add("col_input_disabled");
    console.log("all col clicked")

    var todo_list_editor = document.getElementById("todo_list_editor")
    var todo_id = todo_list_editor.getAttribute("todo_id");
    var board_id = todo_list_editor.getAttribute("board_id");

    popup_msg("creating column", "warning", 5);

    $.post("server/todo.php", {task_type:"create_column", todo_id:todo_id, board_id:board_id}, (data, status)=>{
        console.log(data);
        try{

            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

            if (json_response["msg_code"] == "column_created"){
                add_column(json_response["new_column_id"]); 
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

        btn.classList.remove("col_input_disabled");
    })

}

function add_column(column_id, title="New Column"){
    var todo_columns_list = document.getElementById("todo_columns_list"); 

    var new_col = document.createElement("div");
    new_col.setAttribute("column_id",column_id);
    new_col.className = "todo_column";
    todo_columns_list.appendChild(new_col);

    var drag_bar = document.createElement("div");
    drag_bar.className = "col_drag_bar";
    new_col.appendChild(drag_bar);

    var col_top_bar = document.createElement("div");
    col_top_bar.className = "col_top_bar"
    new_col.appendChild(col_top_bar);

    var col_title = document.createElement("input");
    col_title.type = "input";
    col_title.className = "todo_col_title"
    col_title.value = title;
    // col_title.setAttribute("contenteditable",true);
    col_title.onchange = ()=>{
        col_title.classList.add("col_input_disabled");

        // send text editing
        popup_msg("Updating Note", "warning", 5);

        $.post("server/todo.php", {task_type:"update_column_title", column_id:column_id, new_text:col_title.value}, (data, status)=>{
            console.log(data);
            try{
                var json_response = JSON.parse(data);
                popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
                // if (json_response["msg_code"] == "note_updated"){}
            }catch(e){
                popup_msg(e + "<br><br>" + data, "bad", 15);
            }

            col_title.classList.remove("col_input_disabled");
        });
    }
    col_top_bar.appendChild(col_title);

    var close = document.createElement("img");
    close.className="todo_col_close";
    close.src = "images/icons/close.png";
    col_top_bar.appendChild(close);



    var note_list = document.createElement("div");
    note_list.className = "note_list";
    new_col.appendChild(note_list);

    $(note_list).sortable({
        placeholder: "note_sortable_placeholder",
        handle:".note_drag_bar",
        connectWith: ".note_list"
    })

    var add_note = document.createElement("div");
    add_note.className = "add_note_btn";
    add_note.onclick = ()=>{
        create_node_clicked(add_note, column_id);
    }
    new_col.appendChild(add_note);



    var add_note_icon = document.createElement("img");
    add_note_icon.src = "images/icons/plus.png";
    add_note_icon.className = "add_note_icon";
    add_note.appendChild(add_note_icon);


    
    return new_col;

}


function create_node_clicked(btn, col_id){
    btn.classList.add("col_input_disabled");
    console.log("all col clicked")

    var dest_note_list = btn.closest(".todo_column").querySelector(".note_list")

    var todo_list_editor = document.getElementById("todo_list_editor")
    var todo_id = todo_list_editor.getAttribute("todo_id");
    var board_id = todo_list_editor.getAttribute("board_id");

    popup_msg("creating column", "warning", 5);

    $.post("server/todo.php", {task_type:"create_note", col_id:col_id, todo_id:todo_id, board_id:board_id}, (data, status)=>{
        console.log(data);
        try{

            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

            if (json_response["msg_code"] == "note_created"){
                create_note(json_response["new_note_id"], "New Note", dest_note_list, before_note=null)
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

        btn.classList.remove("col_input_disabled");
    })
}

function create_note(note_id, note_text="New Note", note_list, before_note=null){

    var note = document.createElement("div");
    note.setAttribute("note_id", note_id);
    note.className = "note";

    if (before_note == null){
        note_list.appendChild(note);
        console.log("inser bottom");
    }else{
        note_list.insertBefore(note, before_note);
        console.log("inser in between");
    }


    // var top_bar = document.createElement("div");
    // top_bar.className = "note_top_bar";
    // note.appendChild(top_bar)

    var drag_bar = document.createElement("div");
    drag_bar.className = "note_drag_bar";
    note.appendChild(drag_bar);

    var side_edit_panel = document.createElement('div');
    side_edit_panel.className = "note_side_pane";
    note.appendChild(side_edit_panel);

    var close = document.createElement("img");
    close.className="todo_col_close_float_right";
    close.src = "images/icons/close.png";
    side_edit_panel.appendChild(close);
    
    var checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.className = "note_checkbox";
    side_edit_panel.appendChild(checkbox);

    var close = document.createElement("img");
    close.className="note_edit";
    close.src = "images/icons/edit.png";
    side_edit_panel.appendChild(close);


    var textarea = document.createElement("textarea");
    textarea.name = "note_text";
    textarea.className = "note_textarea"
    // textarea.setAttribute("contenteditable",true);
    textarea.value = note_text
    textarea.onchange = ()=>{
        textarea.classList.add("col_input_disabled");

        // send text editing
        popup_msg("Updating Note", "warning", 5);

        $.post("server/todo.php", {task_type:"update_note", note_id:note_id, new_text:textarea.value}, (data, status)=>{
            console.log(data);
            try{
                var json_response = JSON.parse(data);
                popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
                // if (json_response["msg_code"] == "note_updated"){}
            }catch(e){
                popup_msg(e + "<br><br>" + data, "bad", 15);
            }

            textarea.classList.remove("col_input_disabled");
        });
    }
    note.appendChild(textarea);

    var add_between = document.createElement("div");
    add_between.className = "note_add_between";
    note.appendChild(add_between);

    var add_between_plus = document.createElement("img");
    add_between_plus.src = "images/icons/plus.png"
    add_between_plus.className = "add_between_plus"
    add_between_plus.onclick = ()=>{
            create_note(id, note_list, note);
    }
    add_between.appendChild(add_between_plus)

    
    
}