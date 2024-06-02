function run_setup(btn){
    btn.disabled = true;

    var setup_form = document.getElementById("setup_form");
    $.post("server/setup_scripts.php", {task:"inital_setup",username:setup_form.children[0].value, password:setup_form.children[1].value,dbname:setup_form.children[2].value}, (data,status)=>{
        console.log(data);
        btn.disabled = false;

        var json_response = JSON.parse(data);
        if (json_response["msg_code"] == "initialized"){
            for(each_input of document.getElementById("master_user_data").children){
                each_input.disabled = false;
            }

        }
    })
}

function register_master_user(btn){
    btn.disabled = true;

    var data = $("#master_user_data").serializeArray();
    data.push({name:"task", value:"register_master_user"})

    $.post("server/setup_scripts.php", data, (data, status)=>{
        console.log(data)
        btn.disabled = false;

        var json_response = JSON.parse(data);
        if (json_response['msg_code'] == "user_registered"){
            btn.disabled = true;
        }
    })
}