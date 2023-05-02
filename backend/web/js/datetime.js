$(document).ready(function(){

    var form = $('.datetime-widget').closest('form');
    const startDate = $('#study-start_date-date');
    const endDate = $('#study-end_date-date');

    // update model value on date or time value change
    $('.datetime-widget').on('change', '.date, .time', function(){

        var widget = $(this).closest('.datetime-widget');
        var name = widget.data('name');
        var className = widget.data('class');

        // select date and time
        var date = widget.find('#' + className + '-' + name + '-date').val();
        var time = widget.find('#' + className + '-' + name + '-time').val();

        // a little validation check before updating model field
        var dateEntered = (date != null) && (date != '');
        var timeEntered = (time != 'null') && (time != '');

        // use date and time to make a datetime
        if (dateEntered && timeEntered){
            var datetime = date + ' ' +  time;
        } else if (dateEntered) {
            var datetime = date + ' 12:00 AM'; // if no time entered, assume 12:00 AM
        } else {
            var datetime = null;
        }

        // update hidden field with model value
        widget.find('.hidden-field input').attr('value', datetime);

        endDate.kvDatepicker('setStartDate', date);
        startDate.kvDatepicker('setEndDate', date);

    });

    form.on('beforeValidate', function (e, messages, deferreds) {

        var widgets = $(this).find('.datetime-widget');

        var validation = true;

        $(widgets).each(function (index, widget){
            var name = $(widget).data('name');
            var className = $(widget).data('class');

            var date = $(widget).find('#' + className + '-' + name + '-date').val();
            var time = $(widget).find('#' + className + '-' + name + '-time').val();

            var dateEntered = (date != null) && (date != '');
            var timeEntered = (time != 'null') && (time != '');

/*
            // validation for time entered
            if (dateEntered && !timeEntered){
                $(widget).find('.hidden-field .form-group').addClass('has-error');
                $(widget).find('.hidden-field .help-block').text('Please select a time.');

                showDateTimeErrors();
                validation = false;
            }
*/
        });

        return validation;

    });

    // pass on required input errors from hidden field to separate date and time display
    form.on('afterValidate', function (){
        showDateTimeErrors();
    });

    function showDateTimeErrors ()
    {
        // get all widgets with errors
        var widgetsWithErrors = $('.hidden-field .has-error').closest('.datetime-widget');

        // loop through and change style of displayed fields
        $.each(widgetsWithErrors, function(index, widget){

            var displayedField = $(widget).find('.displayed-field');

            var errorMsg = $(widget).find('.hidden-field .help-block');

            $(displayedField).addClass('hasError');
            // $(displayedField).find('label').css('color', '#a94442');
            $(displayedField).find('.input-group').addClass('has-error');
            // $(displayedField).find('select').css('border-color', '#a94442');
            $(displayedField).find('.row').after(errorMsg);
        });
    }

    showDateTimeErrors();

    const isNumericInput = (event) => {
        const key = event.keyCode;
        return ((key >= 48 && key <= 57) || (key >= 96 && key <= 105) 
        );
    };
    
    const isModifierKey = (event) => {
        const key = event.keyCode;
        return (event.shiftKey === true || key === 35 || key === 36) || 
            (key === 8 || key === 9 || key === 13 || key === 46) || 
            (key > 36 && key < 41) || 
            (
                
                (event.ctrlKey === true || event.metaKey === true) &&
                (key === 65 || key === 67 || key === 86 || key === 88 || key === 90)
            )
    };
    
    const enforceFormat = (event) => {
        
        if(!isNumericInput(event) && !isModifierKey(event)){
            event.preventDefault();
        }
    };

    const formatDate = (event) => {
        if(isModifierKey(event)) {return;}
    

        const target = event.target;
        const input = target.value.replace(/\D/g,'').substring(0,6);
        const month = input.substring(0,2);
        const day = input.substring(2,4);
        const year = input.substring(4,6);
    
        if(input.length > 4){target.value = `${month}/${day}/${year}`;}
        else if(input.length > 2){target.value = `${month}/${day}/`;}
        else if(input.length > 0){target.value = `${month}/`;}


    };
    
    startDate.on('keydown',enforceFormat);
    startDate.on('keyup',formatDate);

    endDate.on('keydown',enforceFormat);
    endDate.on('keyup',formatDate);

});
