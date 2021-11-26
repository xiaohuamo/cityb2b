/*
         ╭──╮╭──╮╭─╮  ╭─╮  TM
         │╭─╯│╭╮│╰╮│  ╰╮│ 
         │╰─╮│╰╯│  ││    ││  
         ╰─╮││╭╮│  ││    ││  
         ╭─╯││╰╯│╭╯╰╮╭╯╰╮
         ╰──╯╰──╯╰──╯╰──╯
                         
      Web: www.5811.com.cn  Tel: 4000-94-5811
  
*/

$(function(){
  //动画效果弹出菜单
	$(".MENUS li").hover(function(){           
		$(this).addClass("ok").find("dl").stop(true,true).slideUp(0).slideDown(300);
	},function(){
    $(".ok").find("dl").stop(true,true).slideUp(200);
		$(this).removeClass("ok");
	});
  
  //无动画弹出菜单
	$(".MENU li,.MENU_MY").hover(function(){           
		$(this).addClass("ok");
	},function(){
		$(this).removeClass("ok");
	});
  
  //当前显示导航
      $('.HOTTOP li').mouseover(function(){
          $(this).parent('ul').find('.ok').removeClass('ok');
          $(this).addClass('ok');
          if(!$(this).next('li').text()){
              $(this).addClass('end');
          }
      });
      
  // 折叠菜单   
  $('.SLIDE h3').click(function(){
      var $nn=$(this).hasClass('ok');
      if($nn){
           $(this).removeClass('ok');
           $(this).next('ul').addClass('dn');
      }
      else
      {
           $(this).addClass('ok');
           $(this).next('ul').removeClass('dn');
      }
  });

	$(".SLIDE h3").hover(function(){
		$(this).addClass("ok_bg");
	},function(){
		$(this).removeClass("ok_bg");
	});

// 动画折叠菜单

      $('.SLIDES h3').mouseover(function(){
          qq=$(this).hasClass('ok');
          if(!qq) $('.SLIDES .ok').removeClass('ok').next('ul').stop(true,true).hide(600,"linear");
          if(!qq) $(this).addClass('ok').next('ul').show(600,"linear");

      });

     //折叠菜单
			$('.SLIDES2 li').mouseover(function(){
          if(!$(this).hasClass('hover')){
              $(this).parent('ul').find('.hover').removeClass('hover').find('.s2').slideUp(300);
              $(this).addClass('hover').find('.s2').stop(true).slideDown(300);
          }

			});
  
  // 选项卡 鼠标经过切换
  $(".TAB li").mousemove(function(){
    var tab=$(this).parent(".TAB");
    var con=tab.attr("id");
    var on=tab.find("li").index(this);
    $(this).addClass('hover').siblings(tab.find("li")).removeClass('hover');
    $(con).eq(on).show().siblings(con).hide();
  });

  // 选项卡 鼠标点击
  $(".TAB_CLICK li").click(function(){
    var tab=$(this).parent(".TAB_CLICK");
    var con=tab.attr("id");
    var on=tab.find("li").index(this);
    $(this).addClass('hover').siblings(tab.find("li")).removeClass('hover');
    $(con).eq(on).show().siblings(con).hide();
  });

  // 滚动到指定位置
    $('.GOTO a').click(function(){
        hh=$(this).attr('rel')*1;
        id=$(this).attr('href');
        num=$(id).offset().top;
        if(hh) num=num+hh;
        $("html,body").animate({scrollTop: num}, 1000);
        return false;
    });

// 鼠标经过添加删除class
  $('.hover-class').hover(function(){           
    c=$(this).attr('rel');
    $(this).toggleClass(c);
  }); 

//鼠标点击添加删除class
  $('.click-class').click(function(){           
    c=$(this).attr('rel');
    $(this).toggleClass(c);
    return false;
  }); 

});