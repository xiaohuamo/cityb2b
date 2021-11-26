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


  
  // 选项卡 鼠标经过切换
  // $(".TAB li").mousemove(function(){
  //   var tab=$(this).parent(".TAB");
  //   var con=tab.attr("id");
  //   var on=tab.find("li").index(this);
  //   $(this).addClass('hover').siblings(tab.find("li")).removeClass('hover');
  //   $(con).eq(on).show().siblings(con).hide();
  // });

  // 选项卡 鼠标点击
  $(".TAB_CLICK li").click(function(){
    var tab=$(this).parent(".TAB_CLICK");
    var con=tab.attr("id");
    var on=tab.find("li").index(this);
    $(this).addClass('on').siblings(tab.find("li")).removeClass('on');
    $(con).eq(on).show().siblings(con).hide();
  });


});