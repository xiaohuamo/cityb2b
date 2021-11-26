function openbox(url, classname, name) {
	var h = jQuery('.' + classname).height();
	jQuery('#boxname').html(name);
	var wheight = jQuery(window).height();
	var display = jQuery('.' + classname).css('display');
	if (display == 'none') {
		jQuery('.' + classname).css('height', wheight - 20 + 'px');
		//jQuery.get(url, function (htmls) {
		//	jQuery('#boxlist').html(htmls);
		//});
		jQuery('.' + classname).show();
		var $ul = jQuery('#boxlist>ul');
		$ul.css('height', $ul.parent().parent().parent().height() - 140 + 'px');
	} else {
		jQuery('.' + classname).css('height', '');
		jQuery('.' + classname).hide();
	}
}

jQuery(function(){
	var $ = jQuery;
	/*侧栏*/
	$('.JS-menu-btn').on('mouseover',function(){
		$(this).find('a').attr('class','btn-a-on');
		$(this).find('.slide-txt').show();
		$(this).find('.side-qcode').show();
		$(this).find('.slide-txt').stop().animate({
			right : '35px',
			filter : 'alpha(opacity=100)', 
			opacity: '1'
		},100)
	})
	$('.JS-menu-btn').on('mouseout',function(){
		$(this).find('a').attr('class','btn-a');
		$(this).find('.slide-txt').hide();
		$(this).find('.side-qcode').hide();
		$(this).find('.slide-txt').stop().animate({
			right : '65px',
			filter : 'alpha(opacity=0)', 
			opacity: '0'
		},100)
	})
	$('.JS-menu-btn').on('click',function(){
		$(this).find('a').attr('class','btn-a');
		$('.JS-hide-box').hide();
		$(this).parent().find('.JS-hide-box').show();
		$(this).find('.slide-txt').hide();
		$(this).find('.slide-txt').stop().animate({
			right : '65px',
			filter : 'alpha(opacity=0)', 
			opacity: '0'
		},100)
	});

	function hoverevent(hobjname, hobjclass, target) {
		hobjname.on('mouseenter', function() {
			$(target || this).addClass(hobjclass);
		});
		hobjname.on('mouseleave', function() {
			$(target || this).removeClass(hobjclass);
		})
	};
	hoverevent($('#dropnavin'), 'dHover');
	hoverevent($('.menu_btn'), 'dHover');
	hoverevent($('.wtc'), 'dHover');
	hoverevent($('.wxtc'), 'dHover');
	hoverevent($('.dwx'), 'dHover');
	hoverevent($('.checkoutBtn .dTitle .fr'), 'cHover', '.checkoutBtn');
	hoverevent($('.checkoutBtn dd'), 'cHover', '.checkoutBtn');

	(function(){
		var $win = $(window),
			$rtt = $('#rtt');
		$rtt.click(function(){
			var scroll = function (){
				st = Math.floor(st / 2);
				window.scroll(0, st);
				st > 2 ? t =setTimeout(scroll, 40) : (window.scrollTo(0, 0), clearTimeout(t));
			}, st = $win.scrollTop(), t = null;
			$.browser.msie ? window.scrollTo(0, 0) : scroll();
		});
		var t, f = false;
		$win.bind('scroll', function(){
			t && clearTimeout(t);
			t = setTimeout(function(){
				if($win.scrollTop() > 0){
					if(!f){
						$rtt.css('visibility', 'visible');
						f = true;
					}
				}else{
					if(f){
						$rtt.css('visibility', 'hidden');
						f = false;
					}
				}
			}, 500);
		});
	})();
});


$('.icon-top').click(function(){ 
   $('html, body').animate({scrollTop:0}, 'slow'); 
});


