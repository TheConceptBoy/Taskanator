


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
                board_tabloid.className = "board_tabloid";

                var board_title = document.createElement("span");
                board_title.innerHTML = project_title;
                board_tabloid.appendChild(board_title);
                


                document.getElementById("project_grid").appendChild(board_tabloid);
                
            }

        }catch(e){
            popup_msg(e + "<br><br>" + data, "bad", 15);
        }

        btn.disabled = false;
    })

}