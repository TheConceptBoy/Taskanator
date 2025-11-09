
window.addEventListener("beforeunload", function (e) {
    var confirmationMessage = 'It looks like you have been editing something. '
                            + 'If you leave before saving, your changes will be lost.';

    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
});






window.addEventListener("load", ()=>{

    $.post("server/dashboard.php", {task_type:"get_projects"}, (data, status)=>{
        console.log(data);

        try{

            var json_response = JSON.parse(data);

            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

            if (json_response["msg_code"] == "projects_load_success"){
                
                for(b of json_response["boards"]){
                    // var project_title = document.getElementById("new_project_title").value;
                    // document.getElementById("new_project_title").value = "";
                    
                    create_project_tabloid(b["board_id"], b["board_name"]);
                    
                }
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
            console.log(e);
        }
    })
})








function create_project_tabloid(id, name){
    //create project board
    var board_tabloid = document.createElement("div");
    board_tabloid.setAttribute("board_id", id);
    board_tabloid.className = "board_tabloid";
    board_tabloid.setAttribute("onclick", "open_board(this)");

    var board_title = document.createElement("span");
    board_title.className = "board_tabloid_title"
    board_title.innerHTML = name;
    board_tabloid.appendChild(board_title);

    var trash = document.createElement("img");
    trash.src = "images/icons/trash.png"
    trash.className = "board_trash_btn";
    board_tabloid.appendChild(trash);
    trash.setAttribute("onclick","delete_prompt(event, this, '"+name+"', 'project', "+id+", )");

    var load = document.createElement("img");
    load.src = "images/icons/load_4.png"
    load.className = "load_rotating_bot_left load_invisible";
    board_tabloid.appendChild(load);
    
    document.getElementById("project_grid").appendChild(board_tabloid);

    return board_tabloid;
}








function create_project(btn){
    
    var project_title = document.getElementById("new_project_title").value;
    document.getElementById("new_project_title").value = "";
    project_title = project_title.trim()
    
    // prevent empty
    if (project_title == ""){
        popup_msg("Cancelling: No name was provided.", "warning", 5);
        return
    }


    btn.disabled = true;

    document.getElementById("new_project_load_icon").classList.remove("load_invisible");

    popup_msg("creating project", "warning", 5);

    $.post("server/dashboard.php", {task_type:"create_project", title:document.getElementById("new_project_title").value}, (data, status)=>{

        console.log(data);

        try{

            var json_response = JSON.parse(data);

            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

            if (json_response["msg_code"] == "project_made"){
                
                

                //create project board
                var new_board_tabloid = create_project_tabloid(json_response["new_project_id"], project_title);

                new_board_tabloid.style.display = "none";
                $(new_board_tabloid).fadeIn(500);
                
                document.getElementById("project_grid").appendChild(new_board_tabloid);

            }

            document.getElementById("new_project_load_icon").classList.add("load_invisible");

        }catch(e){
            console.log(e);
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

        btn.disabled = false;
    })

}









function delete_prompt(event, delete_btn, title, delete_type="[undefined type]", delete_id=null){ // delete_type can be project / todo / graph
    event.stopPropagation();

    var prompt_bg = document.createElement("div");
    prompt_bg.className = "prompt_bg";
    document.body.appendChild(prompt_bg);
    $(prompt_bg).fadeIn(500);

    var prompt_panel = document.createElement("div");
    prompt_panel.className = "prompt_panel";
    document.body.appendChild(prompt_panel);
    prompt_panel.style.display = "none";
    $(prompt_panel).fadeIn(500);

    var prompt_info = document.createElement("div");
    prompt_info.innerHTML = "You are about to delete a <b>"+delete_type+"</b>: <br> <span class='del_proj_name'>" + title + "</span> <br> <span> Type the name of this item again below<br> to confirm intentional deletion. </span>";
    prompt_info.className = "prompt_info";
    prompt_panel.appendChild(prompt_info);

    var prompt_input = document.createElement('input');
    prompt_input.id = "prompt_project_confirm_input";
    prompt_input.type = "text";
    prompt_input.className = "input_style del_name_input";
    prompt_input.placeholder = "retype name here";
    prompt_panel.appendChild(prompt_input);


    var prompt_del_btn = document.createElement("input");
    prompt_del_btn.type = "button";
    prompt_del_btn.value = "Confirm";
    prompt_del_btn.className = "input_style input_button proj_del_btn input_crimson"
    prompt_panel.appendChild(prompt_del_btn);
    prompt_del_btn.onclick = ()=>{
        if (title == document.getElementById("prompt_project_confirm_input").value ){
            switch(delete_type){
                case "project":
                    delete_project(delete_id);
                    break;
                    
                case "ToDo":
                    delete_todo_list(delete_id);
                    break;
                    
                case "graph":
                    delete_graph(delete_id);
                    break;
            }
            
            popup_msg("Deleting "+delete_type, "warning", 5);
        }
        else{
            popup_msg("Deletion Cancelled: Typed Name does not match "+delete_type+" name", "bad", 5);
        }

        $(prompt_bg).fadeOut(500, ()=>{prompt_bg.remove();});
        $(prompt_panel).fadeOut(500, ()=>{prompt_panel.remove();});
    }

    var prompt_cancel_btn = document.createElement("input");
    prompt_cancel_btn.type = "button";
    prompt_cancel_btn.value = "Cancel";
    prompt_cancel_btn.className = "input_style input_button proj_del_btn"
    prompt_panel.appendChild(prompt_cancel_btn);
    prompt_cancel_btn.onclick = ()=>{
        popup_msg("Deletion Cancelled", "bad", 5);
        $(prompt_bg).fadeOut(500, ()=>{prompt_bg.remove();});
        $(prompt_panel).fadeOut(500, ()=>{prompt_panel.remove();});
    }


    
}   





function delete_project(project_id){
    var proj_board = document.querySelector("[board_id='"+project_id+"']");
    proj_board.querySelector(".load_rotating_bot_left").classList.remove("load_invisible");

    $.post("server/dashboard.php", {task_type:"delete_board", board_id:project_id}, (data, status)=>{
        console.log(data);

        try{
            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
            if (json_response["msg_code"] == "project_removed"){
                
                $(proj_board).fadeOut(500);
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }
    })
}





function delete_todo_list(id){

    var todo_tabloid = document.querySelector("[todo_list_id='"+id+"']");
    var todo_board_onwer_id = todo_tabloid.getAttribute("my_board_id");

    todo_tabloid.querySelector(".load_rotating_bot_left").classList.remove("load_invisible");

    $.post("server/dashboard.php", {task_type:"delete_todo_list", todo_id:id, board_id:todo_board_onwer_id}, (data, status)=>{
        console.log(data);

        try{
            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
            if (json_response["msg_code"] == "todo_removed"){
    
                $(todo_tabloid).fadeOut(500, ()=>{ todo_tabloid.remove() });
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }
    })
}


function delete_graph(id){

    var graph_tabloid = document.querySelector("[graph_id='"+id+"']");
    var graph_id = graph_tabloid.getAttribute("my_board_id");

    graph_tabloid.querySelector(".load_rotating_bot_left").classList.remove("load_invisible");

    $.post("server/dashboard.php", {task_type:"delete_graph", todo_id:id, board_id:graph_id}, (data, status)=>{
        console.log(data);

        try{
            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
            if (json_response["msg_code"] == "graph_removed"){
    
                $(graph_tabloid).fadeOut(500, ()=>{ graph_tabloid.remove(); });

            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
            console.log(e);
        }
    })
}








function open_board(board_tabloid){
    var b_id = board_tabloid.getAttribute("board_id");
    console.log("open board", b_id);

    board_tabloid.classList.add("disabled_a");
    board_tabloid.querySelector(".load_rotating_bot_left").classList.remove("load_invisible");


    // send request to get project data
    $.post("server/dashboard.php", {task_type:"load_project_content", board_id:b_id}, (data, status)=>{

        console.log(data);

        try{

            var json_response = JSON.parse(data);

            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

            if (json_response["msg_code"] == "project_load_success"){
                //store board id
                document.getElementById("board_tools_panel").setAttribute("board_id", b_id);

                // set title in project edit board
                document.getElementById("project_title_header").innerHTML = board_tabloid.querySelector(".board_tabloid_title").innerHTML;

                // exit manager, enter project
                document.getElementById("boards_manager").classList.add("dash_exit_left");
                document.getElementById("board_tools_panel").style.display = "block";

                setTimeout(()=>{
                    document.getElementById("board_tools_panel").classList.remove("dash_exit_right");
                }, 15)

                setTimeout(()=>{
                    document.getElementById("boards_manager").style.display = "none";
                    console.log("display set")
                }, 250);
                // $("#boards_manager").hide("slide", { direction: "left", distance: 2000 }, 1000);



                //populate todo lists and graphs
                // todo lists
                document.getElementById("lists_grid").innerHTML = ""; // delete old lists
                for (td of json_response["todo_lists"]){
                    create_list_tabloid(td["todo_list_id"], td["todo_list_name"], b_id);  
                }

                // graphs
                document.getElementById("graph_grid").innerHTML = ""; // delete old lists
                for (td of json_response["graphs"]){
                    create_graph_tabloid(td["graph_id"], td["graph_name"], b_id);  
                }

                 
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
            console.log(e);
        }

        board_tabloid.classList.remove("disabled_a");
        board_tabloid.querySelector(".load_rotating_bot_left").classList.add("load_invisible");
    })

}








function back_to_dashboard(){

    document.getElementById("board_tools_panel").classList.add("dash_exit_right");
    document.getElementById("boards_manager").style.display = "block";
    
    setTimeout(()=>{
        document.getElementById("boards_manager").classList.remove("dash_exit_left");
    }, 5)

    setTimeout(()=>{
        document.getElementById("board_tools_panel").style.display = "none";
        console.log("display set")
    }, 250);
    // $("#boards_manager").hide("slide", { direction: "left", distance: 2000 }, 1000);
}









function add_list(btn){
    var title = document.getElementById("new_list_title").value
    document.getElementById("new_list_title").value = ""
    title = title.trim()

    var board_id = document.getElementById("board_tools_panel").getAttribute("board_id");

    // prevent empty
    if (title == ""){
        popup_msg("Cancelling: No name was provided.", "warning", 5);
        return
    }

    btn.disabled = true;
    popup_msg("creating ToDo list", "warning", 5);

    $.post("server/dashboard.php", {task_type:"create_todo_list", title:title, board_id:board_id}, (data, status)=>{
        console.log(data);
        try{

            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
            if (json_response["msg_code"] == "todo_made"){
                create_list_tabloid(json_response["new_id"], title, board_id, true);  
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

        btn.disabled = false;
    })
}



function add_graph(btn){
    var title = document.getElementById("new_graph_title").value
    document.getElementById("new_graph_title").value = ""
    title = title.trim()

    var board_id = document.getElementById("board_tools_panel").getAttribute("board_id");

    // prevent empty
    if (title == ""){
        popup_msg("Cancelling: No name was provided.", "warning", 5);
        return
    }

    btn.disabled = true;
    popup_msg("creating ToDo list", "warning", 5);

    $.post("server/dashboard.php", {task_type:"create_graph", title:title, board_id:board_id}, (data, status)=>{
        console.log(data);
        try{

            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
            if (json_response["msg_code"] == "graph_made"){
                create_graph_tabloid(json_response["new_id"], title, board_id, true);  
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

        btn.disabled = false;
    })
}





function create_list_tabloid(id, title, board_owner_id=null, fade=false, ){
    var lists_grid = document.getElementById("lists_grid");

    var new_list_tabloid = document.createElement("div");
    new_list_tabloid.setAttribute("todo_list_id", id);
    new_list_tabloid.setAttribute("my_board_id", board_owner_id);
    new_list_tabloid.classList = "list_add_panel";
    lists_grid.appendChild(new_list_tabloid);

    var title_span = document.createElement("span")
    title_span.className = "dash_section_title_small"
    title_span.innerHTML = title
    new_list_tabloid.appendChild(title_span);

    var trash = document.createElement("img");
    trash.src = "images/icons/trash.png"
    trash.className = "board_trash_btn";
    new_list_tabloid.appendChild(trash);
    trash.setAttribute("onclick","delete_prompt(event, this, '"+title+"', 'ToDo', "+id+")");

    
    var load = document.createElement("img");
    load.src = "images/icons/load_4.png"
    load.className = "load_rotating_bot_left load_invisible";
    new_list_tabloid.appendChild(load);

    if (fade){
        $(new_list_tabloid).css("display", "none").fadeIn(500);
    }
}


function create_graph_tabloid(id, title, board_owner_id=null, fade=false, ){
    var graph_grid = document.getElementById("graph_grid");

    var new_graph_tabloid = document.createElement("div");
    new_graph_tabloid.setAttribute("graph_id", id);
    new_graph_tabloid.setAttribute("my_board_id", board_owner_id);
    new_graph_tabloid.classList = "list_add_panel";
    graph_grid.appendChild(new_graph_tabloid);

    var title_span = document.createElement("span")
    title_span.className = "dash_section_title_small"
    title_span.innerHTML = title
    new_graph_tabloid.appendChild(title_span);

    var trash = document.createElement("img");
    trash.src = "images/icons/trash.png"
    trash.className = "board_trash_btn";
    new_graph_tabloid.appendChild(trash);
    trash.setAttribute("onclick","delete_prompt(event, this, '"+title+"', 'graph', "+id+")");

    
    var load = document.createElement("img");
    load.src = "images/icons/load_4.png"
    load.className = "load_rotating_bot_left load_invisible";
    new_graph_tabloid.appendChild(load);

    if (fade){
        $(new_graph_tabloid).css("display", "none").fadeIn(500);
    }
}