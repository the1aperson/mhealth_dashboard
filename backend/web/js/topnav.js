$(document).ready(function(){
	
	setupSessionCountdown();		

	$('.study-dropdown-container .dropdown-toggle').click(function(){
		if(!$('.dropdown').hasClass('open')){
			$('#navbar-left-list').css('opacity', '0.6');
			$('.navbar-border').css('border-radius', '25px 25px 0 0');

			highlightCurrentStudy()	

		} else {
			$('#navbar-left-list').css('opacity', '1');
			$('.navbar-border').css('border-radius', '25px');

		}	
	})
		
	$(document).click(function(){
		if(!$('.dropdown').hasClass('open')){
			$('.dropdown').removeClass('open');
			$('#navbar-left-list').css('opacity', '1');
			$('.navbar-border').css('border-radius', '25px');
		}
	});
});

function highlightCurrentStudy()
{
	$('.study-dropdown-container .dropdown-menu li a').each(function(){
		if($(this).text().trim() == $('.navbar-study-name').text().trim()){
			$(this).addClass('selected');
		}
	})
}

// displays the countdown timer at the top of the navigation

function setupSessionCountdown()
{
	if($("#header-countdown").length == 0)
	{
		return;
	}
	
	setInterval(function(){
		var expirationTime = parseInt($("#header-countdown").data("expiration-time"));
		var currentTime = Math.floor(Date.now() / 1000);
		var timeLeft = Math.max(expirationTime - currentTime, 0);
		var timeLeftString = new Date(1000 * timeLeft).toISOString().substr(11, 8);	
		$("#header-countdown").html(timeLeftString);
		
	}, 1000);
}
