$(document).ready(function(){
	
	$("#user-form-add-study").click(function(){
		var index = $(".user-role-item").length;
		//Keeps user from adding another study until one is selected
		var select_study = $("#studyuserauth-"+ (index-1) +"-study_id option:selected");
		if ((select_study).val() == ''){
			return;
		}	
		$.post($(this).data("url"), {"index": index}, function(response){
		
			$("#user-roles").append(response);
			
			$('.user-role-item').each(function(i){
				var len = $('.user-role-item').length;
				var first = $(".user-role-item").first()
				for(var i=0; i<len; i++){
					if(i == 0){
							first.addClass('first-role');
							$(".first-role .user-role-item-remove").css('display', 'none');
							$(".first-role .role-compare").css('display', 'block');
						} else if ($("#user-roles").find(".user-role-item").eq(i)) {				
							$("#user-roles").find(".user-role-item").eq(i).addClass('next-role');
								$(".next-role .user-role-item-remove").css('display', 'inline-block');
								$(".next-role .role-compare").css('display', 'none');
						}
				}
			})
			hideStudies();
		}, 'html');			
	});
	
	$("body").on("click", ".user-role-item-remove", function(){
		$('#studyuserauth-0-study_id option').show();
		$(this).parents(".user-role-item").remove();
	});


	//Show/hide Add Another Study when "All Studies" Study is selected
	$(function() { allStudiesOption(); });
		function allStudiesOption(){
			var first_study_val = $("#studyuserauth-0-study_id").val();
			if(first_study_val == '-99'){
				$('#add-study').hide();		
			} else{
				$('#add-study').show();
			}
		};

		var first_study = $("#studyuserauth-0-study_id");
		first_study.on('change',function(){
			allStudiesOption();
		});

	//Hide Study already selected and "All Studies" 
	// $('.select-studies').on('change',function(){
	// 	if($('.select-studies option[value="'+ $(this).val() +'"]:selected').length > 1){
	// 		$(this).val('-1').change();
	// 	}
	// })


	$(function() { hideStudies(); });
		function hideStudies(){
			$('.select-studies option').show();
			$('.select-studies option:selected').each(function(){
				var optionSelected = this;
				var studyValue = $(this).val();
				$('option[value="'+ studyValue +'"]').each(function(){
					if(this != optionSelected){
						$(this).hide();
						$('.select-studies option[value=""').show();
					}
				})
			})
			var index = $(".user-role-item").length;
			for(i=1;i<index;i++){
				$("#studyuserauth-"+ i +"-study_id option[value='-99']").hide();
			}		
		}
	
});