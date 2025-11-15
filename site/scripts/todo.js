
window.addEventListener("load", ()=>{
    $("#todo_columns_list").sortable({
        placeholder: "col_sortable_placeholder",
        handle: ".col_drag_bar",
        tolerance: "pointer",
        update: function( event, ui ) {
            console.log("event", event);
            save_column_order(event.target);
        }
    });
})

function clear_columns(){
    var column_note_list = document.getElementById("todo_columns_list");
    column_note_list.innerHTML = "";
}


function ToDoListSetup(array_columns){ // called by dasboard.js when clicking on todo list tabloid 
    // popilate columns
    for(var c of array_columns){
        var column = add_column(c["col_id"], c["col_title"]);
        var column_note_list = column.querySelector(".note_list");
        for(n of c["notes"]){
            create_note(n["note_id"], n["note_text"], column_note_list, n["note_checked"]);
        }
    }
}

function add_column_clicked(btn){
    btn.classList.add("col_input_disabled");
    console.log("all col clicked")

    var todo_list_editor = document.getElementById("todo_list_editor")
    var todo_id = todo_list_editor.getAttribute("todo_id");
    var board_id = todo_list_editor.getAttribute("board_id");

    // popup_msg("creating column", "warning", 5);

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
        // popup_msg("Updating Note", "warning", 5);

        $.post("server/todo.php", {task_type:"update_column_title", column_id:column_id, new_text:col_title.value}, (data, status)=>{
            console.log(data);
            try{
                var json_response = JSON.parse(data);
                popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
                // if (json_response["msg_code"] == "note_updated"){}
            }catch(e){
                popup_msg(e + "<br><br>" + data, "bad", 15);
                console.log(e);
            }

            col_title.classList.remove("col_input_disabled");
        });
    }
    col_top_bar.appendChild(col_title);

    var close = document.createElement("img");
    close.className="todo_col_close";
    close.src = "images/icons/close.png";
    close.onclick = ()=>{
        close.classList.add("col_input_disabled");
        new_col.classList.add("disabled_dark")

        // send text editing
        // popup_msg("Updating Note", "warning", 5);

        $.post("server/todo.php", {task_type:"delete_column", column_id:column_id}, (data, status)=>{
            console.log(data);
            try{
                var json_response = JSON.parse(data);
                popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
                if (json_response["msg_code"] == "column_delete_success"){
                    $(new_col).hide("slide", {direction: "up"}, 500, ()=>{new_col.remove()})
                    return // prevent releasing of delete button lock
                }
            }catch(e){
                popup_msg(e + "<br><br>" + data, "bad", 15);
                console.log(e);
            }

            close.classList.remove("col_input_disabled");
            new_col.classList.remove("disabled_dark")
        });
    }
    col_top_bar.appendChild(close);



    var note_list = document.createElement("div");
    note_list.className = "note_list";
    new_col.appendChild(note_list);

    $(note_list).sortable({
        placeholder: "note_sortable_placeholder",
        handle:".note_drag_bar",
        connectWith: ".note_list",
        update: function( event, ui ) {
            console.log("event", event);
            save_note_order(event.target);
        }
    })

    var add_note = document.createElement("div");
    add_note.className = "add_note_btn";
    add_note.onclick = ()=>{
        create_note_clicked(add_note, column_id);
    }
    new_col.appendChild(add_note);



    var add_note_icon = document.createElement("img");
    add_note_icon.src = "images/icons/plus.png";
    add_note_icon.className = "add_note_icon";
    add_note.appendChild(add_note_icon);


    
    return new_col;

}


