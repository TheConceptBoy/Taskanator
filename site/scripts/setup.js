window.onload = ()=>{
    var ip_checkbox = document.getElementById("enable_ip_filter");
    for(x of document.getElementById("setup_new_ip_field").querySelectorAll("input")){
        x.disabled = !ip_checkbox.checked;
    }
    for(x of document.getElementById("setup_ip_list").querySelectorAll("input")){
        x.disabled = !ip_checkbox.checked;
    }

}



function run_setup(btn){
    btn.disabled = true;

    var setup_form = document.getElementById("setup_form");
    $.post("server/setup_database.php", {task:"inital_setup",username:document.getElementById("db_user").value, password:document.getElementById("db_password").value,dbname:document.getElementById("db_database").value}, (data,status)=>{
        console.log(data);[]
        
        try{
            var json_response = JSON.parse(data);
            
            popup_msg(json_response["msg_text"], json_response["msg_type"], -1, "stage_db_status");

            if (json_response["msg_code"] == "initialized"){
                for(each_input of document.getElementById("master_user_data").children){
                    each_input.disabled = false;
                }

                document.getElementById("db_user").disabled = true;
                document.getElementById("db_password").disabled = true;
                document.getElementById("db_database").disabled = true;
                document.getElementById("record_ip_filter").disabled = false;
                document.getElementById("enable_ip_filter").disabled = false;

                $("#section_2").delay(500).slideDown(500);

            }else{
                btn.disabled = false; // reneable the button if failed to setup
            }
        } catch(e){
            popup_msg("fatal error has occured", "warning", 5, "stage_db_status");
            popup_msg(e, "bad", 30);
            popup_msg(data, "bad", 30);
            btn.disabled = false; // reneable the button if failed to setup
        }
    })
}

function register_master_user(btn){
    btn.disabled = true;


    var data = $("#master_user_data").serializeArray();
    data.push({name:"task", value:"register_master_user"})

    $.post("server/setup_scripts.php", data, (data, status)=>{
        console.log(data)
        btn.disabled = true;

        try{
            var json_response = JSON.parse(data);

            popup_msg(json_response["msg_text"], json_response["msg_type"], -1, "stage_user_status");

            if (json_response['msg_code'] == "user_registered"){
                document.getElementById("master_username").disabled = true;
                document.getElementById("master_password").disabled = true;
                document.getElementById("master_fname").disabled = true;
                document.getElementById("master_lname").disabled = true;

                $("#section_3").delay(500).slideDown(500);
            }else{
                btn.disabled = false;

            }
        }
        catch(e){
            popup_msg("fatal error has occured", "warning", 5, "stage_user_status");
            popup_msg(e, "bad", 30);
            popup_msg(data, "bad", 30);
            console.log(e);
            btn.disabled = false;
        }
    })
}


function add_ip_to_list(){
    var ip_inputs_container = document.getElementById("setup_new_ip_field");
    var list = document.getElementById("setup_ip_list");

    var div = document.createElement("div");
    div.className = "ip_listing";
    list.appendChild(div);

    var span_title = document.createElement("span");
    span_title.innerHTML = ip_inputs_container.querySelector('[placeholder="name"]').value;
    div.appendChild(span_title);

    var span_info = document.createElement("span");
    span_info.innerHTML = "[hover for info]";
    span_info.title = ip_inputs_container.querySelector('[placeholder="description"]').value;
    div.appendChild(span_info);

    var span_ip = document.createElement("span");
    var ip = "";
    for(x of ip_inputs_container.querySelector('.setup_ip_grid').querySelectorAll('input')){
        ip += x.value + ".";
    }
    ip = ip.substring(0, ip.length-1); // remove trailing .

    span_ip.innerHTML = ip;
    div.appendChild(span_ip);

    var delete_btn = document.createElement("input");
    delete_btn.type = "button";
    delete_btn.value = "remove";
    delete_btn.setAttribute("onclick", "remove_ip_from_list(this)");
    
    div.appendChild(delete_btn)
    
}

function remove_ip_from_list(ip_listing){
    ip_listing.parentElement.remove();
}

