/* 
//   cssClass:给倒计时写的样式
//   message: 当时间到达给定的时间时提示框消息,
//   miniteToAlert: 给定的消息提示时间分钟为单位,
//   callback: 回调函数，当倒计时完成时需要执行的函数
//   Copyright (C) 2012 易立方教育科技
//   版权所有。
*/
(function ($) {
    jQuery.fn.extend({
        countDown: function (opts) {
            opts = jQuery.extend({
                endDate: this.attr('endDate'),
                cssClass: "imgDiv",
                message: "",
                miniteToAlert: 5,
                callback: function () { return false; }
            }, opts || {});
            var $this = $(this);
            $this.addClass(opts.cssClass);
            
            //计时功能
            var totalSecs, days, hours, mins, secs, date;
            var date1 = new Date(opts.endDate);
            var flag = true;

            var timer = setInterval(function () {
                date = new Date();
                if (date1- date  >= 0) {
                    totalSecs = (date1-date  ) / 1000;
                    days = Math.floor(totalSecs / 3600 / 24);
                    hours = Math.floor((totalSecs ) / 3600);
                    mins = Math.floor((totalSecs - hours * 3600) / 60);
                    secs = Math.floor((totalSecs - hours * 3600 - mins * 60));
                    if (flag && mins < opts.miniteToAlert && days == 0 && hours == 0) {
                        flag = false;
                    }
                    days = days < 10 ? "0" + days : days;
                    hours = hours < 10 ? "0" + hours : hours;
                    mins = mins < 10 ? "0" + mins : mins;
                    secs = secs < 10 ? "0" + secs : secs;

                    $this.html("");
                    $this.append('<em>'+hours+'</em>' + "<em>" + mins + "</em>" + '<em>'+ secs+'</em>');
                } else {
                    $this.html("");
                    $this.append("");
                    opts.callback();
                    clearInterval(timer);
                }
            }, 1000);
        }
    });
})(jQuery);