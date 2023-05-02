$(document).ready(function(){
	$('#study-settings-modal').on('hidden.bs.modal', function (e) {
		$(this).find(".collapse").removeClass("in");
		$(this).find("#study-settings-main").addClass("in").css("height", '');
	})
		
	
	$("#remove-participant").click(function(){

		$(this).attr("disabled", "disabled");
		var url = $(this).data("url");
		var password = $('#study-settings-remove-password').val();
		
		if(password == "")
		{
			$('#study-settings-remove-password').parent('.form-group').addClass('has-error');
			return;
		}
		$.post(url, {'password': password}, function(data){
			$('#study-settings-remove-password').val('');
			$("#remove-participant").attr("disabled", null);
			if(data["success"] == true)
			{
				var url = data["redirect"];
				if(url)
				{
					window.location.replace(url);
					return;
				}
				$("#study-settings-confirm-remove").collapse('hide');
				$("#study-settings-success").collapse('show');
			}
			else
			{
				$('#study-settings-remove-password').parent('.form-group').addClass('has-error');
			}
		} )

	});
	
	$('.study-settings-password').focus(function(){
		$(this).parent('.form-group').removeClass('has-error');
	});
});