function toggle_ip_filtering(checkbox){

    for(x of document.getElementById("setup_new_ip_field").querySelectorAll("input")){ x.disabled = true; }
    for(x of document.getElementById("setup_ip_list").querySelectorAll("input")){  x.disabled = true; }
    checkbox.disabled = true;
    var prop = [ ['ip_filter', checkbox.checked] ];
    $.post("server/setup_scripts.php", {task:"set_properties", property_list:prop}, (data, status)=>{

        try{
            var json_response = JSON.parse(data);

            popup_msg(json_response["msg_text"], json_response["msg_type"], -1);

            if (json_response['msg_code'] == "settings_set"){

                if (checkbox.checked){
                    for(x of document.getElementById("setup_new_ip_field").querySelectorAll("input")){ x.disabled = false; }
                    for(x of document.getElementById("setup_ip_list").querySelectorAll("input")){  x.disabled = false; }
                }else{
                    for(x of document.getElementById("setup_new_ip_field").querySelectorAll("input")){ x.disabled = true; }
                    for(x of document.getElementById("setup_ip_list").querySelectorAll("input")){  x.disabled = true; }
                }
                
            }else{
                checkbox.disabled = false;
                
            }
        }
        catch(e){
            popup_msg("fatal error has occured", "warning", 5, "stage_user_status");
            popup_msg(e, "bad", 30);
            popup_msg(data, "bad", 30);
            console.log(e);
            

        }

 
        
        checkbox.disabled = false;
    });


    // for(x of document.getElementById("setup_new_ip_field").querySelectorAll("input")){
    //     x.disabled = !checkbox.checked;
    // }
    // for(x of document.getElementById("setup_ip_list").querySelectorAll("input")){
    //     x.disabled = !checkbox.checked;
    // }


}






function record_ip_filter(checkbox){

    var ip_list = [];
    for(x of document.getElementById("setup_ip_list").children){
        var ip_listing = [];
        ip_listing.push(x.children[0].innerHTML);
        ip_listing.push(x.children[1].title);
        ip_listing.push(x.children[2].innerHTML);
        ip_list.push(ip_listing);
    }

    console.log(ip_list);
    checkbox.disabled = true;

    $.post("server/setup_scripts.php", {task:"ip_filter_submit", ip_list:ip_list}, (data, status)=>{


        for(x of document.getElementById("setup_new_ip_field").querySelectorAll("input")){ x.disabled = true; }
        for(x of document.getElementById("setup_ip_list").querySelectorAll("input")){  x.disabled = true; }

        try{
            var json_response = JSON.parse(data);

            popup_msg(json_response["msg_text"], json_response["msg_type"], 5, "stage_ip_status");

            if (json_response['msg_code'] == "ip_recorded"){
                if (checkbox.checked){
                    for(x of document.getElementById("setup_new_ip_field").querySelectorAll("input")){ x.disabled = true; }
                    for(x of document.getElementById("setup_ip_list").querySelectorAll("input")){  x.disabled = true; }
                }

                $("#section_4").slideDown(500);
                checkbox.disabled = true
            }
            
            checkbox.disabled = false;
        }
        catch(e){
            popup_msg("fatal error has occured", "warning", 5, "stage_user_status");
            popup_msg(e, "bad", 30);
            popup_msg(data, "bad", 30);
            console.log(e);

            for(x of document.getElementById("setup_new_ip_field").querySelectorAll("input")){ x.disabled = false; }
            for(x of document.getElementById("setup_ip_list").querySelectorAll("input")){  x.disabled = false; }

            checkbox.disabled = false;
        }
    })

}



function finish_setup(finish_button){
    finish_button.disabled = true;
    
    $.post("server/setup_scripts.php", {task:"finish"}, (data, status)=>{
        try{
            var json_response = JSON.parse(data);
            
            console.log(json_response);
            
            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);
            
            if (json_response["msg_code"] == "setup_finished"){
                window.location.replace("index.html");
                console.log("navigating to login");
          
            }else{

                finish_button.disabled = false;
            }
        }
        catch(e){
            popup_msg("failed to finalize setup", "warning", 5, "stage_user_status");
            finish_button.disabled = false;
        }
        
    })
}