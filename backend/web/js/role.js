$(document).ready(function(){

	var help_text = $('.help-block').text();

	if((~help_text.indexOf("incorrect")) && $('.help-block').css('display','block')){
		$('#role-modal').modal('show');
	};

});

