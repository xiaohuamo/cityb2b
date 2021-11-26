$(document).ready(function($) {
    // 选项卡 鼠标点击
    $(".TAB_CLICK li").click(function(){
      var tab=$(this).parent(".TAB_CLICK");
      var con=tab.attr("id");
      var on=tab.find("li").index(this);
      $(this).addClass('on').siblings(tab.find("li")).removeClass('on');
      $(con).eq(on).show().siblings(con).hide();
    });
    $(".TAB_CLICK li").filter(':first').trigger('click');
	// mobile导航
	$('.menuBtn').click(function(e) {
		$('body').toggleClass('fixme');
		$('#aside').toggleClass('open');
		e.stopPropagation();
	});
	$(document).on('click',function(){
		$('#aside').removeClass('open');
		$('body').removeClass('fixme');
	});
    $('#aside a').click(function(event) {
        /* Act on the event */
        $('#aside').removeClass('open');
        $('body').removeClass('fixme');
    });

    // pc导航
    $(window).scroll(function(event) {
        /* Act on the event */
        var _top = $(window).scrollTop();
        var h = $(window).height();
        var w = $(window).width();
        if(_top >= h && w >=960){
            $('#hd').hide();
            $('#nav').addClass('fixed').find('.logo').fadeIn();
        }else{
            $('#hd').fadeIn();
            $('#nav').removeClass('fixed').find('.logo').hide();
        }
    });
	$('.menu a').click(function(event) {
        /* Act on the event */
        $('body,html').animate({
            'scrollTop': $( $.attr(this, 'href') ).offset().top - 68
        }, 500);
        return false;
    });
});