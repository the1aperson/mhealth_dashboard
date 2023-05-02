$(document).ready(function(){
	
    document.getElementsByClassName("icon-visibility-on_blue").onclick = function() {login_password()};	
    document.getElementsByClassName("icon-visibility-on_blue").onclick = function() {role_password()};	
	document.getElementsByClassName("icon-visibility-on_blue").onclick = function() {study_drop_password()};	
});

function login_password(){

    var login_password = document.getElementById("loginform-password");
    var show_password = document.getElementsByClassName("show_password")[0];
    var eye_icon = document.getElementById("eye-icon");


    if (login_password.type == "password") {
        (login_password.type = "text"),
        (show_password.innerHTML = "Hide");
        (eye_icon.className = "icon-visibility-off_blue");
    } else {
        (login_password.type = "password"),
        (show_password.innerHTML = "Show");
        (eye_icon.className = "icon-visibility-on_blue"); 
    }
};

function role_password(){

    var role_password = document.getElementById("roleform-password");
    var show_password = document.getElementsByClassName("show_password")[0];
    var eye_icon = document.getElementById("eye-icon");

    if (role_password.type == "password") {
        (role_password.type = "text"),
        (show_password.innerHTML = "Hide");
        (eye_icon.className = "icon-visibility-off_blue");
    } else {
        (role_password.type = "password"),
        (show_password.innerHTML = "Show");
        (eye_icon.className = "icon-visibility-on_blue"); 
    }
};


function study_drop_password(){

    var study_drop_password = document.getElementById("participantdropform-password");
    var show_password = document.getElementsByClassName("show_password")[0];
    var eye_icon = document.getElementById("eye-icon");

    if (study_drop_password.type == "password") {
        (study_drop_password.type = "text"),
        (show_password.innerHTML = "Hide");
        (eye_icon.className = "icon-visibility-off_blue");
    } else {
        (study_drop_password.type = "password"),
        (show_password.innerHTML = "Show");
        (eye_icon.className = "icon-visibility-on_blue"); 
    }
};
