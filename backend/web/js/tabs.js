$(document).ready(function(){

    $('.nav-tabs li a').click(function(){
		var tab_id = $(this).attr('data-tab');

		$('.nav-tabs li a').removeClass('active');
		$('.tab-pane').removeClass('active');

		$(this).addClass('active');
		$("#"+tab_id).addClass('active');
	})

	$("#red-alert-tabs li").click(function() {
		if ($('#followed-up').hasClass('active')){
			$('.alert-item').filter(function(){
				return $('div', this).hasClass('alert-log-follow-up');
			}).hide();
			$('.alert-item').filter(function(){
				return $('div', this).hasClass('alert-item-follow-up-msg');
			}).show();			

		}else if ($('#no-action').hasClass('active')){
			$('.alert-item').filter(function(){
				return $('div', this).hasClass('alert-item-follow-up-msg');
			}).hide();
			$('.alert-item').filter(function(){
				return $('div', this).hasClass('alert-log-follow-up');
			}).show();				
			
		} else if ($('#all-red').hasClass('active')) {
			$('.alert-item').show();
	}
})
});
