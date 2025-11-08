


window.addEventListener("load", ()=>{

    $.post("server/dashboard.php", {task_type:"get_projects"}, (data, status)=>{
        console.log(data);

        try{

            var json_response = JSON.parse(data);

            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

            if (json_response["msg_code"] == "projects_load_success"){
                
                for(b of json_response["boards"]){
                    var project_title = document.getElementById("new_project_title").value;
                    document.getElementById("new_project_title").value = "";

                    //create project board
                    var board_tabloid = document.createElement("div");
                    board_tabloid.setAttribute("board_id", b['board_id']);
                    board_tabloid.className = "board_tabloid";

                    var board_title = document.createElement("span");
                    board_title.innerHTML = b['board_name'];
                    board_tabloid.appendChild(board_title);

                    var trash = document.createElement("img");
                    trash.src = "images/icons/trash.png"
                    trash.className = "board_trash_btn";
                    board_tabloid.appendChild(trash);
                    trash.setAttribute("onclick","delete_project_prompt(this)");
                    
                    document.getElementById("project_grid").appendChild(board_tabloid);
                }
                


                
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }
    })
})

function create_project(btn){
    btn.disabled = true;

    popup_msg("creating project", "warning", 5);

    $.post("server/dashboard.php", {task_type:"create_project", title:document.getElementById("new_project_title").value}, (data, status)=>{

        console.log(data);

        try{


            var json_response = JSON.parse(data);

            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

            if (json_response["msg_code"] == "project_made"){
                
                var project_title = document.getElementById("new_project_title").value;
                document.getElementById("new_project_title").value = "";

                //create project board
                var board_tabloid = document.createElement("div");
                board_tabloid.setAttribute("board_id", json_response["new_project_id"]);
                board_tabloid.className = "board_tabloid";

                var board_title = document.createElement("span");
                board_title.innerHTML = project_title;
                board_tabloid.appendChild(board_title);

                var trash = document.createElement("img");
                trash.src = "images/icons/trash.png"
                trash.className = "board_trash_btn";
                board_tabloid.appendChild(trash);
                trash.setAttribute("onclick","delete_project_prompt(this)");

                board_tabloid.style.display = "none";
                $(board_tabloid).fadeIn(500);
                


                document.getElementById("project_grid").appendChild(board_tabloid);
                
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

        btn.disabled = false;
    })

}


function delete_project_prompt(delete_btn){
    var project_title = delete_btn.parentElement.querySelector("span").innerHTML; 
    var project_id = delete_btn.parentElement.getAttribute("board_id"); 

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
    prompt_info.innerHTML = "You are about to delete <br> <span class='del_proj_name'>" + project_title + "</span> <br> Type the name of this project again below to confirm intentional deletion.";
    prompt_info.className = "prompt_info";
    prompt_panel.appendChild(prompt_info);

    var prompt_input = document.createElement('input');
    prompt_input.id = "prompt_project_confirm_input";
    prompt_input.type = "text";
    prompt_input.className = "input_style del_name_input";
    prompt_input.placeholder = "retype project name here";
    prompt_panel.appendChild(prompt_input);


    var prompt_del_btn = document.createElement("input");
    prompt_del_btn.type = "button";
    prompt_del_btn.value = "Confirm";
    prompt_del_btn.className = "input_style input_button proj_del_btn"
    prompt_del_btn.onclick = ()=>{
        if (project_title == document.getElementById("prompt_project_confirm_input").value ){
            delete_project(project_id);
            popup_msg("Deleting Project", "warning", 5);
        }
        else{
            popup_msg("Deletion Cancelled: Typed Name does not match Project Name", "bad", 5);
        }

        $(prompt_bg).fadeOut(500, ()=>{prompt_bg.remove();});
        $(prompt_panel).fadeOut(500, ()=>{prompt_panel.remove();});
    }
    prompt_panel.appendChild(prompt_del_btn, project_id);
}   

function delete_project(project_id){

    $.post("server/dashboard.php", {task_type:"delete_board", board_id:project_id}, (data, status)=>{
        console.log(data);

        try{
            var json_response = JSON.parse(data);
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
            if (json_response["msg_code"] == "project_removed"){
                var proj_board = document.querySelector("[board_id='"+project_id+"']");
                $(proj_board).fadeOut(500);
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }
    })
}