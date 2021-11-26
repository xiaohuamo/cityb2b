$(document).ready(function($) {
    
    // 返回顶部
    $('.goTop').click(function(){
        $('body,html').animate({
            'scrollTop':0
        }, 500);
    });
    // $(window).scroll(function(){
    //     var _top = $(window).scrollTop();
    //     if( _top<100 ){
    //         $('.goTop').stop().fadeOut();
    //     }else{
    //         $('.goTop').stop().fadeIn();
    //     }
    // });

    // 选项卡 鼠标点击
    $(".TAB_CLICK li").click(function(){
      var tab=$(this).parent(".TAB_CLICK");
      var con=tab.attr("id");
      var on=tab.find("li").index(this);
      $(this).addClass('on').siblings(tab.find("li")).removeClass('on');
      $(con).eq(on).show().siblings(con).hide();
    });
    $(".TAB_CLICK li").filter(':first').trigger('click');
});