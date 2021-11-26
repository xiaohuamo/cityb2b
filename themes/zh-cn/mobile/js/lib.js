
$(window).load(function(){

});
$(document).ready(function($) {
	
	$('.js-on').click(function(event) {
		$(this).toggleClass('on');
		
		if($(this).hasClass('on')){
			$('.js-on-value').val(1);
		}else{
			$('.js-on-value').val(0);
		}
		
 	});
	$('.so-top-2 .sele').click(function(event) {
		$(this).next('.sub').show();
		event.stopPropagation();
 	});
	$('.so-top-2 .sub span').click(function(event) {
		var _txt = $(this).text();
		$(this).parents('.sub').hide();
		$(this).parents('.so-top-2').find('.sele').val( _txt );
 	});
	$('body').click(function(){
		$('.so-top-2 .sub').hide();
	});

	$('body').click(function(){
		$(this).removeClass('open');
	});

	//点击搜索框弹出+消失
	$(".a-so").click(function(){
	    $("body").addClass('open-so');
	});
	$(".so-top-pop").click(function(){
	    $("body").addClass('open-so');
	});
	$(".box-so .a-txt").click(function(){
	    $("body").removeClass('open-so');
	});
	$('.box-so .so-top .inp').click(function(){
		$('.so-pop').show();
		$('.m-so').hide();
	});

    //公用弹窗
    $('.myfancy').click(function(){
        var _id = $(this).attr('href');
        $(_id).fadeIn();
        $(_id).find(".bg").fadeIn();
    });
    $('.m-pop .btn-close,.m-pop .bg,.m-pop .sub').click(function(){
        $(this).parents('.m-pop').fadeOut();
        $(".m-pop .bg").fadeOut();
    });

    // 选项卡 鼠标点击切换
    $(".TAB_CLICK li").click(function(){
      var tab=$(this).parent(".TAB_CLICK");
      var con=tab.attr("id");
      var on=tab.find("li").index(this);
      $(this).addClass('on').siblings(tab.find("li")).removeClass('on');
      $(con).eq(on).show().siblings(con).hide();
    });


	$('#g-top, .to-gp').click(function(){ 
	   $('html, body').animate({scrollTop:0}, 'slow'); 
	});

	$(window).on('scroll',function(){
		if($(window).scrollTop()>400){
			$('#g-top').show();
		}else{
			$('#g-top').hide();
		}
	})

});

!(function (doc, win) {    
    var docEl = doc.documentElement,    
    resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',    
    recalc = function () {    
    var clientWidth = docEl.clientWidth;    
    if (!clientWidth) return;    
    docEl.style.fontSize = 100 * (clientWidth / 1280) + 'px';    
};    
if (!doc.addEventListener) return;    
win.addEventListener(resizeEvt, recalc, false);    
doc.addEventListener('DOMContentLoaded', recalc, false);    
})(document, window);

$(function(){
	 $('.more-box a.more').click(function(event) {
        $('.more-box .box').stop().slideToggle();
    });
})

   

