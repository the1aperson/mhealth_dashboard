$(document).ready(function(){
	$("#export-submit-button").click(function(){
		var scope = document.querySelector('input[name=export-scope]:checked').value;
		var format = document.querySelector('input[name=export-format]:checked').value; 
		var queryParams = {}
		queryParams["scope"] = scope;
		queryParams["format"] = format;
		
		if(scope == "filter")
		{
			var existingFilters = yii.getQueryParams(location.search);
			queryParams = Object.assign({}, queryParams, existingFilters);
		}
	
		$("#export-data-modal .export-modal-additional-param").each(function(){
			var key = $(this).attr("name");
			var val = $(this).val();
			queryParams[key] = val;
		});
		
		var url = $(this).data("url");
		
		$.get(url, queryParams, function(result){
			if(result["success"])
			{
				if(result["url"] != undefined)
				{
					location.href = result["url"];
				}
			}
			else
			{
				if(result["error_msg"] != undefined)
				{
					$('#export-data-modal .form-error').html(result["error_msg"]);
				}
			}
		});
		
		
	});
	
	$("#export-data-modal").on("hidden.bs.modal", function(){
		$("#export-data-modal .form-error").html("");
	});
	
	$("#export-data-modal input").on("change", function(){
		$("#export-data-modal .form-error").html("");
	});

});