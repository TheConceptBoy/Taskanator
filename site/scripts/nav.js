window.onload = ()=>{
    var nav = document.body.querySelector("nav");

    var nav_div = document.createElement("div");
    nav_div.className = "nav_div"
    nav.appendChild(nav_div);

    var span = document.createElement("span");
    span.innerHTML = "Taskanator";
    span.className = "header_title"
    nav_div.appendChild(span);


    var index = document.createElement("a");
    index.innerHTML = "back to login";
    index.href="index.html";
    nav_div.appendChild(index);
    
    var settings = document.createElement("a");
    settings.innerHTML = "settings";
    settings.href = "settings.html";
    nav_div.appendChild(settings);
}