function create_note_clicked(btn, col_id){
    btn.classList.add("col_input_disabled");
    console.log("all col clicked")

    var dest_note_list = btn.closest(".todo_column").querySelector(".note_list")

    var todo_list_editor = document.getElementById("todo_list_editor")
    var todo_id = todo_list_editor.getAttribute("todo_id");
    var board_id = todo_list_editor.getAttribute("board_id");

    // popup_msg("Adding Note", "warning", 5);

    $.post("server/todo.php", {task_type:"create_note", col_id:col_id, todo_id:todo_id, board_id:board_id}, (data, status)=>{
        console.log(data);
        try{

            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

            if (json_response["msg_code"] == "note_created"){
                create_note(json_response["new_note_id"], "New Note", dest_note_list, false, before_note=null);
                save_note_order(dest_note_list);
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

        btn.classList.remove("col_input_disabled");
    })
}

function create_note(note_id, note_text="New Note", note_list, checked=false, before_note=null){

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

    var left_bar = document.createElement("div");
    left_bar.className = "note_left_bar";
    note.appendChild(left_bar);

    var checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.className = "note_checkbox";
    checkbox.checked = checked;
    checkbox.onchange = ()=>{
        note_check(checkbox)
    }
    left_bar.appendChild(checkbox);

    var drag_bar = document.createElement("div");
    drag_bar.className = "note_drag_bar";
    // drag_bar.innerHTML = "&#8801;"
    left_bar.appendChild(drag_bar);



    var side_edit_panel = document.createElement('div');
    side_edit_panel.className = "note_side_pane";
    note.appendChild(side_edit_panel);
    
    var edit = document.createElement("img");
    edit.className="note_edit";
    edit.src = "images/icons/edit.png";
    side_edit_panel.appendChild(edit);

    var close = document.createElement("img");
    close.className="todo_col_close_float_right";
    close.src = "images/icons/close.png";
    close.onclick = ()=>{
        close.classList.add("col_input_disabled");
        note.classList.add("disabled_dark");

        var board_id = document.getElementById("todo_list_editor").getAttribute("board_id");

        // send text editing
        // popup_msg("Deleting Note", "warning", 5);

        $.post("server/todo.php", {task_type:"delete_note", note_id:note_id, board_id:board_id}, (data, status)=>{
            console.log(data);
            try{
                var json_response = JSON.parse(data);
                popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
                if (json_response["msg_code"] == "note_delete_success"){
                    // $(note).hide("slide",{direction:"up"}, 500, ()=>{note.remove()})
                    $(note).hide(500, ()=>{note.remove()})
                    save_note_order(note.closest(".note_list"));
                    return
                }
            }catch(e){
                popup_msg(e + "<br><br>" + data, "bad", 15);
                console.log(e);
            }

            close.classList.remove("col_input_disabled");
            note.classList.remove("disabled_dark");
        });
    }
    side_edit_panel.appendChild(close);



    var textarea = document.createElement("textarea");
    textarea.name = "note_text";
    textarea.className = "note_textarea"
    // textarea.setAttribute("contenteditable",true);
    textarea.value = note_text
    textarea.onchange = ()=>{
        textarea.classList.add("col_input_disabled");

        // send text editing
        // popup_msg("Updating Note", "warning", 5);

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


    // Function to resize the textarea
    const resizeTextarea = () => {
        // Step 1: Reset height to auto to calculate new scrollHeight
        textarea.style.height = 'auto';

        // Step 2: Set height to scrollHeight (total content height)
        // Add a small buffer (e.g., 2px) to prevent scrollbar flicker in some browsers
        textarea.style.height = `${textarea.scrollHeight + 2}px`;
    };

    // Trigger resize on initial load (for pre-filled text)
    resizeTextarea();

    // Trigger resize on user input (typing, pasting, cutting)
    textarea.addEventListener('input', resizeTextarea);

    // Optional: Trigger resize on window resize (if container width changes)
    window.addEventListener('resize', resizeTextarea);




    var add_between = document.createElement("div");
    add_between.className = "note_add_between";
    note.appendChild(add_between);

    var add_between_plus = document.createElement("img");
    add_between_plus.src = "images/icons/plus.png"
    add_between_plus.className = "add_between_plus"
    add_between_plus.onclick = ()=>{

        add_between_plus.classList.add("col_input_disabled");
        var column_id = note.closest(".todo_column").getAttribute("column_id");

        var todo_list_editor = document.getElementById("todo_list_editor")
        var board_id = todo_list_editor.getAttribute("board_id");

        // popup_msg("Adding Note", "warning", 5);

        $.post("server/todo.php", {task_type:"create_note", col_id:column_id, board_id:board_id}, (data, status)=>{
            console.log(data);
            try{

                var json_response = JSON.parse(data);
                popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

                if (json_response["msg_code"] == "note_created"){
                    create_note(json_response["new_note_id"], "New Note", note_list, false, note);
                    save_note_order(note_list);
                }

            }catch(e){
                popup_msg(e + "<br><br>" + data, "bad", 15);
            }

            add_between_plus.classList.remove("col_input_disabled");
    
        })

    }
    add_between.appendChild(add_between_plus)

    
    
}



function save_note_order(note_list){

    var board_id = note_list.closest(".todo_column").getAttribute("column_id");

    var order_list = [];

    for(var x = 0; x < note_list.children.length; x++){
        var note = note_list.children[x];
        order_list.push([x, note.getAttribute("note_id")]);
    }
    console.log(order_list);

    $.post("server/todo.php", {task_type:"set_note_order", note_order:order_list, board_id:board_id}, (data, status)=>{
        console.log(data);
        try{

            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

    });

}




function save_column_order(column_list_list){

    var board_id = column_list_list.closest("#todo_list_editor").getAttribute("board_id");

    var order_list = [];

    for(var x = 0; x < column_list_list.children.length; x++){
        var column = column_list_list.children[x];
        order_list.push([x, column.getAttribute("column_id")]);
    }
    console.log(order_list);

    $.post("server/todo.php", {task_type:"set_column_order", order:order_list, board_id:board_id}, (data, status)=>{
        console.log(data);
        try{

            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

    });

}



function note_check(checkbox){
    checkbox.classList.add("col_input_disabled");
    var note_id = checkbox.closest(".note").getAttribute("note_id");
    var board_id = checkbox.closest("#todo_list_editor").getAttribute("board_id");

      $.post("server/todo.php", {task_type:"check_note", note_id:note_id, complete:checkbox.checked, board_id:board_id}, (data, status)=>{
        console.log(data);
        try{
            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

        checkbox.classList.remove("col_input_disabled");
    })
}