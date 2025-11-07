function popup_msg(text, type = "good", delay=-1, dest_id=null){

    var popup = document.createElement("div");
    popup.className = "status_popup";
    popup.style.display = "none";

    var span_text = document.createElement("span");
    span_text.innerHTML = text;
    popup.appendChild(span_text);

    var close_btn = document.createElement("div");
    close_btn.className = 'popup_close';
    close_btn.setAttribute("onClick", "popup_msg_close(this)");
    popup.appendChild(close_btn);

    var close_icon = document.createElement("img");
    close_icon.className = "popup_close_icon"
    close_icon.src = "images/icons/close.png";
    close_btn.appendChild(close_icon);

    

    if (type=="bad"){
        popup.classList.add("popup_bad")
    }
    else if (type == "warning"){
        popup.classList.add("popup_warning")
    }
    
    
    if (dest_id == null){
        // deposit message into bottom left corner float box. create one if one does not exist
        
        var float_box = document.getElementById("status_float_box");
        if (float_box == null){
            float_box = document.createElement("div");
            float_box.id = "status_float_box";
            document.body.appendChild(float_box);
        }

        float_box.appendChild(popup);

    }else{
        document.getElementById(dest_id).appendChild(popup);
    }

    // if delay is supplied, make message disappear after that delay in seconds
    if (delay != -1 ){
        $(popup).slideDown(500).delay(delay*1000).slideUp(500, function() { $(this).remove(); })
    }else{
        $(popup).slideDown(500);
    }
}

function popup_msg_close(close_btn){
    console.log(close_btn.parentElement);
    $(close_btn.parentElement).stop().fadeOut(500);
}