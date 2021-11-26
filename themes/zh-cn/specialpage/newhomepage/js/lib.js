$(document).ready(function($) {
    // 搜索框分类选择
    $('#sel-cat .selected').click(function(event) {
        /* Act on the event */
        $(this).next('.box-item').stop().slideToggle();
    });
    $('#sel-cat .box-item a').click(function(event) {
        /* Act on the event */
        var txt = $(this).html();
        $('.hd-sel').val(txt);
        $('#sel-cat .selected').html(txt);
        $(this).parent().slideUp();
    });
    // 导航分类
    $('.cont-nav-cat .selected').click(function(event) {
        /* Act on the event */
        $(this).next('.box-item').stop().slideToggle();
    });
});