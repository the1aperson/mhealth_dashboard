$(document).ready(function(){

    $(".navbar-left-toggle-button").click(function(){
	    $("body").toggleClass("hide-navbar");
        document.cookie = "hide_navbar=" + $("body").hasClass("hide-navbar") + ";path=/";
        $('.alt-nav').toggle();

    });
});
