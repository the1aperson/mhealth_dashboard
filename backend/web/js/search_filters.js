$(document).ready(function(){
	
	var startValue = $('#enrolled_start').val();
	var endValue = $('#enrolled_end').val();
	$('.choose-dates').show();
	$('.edit-dates').hide();

	if (startValue != '' || endValue != ''){
		$('.choose-dates').hide();
		$('.edit-dates').show();
		$('.edit-dates .start-date p').text(startValue);
		$('.edit-dates .end-date p').text(endValue);
	} 

	$(".search-filter-option").click(function(){
		
		var data = yii.getQueryParams(location.search);
		
		var newAttr = $(this).data("attribute");
		var newValue = $(this).data("value");
		
		data[newAttr] = newValue;
		submitFormData(data);
	});
	
	$(".search-filter-active-list-item").click(function(){
		
		var data = yii.getQueryParams(location.search);
		
		var attrToDelete = $(this).data("attribute");
		if (attrToDelete == 'enrolled_start' || attrToDelete == 'enrolled_end'){
			delete data['enrolled_start'];
			delete data['enrolled_end'];
			$('.choose-dates').show();
			$('.edit-dates').hide();
		}
		delete data[attrToDelete];
		submitFormData(data);
		
	});
	
	$("#enrolled_submit").click(function(){
		

		var data = yii.getQueryParams(location.search);

		var start = 'enrolled_start';
		var startValue = formatDate($('#enrolled_start').val());
		var end = 'enrolled_end';
		var endValue = formatDate($('#enrolled_end').val());
		
		data[start] = startValue;
		data[end] = endValue;
		$('.edit-dates .start-date p').text(startValue);
		$('.edit-dates .end-date p').text(endValue);
		$('.choose-dates').hide();
		$('.edit-dates').show();

		submitFormData(data);

	});

	function formatDate(date)
	{
		var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return month + '/' + day + '/' + year;
	}

	function submitFormData(data)
	{
		var $filterSection = $(".search-filter-section");
		// follow what yii.gridView.js does to setup and submit its form
		
		$('form.gridview-filter-form').remove();
		var url = location.origin + location.pathname;
		
        var $form = $('<form/>', {
            action: url,
            method: 'get',
            'class': 'gridview-filter-form',
            style: 'display:none',
            'data-pjax': ''
        }).appendTo($filterSection);
        
        $.each(data, function (name, value) {
            $form.append($('<input/>').attr({type: 'hidden', name: name, value: value}));
        });

        $form.submit();
	}
	
});

