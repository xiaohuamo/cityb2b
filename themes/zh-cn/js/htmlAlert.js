// Jquery Required
// Html alert box
// also have a htmlAlert.css
function htmlConfirm(msg,yesCallback,noCallback){
	var html = "";
	html += "<div class='system-notification'>";
		html += "<div class='mask'></div>";
		html += "<div class='alert-box'>";
		html += "	<div class='header'>消息</div>";
		html += "	<div class='body'>"+msg+"</div>";
		html += "	<div class='btn-yes'>确认</div>";
		html += "	<div class='btn-no'>取消</div>";
		html += "</div>";
	html += "</div>";
	$('body').append(html);

	$('.system-notification .btn-yes').on('click',function(){
		if (typeof yesCallback === "function") {
		 	yesCallback();
		}
		removeAlert();

	});
	$('.system-notification .btn-no').on('click',function(){
		if (typeof noCallback === "function") {
		 	noCallback();
		}
		removeAlert();
	});
}

function htmlAlert(msg,closeCallback){
	var html = "";
	html += "<div class='system-notification'>";
		html += "<div class='mask'></div>";
		html += "<div class='alert-box'>";
		html += "	<div class='header'>消息</div>";
		html += "	<div class='body'>"+msg+"</div>";
		html += "	<div class='btn-yes only'>确认</div>";
		html += "</div>";
	html += "</div>";
	$('body').append(html);

	$('.system-notification .btn-yes').on('click',function(){
			if (typeof closeCallback === "function") {
			 	closeCallback();
			}
			removeAlert();
	});

	//AutoDismiss
	setTimeout(function(){
		 $('.system-notification .btn-yes').click();
	},2000)
}

function htmlMessage(msg,callback) {
	
	
	var html ='<div class="form_response_alert" style="font-size:16px;">'+ msg +'</div>';
	$('body').append(html);

	$('.form_response_alert').on('click',function(){
		$(this).remove();
		callback();
	}).delay(3000).fadeOut(300,function(){callback();});
}


function htmlMessage1(msg,callback) {
	
	
	var html ='<div class="form_response_alert" style="font-size:16px;">'+ msg +'</div>';
	$('body').append(html);

	$('.form_response_alert').on('click',function(){
		$(this).remove();
		callback();
	}).delay(500).fadeOut(1000,function(){callback();});
}

function removeAlert(){
	$('.system-notification').remove();
}