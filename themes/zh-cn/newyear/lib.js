$(document).ready(function() {
    // 左侧浮动菜单
    var lastId,
        topMenu = $(".default-outer"),
        topMenuHeight = topMenu.outerHeight()+0,
        menuItems = topMenu.find(".floor a"),
        scrollItems = menuItems.map(function(){
            var item = $($(this).attr("href"));
            if (item.length) { return item; }
        });
    function highlightNav() {
        var fromTop = $(window).scrollTop()+topMenuHeight;
        var cur = scrollItems.map(function(){ if ($(this).offset().top < fromTop) { return this; } });
        cur = cur[cur.length-1];
        var id = cur && cur.length ? cur[0].id : "";
        if (lastId !== id) {
            lastId = id;
            menuItems.removeClass("on").filter("[href=#"+id+"]").addClass("on");
        }
    }
    highlightNav();
    $(window).scroll(function(){
        highlightNav();
    });
    $('.default-outer .floor a').click(function(){
        $('html, body').animate({
            scrollTop: $( $.attr(this, 'href') ).offset().top-60
        }, 500);
        return false;
    });
});

$(window).scroll(function(){
    var _top = $(window).scrollTop();
    if( _top<50)
    {
        $('.default-outer').stop().hide();
    }
    else
    {
        $('.default-outer').stop().show();
    }

});

$(window).scroll(function(){
    var _top = $(window).scrollTop();
    if( _top<260)
    {
        $('.ul-nav2').stop().hide();
    }
    else
    {
        $('.ul-nav2').stop().show();
    }

});

// 头部下拉
$('.JS-select').hover(function() {
    $(this).find('.JS-select-con').stop().slideToggle('fast');
});

// 搜索键盘事件
$('.head-center .form .inp').keydown(function() {
    $('.search-list').fadeIn();
});
$('.head-center .form').mouseleave(function(){
    $('.search-list').fadeOut();
})

// 选择地址
$("#city-box1 dd").click(function(){
    var _txt = $(this).text();
    $(".xz-city p").text(_txt);
});
// 选项卡 鼠标点击
$(".TAB_CLICK li").click(function(){
    var tab=$(this).parent(".TAB_CLICK");
    var con=tab.attr("id");
    var on=tab.find("li").index(this);
    $(this).addClass('on').siblings(tab.find("li")).removeClass('on');
    $(con).eq(on).show().siblings(con).hide();
});
$(".TAB li").hover(function () {
    var tab = $(this).parent(".TAB");
    var con = tab.attr("id");
    var on = tab.find("li").index(this);
    $(this).addClass('ok').siblings(tab.find("li")).removeClass('ok');
    $(con).eq(on).show().siblings(con).hide();
});
//底部导航
$(".fd-nav dl:last-child").addClass('last');
//详细详情页-代金卡 替换图片
$(".slide-lb-1 li").hover(function() {
    var a = $(this).find("img").attr("src");
    console.log(a);
    $(".pic-move img").attr('src', a);
}, function() {
    return true;

});
//锚点
var $root = $('html, body');
$('.ul-anchor-lb li a').click(function() {
    $(this).addClass('on').parent("li").siblings('li').find("a").removeClass('on');
    $root.animate({
        scrollTop: $( $.attr(this, 'href') ).offset().top
    }, 500);
    return false;
});
$(window).scroll(function(){
    var _top = $(window).scrollTop();
    if( _top>300 ){
        $('.ul-anchor-lb').addClass('fixed');
        $('.m-detail-lb').css('padding-top', '50px');
    }else{
        $('.ul-anchor-lb').removeClass('fixed');
        $('.m-detail-lb').css('padding-top', '0');
    }
});
//锚点自动跟随
var lastId,
    topMenu = $(".ul-anchor-lb"),
    topMenuHeight = topMenu.outerHeight()+0,
    menuItems = topMenu.find("li a"),
    scrollItems = menuItems.map(function(){
        var item = $($(this).attr("href"));
        if (item.length) { return item; }
    });
function highlightNav() {
    var fromTop = $(window).scrollTop()+topMenuHeight;
    var cur = scrollItems.map(function(){ if ($(this).offset().top < fromTop) { return this; } });
    cur = cur[cur.length-1];
    var id = cur && cur.length ? cur[0].id : "";
    if (lastId !== id) {
        lastId = id;
        menuItems.removeClass("on").filter("[href=#"+id+"]").addClass("on");
    }
}
highlightNav();
$(window).scroll(function(){
    highlightNav();
});
//留言
$(".m-cont-lb textarea").focus(function(event) {
    $(this).siblings('p').addClass('on');
});
$(".m-cont-lb textarea").focusout(function(event) {
    $(this).siblings('p').removeClass('on');
});
//数量变化
$(".g-num .add").click(function(event) {
    var num = $(this).siblings('.num').attr("value");
    var num2 = parseInt(num);
    $(this).siblings('.num').attr("value",num2+1);
});
$(".g-num .min").click(function(event) {
    var num = $(this).siblings('.num').attr('value');
    var num2 = parseInt(num);
    if(num2>1){
        $(this).siblings('.num').attr("value",num2-1);
    }

});
//购物车
$(".js-shop1").click(function(event) {
    $(this).toggleClass('ok');
    $(this).parents("tr").siblings('tr').find(".js-shop2").toggleClass('ok');
});
$(".js-shop2").click(function(event) {
    $(this).toggleClass('ok');
});
$(".js-shop3").click(function(event) {
    $(this).toggleClass('ok');
    $(".js-shop2").toggleClass('ok');
    $(".js-shop1").toggleClass('ok');
});
if($(".js-shop2").hasClass('ok')){
    $(".js-shop2").parents("tr").siblings('tr').find(".js-shop1").addClass('ok')
}else{
    $(".js-shop2").parents("tr").siblings('tr').find(".js-shop1").removeClass('ok')
}
//订单确认
$(".ul-pay li").click(function(event) {
    $(this).addClass('on').siblings('li').removeClass('on');
});
$(".take-me .me").click(function(event) {
    $(this).addClass('on');
});
//订单确认弹窗
$(".addr-close").click(function(event) {
    $(".addr-mask").fadeOut(300);
    $(".new-addr").fadeOut(300);
});
$(".addr-open").click(function(event) {
    $(".addr-mask").fadeIn(300);
    $(".new-addr").fadeIn(300);
});
$(".addr-mask").click(function(event) {
    $(this).fadeOut(300);
    $(".new-addr").fadeOut(300);
    $(".enter-float").fadeOut(300);

});
// 登录弹窗
$(".js-enter").click(function(event) {
    $(".enter-float").fadeIn(300);
    $(".addr-mask").fadeIn(300);
});


$(window).scroll(function() {
    if($(window).scrollTop() >= 720){
        $(".m-nav2").addClass('float');
        $("body").addClass('float');
    }else{
        $(".m-nav2").removeClass('float');
        $("body").removeClass('float');
    }
});