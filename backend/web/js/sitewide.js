$(function () {
  $('[data-toggle="tooltip"]').tooltip();

  $(".back-button").onclick = function() {goBack()};

  if(window.location.href.indexOf('/study/view') != -1 || 
    window.location.href.indexOf('/role/update') != -1 || 
    window.location.href.indexOf('/participant/create') != -1 ||
    window.location.href.indexOf('/role/create') != -1 ||
    window.location.href.indexOf('/study/create') != -1){

    $("#main-header").hide();
    $("#main-header-content").hide();
  }

})

function goBack() 
{
  window.history.back();
}

$(document).ready(function(){

  $('.role-name:contains("Admin")').css('background','rgba(7,133,0,.15)');
  $('.role-name:contains("Staff")').css("background","rgba(0,95,133,.15)");
  $('.role-name:contains("Read")').css('background' , 'rgba(240,128,128,.15)');
  $('.role-name:contains("Researcher")').css('background','rgba(239,193,0,.15)');

  if(window.location.href.indexOf('/roles') != -1){
    $('body').css('width', '1444px'); 
  }
}); 
