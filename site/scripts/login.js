function login_pressed(btn){

    btn.disabled = true;

    

    $.post("server/login.php", {task_type:"login", email:document.getElementById("login_email").value, password:document.getElementById("login_pass").value}, (data, status)=>{
        
        try{
            
            var json_response = JSON.parse(data);
            console.log(json_response);

            popup_msg(json_response["msg_text"], json_response["msg_type"], 5);

            console.log(window.location);

            if (json_response["msg_code"] == "login_success"){

                // for login page, navigate to the dash page
                if (window.location.href.includes("index")){ 
                    console.log("navigating to dash");

                    setTimeout(()=>{
                        window.location.replace("dashboard.html")
                        // window.location.assign("dashboard.html");
                    }, 1000);
                }
                

            }else{ // re-enable the button.
                btn.disabled = false;
            }
        }
        catch(e){
            popup_msg(e, "bad", 15);
            btn.disabled = false;
        }
        
    });
}