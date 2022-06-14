//disable submit on enter
$(document).on("keypress", "#memberForm,.memberForm", function(event) { return event.keyCode != 13;});

// this is attached to the end of each form to handle response
$('#memberForm,.memberForm').submit(function(){
	var _form = $(this);
	_form.find(':submit').attr('disabled', true);

	$('.form_response_alert').remove();
	_form.before('<p class="form_response_loader"></p>');

	$('#post_frame').unbind('load').load(function(){

		$('.form_response_alert').remove();
		_form.find(':submit').attr('disabled', false);

		var result = $(this).contents().find('body').html();
		var data=[];
		try{
			data = JSON.parse(result);
		}catch(err){
			data['msg'] = result;
		}

		var further_action_delay=0;
		//Display message
		if(data.msg){
			$('.form_response_loader').remove();
			if(data.redirect){
				data.msg=data.msg+" ...";
			}
			_form.before('<div class="form_response_alert">'+ data.msg +'</div>');
			further_action_delay=2000;
		}

		//Further action
		if (data.status==200) {
			
			$('.quantity').val('');
			$('.form_response_alert').on('click',function(){
				$(this).remove();
			}).delay(2000).fadeOut(500);
		}else{
			//alert('300');
			$('.form_response_alert').on('click',function(){
				$(this).remove();
			}).delay(2500).fadeOut(500);
		}
	});
});
