$(document).ready(function(){
	
	$(".alert-item .alert-item-hide").click(function(e){
		
		var url = $(this).data('url');
		var target = $(this).data('target');
		$(target).hide();
		$.post(url, function(data, status, xhr){
			if(data["success"] == true)
			{
				
			}
		});
	});	
	
	$(".alert-follow-up-modal").on("pjax:success", function(e){
		
		var alert_id = $(this).data("alert-id");
		var parent = $("#alert-item-id-" + alert_id).parent("[data-pjax-container]");
		if(parent.length == 0)
		{
			return;
		}
		
		var url = parent.eq(0).data("url");
		var parent_id = parent.attr("id");
		var options = {"url":url, "container":"#" + parent_id, history: false, push: false};
		$.pjax(options);
		
	});
});