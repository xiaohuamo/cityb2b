
$(document).ready(function($) {
	
	// 手机导航
	$('.menuBtn').append('<b></b><b></b><b></b>');
	$('.menuBtn').click(function(event) {
		$(this).toggleClass('open');
		var _winw = $(window).width();
		var _winh = $(window).height();
		if( $(this).hasClass('open') ){
			$('body').addClass('open');
			if( _winw<=992 ){
				$('.soBox').stop().slideDown();
				$('#menu').stop().slideDown();
			}
		}else{
			$('body').removeClass('open');
			if( _winw<=992 ){
				$('.soBox').stop().slideUp();
				$('#menu').stop().slideUp();
			}
		}
	});

    // 弹出框
    $('.myfancy').click(function(){
        var _id = $(this).attr('href');
        $(_id).fadeIn();
        $(_id).find(".pop-bg").fadeIn();
    });
    $('.pop-bg,.close,.confirmBtn').click(function(){
        $(this).parents('.m-pop').fadeOut();
        $(".pop-bg").fadeOut();
    });



	
	
});