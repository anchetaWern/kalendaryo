var attendee_template = Handlebars.compile($('#attendee-template').html());

$('.datetimepicker').datetimepicker();

$('#attendees').on('blur', '.email', function(){

	var attendee_row = $('.attendee-row:last');
	var name = attendee_row.find('.name').val();
	var email = attendee_row.find('.email').val();

	if(name && email){
		$('#attendees').append(attendee_template());
	}
});