$(document).ready(function() {
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
});