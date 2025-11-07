function run_setup(btn){
    btn.disabled = true;

    var setup_form = document.getElementById("setup_form");
    $.post("server/setup_scripts.php", {task:"inital_setup",username:document.getElementById("db_user").value, password:document.getElementById("db_password").value,dbname:document.getElementById("db_database").value}, (data,status)=>{
        console.log(data);
        
        try{
            var json_response = JSON.parse(data);
            
            popup_msg(json_response["msg_text"], json_response["msg_type"], -1, "stage_db_status");

            if (json_response["msg_code"] == "initialized"){
                for(each_input of document.getElementById("master_user_data").children){
                    each_input.disabled = false;

                    document.getElementById("db_user").disabled = true;
                    document.getElementById("db_password").disabled = true;
                    document.getElementById("db_database").disabled = true;
                }

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
    
}