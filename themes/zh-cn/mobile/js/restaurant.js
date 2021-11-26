function updateInpageShoppingCart() {
    $.ajax({
        url: "/query?cmd=get_cart_item_ajax&businessUserId=<{$coupon.createUserId}>",
        type: "GET",
        beforeSend: function () {
            $('body').append('<p class="form_response_loader"></p>');
        },

    }).done(function (data) {
        $('.inpageShoppingCart').html(data);
        $('.inpageShoppingCart').show();
    }).always(function () {
        $('.form_response_loader').remove();
    })
}


function addMenuWithOption(obj, businessUserId) {

    var qtyCheck = 0;
    $(obj).parents('.menu_guige').find('.menu_option input').each(function (index, value) {
        qtyCheck += parseInt($(value).val());
    })
    if (qtyCheck == 0 && $(obj).parents('.menu_guige').find('.menu_option input').size() != 0) {
        alert('请至少选择一种规格');
        return false;
    }

    var postData = [];

    var totalPirce = 0;
    $(obj).parents('.menu_guige').find('input').each(function (index, value) {
        if (parseInt($(value).val()) > 0) {
            var singleData = {};

            singleData['main_coupon_id'] = $(value).data('main_coupon_id');
            singleData['menu_id'] = $(value).data('menu_id');


            var type = $(value).data('type');
            var price = $(value).data('price');

            if (type == 'menu_option') {
                var id = $(value).data('menu_option_id');
            } else if (type == 'sidedish_menu') {
                var id = $(value).data('sidedish_menu_id');
            } else {
                var id = null;
            }

            //console.log('type='+type +';id='+id+';price='+price);
            singleData['type'] = type;
            singleData['id'] = id;
            singleData['price'] = price;
            singleData['quantity'] = $(value).val();

            postData.push(singleData);

            totalPirce += singleData['quantity'] * singleData['price'];

            //reset to 0
            $(value).val(0);
        }
    })

    $.ajax({
        type: 'POST',
        url: '/query?cmd=restaurant_add_menu_with_option',
        data: {data: postData},
        beforeSend: function () {

        }
    }).done(function (data) {
        updateCartDisplay(data, businessUserId);
    });


    $(obj).parents('.menu_guige').hide();
}

function updateShoppingCart(target, businessUserId) {

    var type = $target.data('type');
    // alert(type);
    switch (type) {
        case 'voucher':
            //console.log('this is '+ type);

            var main_coupon_id = $target.data('main_coupon_id');
            var sub_coupon_id = $target.data('sub_coupon_id');
            var quantity = $target.val();


            var postData = {};
            postData['type'] = type;
            postData['main_coupon_id'] = main_coupon_id;
            postData['sub_coupon_id'] = sub_coupon_id;
            postData['quantity'] = quantity;

            $.ajax({
                type: 'POST',
                url: '/query?cmd=restaurant_menu_add_carts',
                data: postData,
                beforeSend: function () {

                }
            }).done(function (data) {

                updateCartDisplay(data, businessUserId);
            });

            break;

        case 'menu':


            var main_coupon_id = $target.data('main_coupon_id');
            var menu_id = $target.data('menu_id');
            var onspecial = $target.data('onspecial');
            var quantity = $target.val();
            //console.log('this is '+ onspecial);

            var postData = {};
            postData['type'] = type;
            postData['main_coupon_id'] = main_coupon_id;
            postData['menu_id'] = menu_id;
            postData['quantity'] = quantity;
            postData['onspecial'] = onspecial;

            $.ajax({
                type: 'POST',
                url: '/query?cmd=restaurant_menu_add_carts',
                data: postData,
                beforeSend: function () {

                }
            }).done(function (data) {

                if (!updateCartDisplay(data, businessUserId)) {

                    $target.val(parseInt($target.val()) - 1);
                }

            });

            if (quantity == 0) {
                $target.parent('.quantity').next('.sidedish_menu').find('.input-quantity').val(0);
            }
            break;
    }


}

function updateCartDisplay(data, businessUserId) {
    var aa = JSON.parse(data);

    if (aa['msg']) {
        if (aa['msg']['code'] == '0') {
            // alert('ddd');
            window.location.href = "/member/login?returnUrl=" + encodeURIComponent("/restaurant2/" + businessUserId + "?id=" + businessUserId);
            return 0;
        } else {
            alert(aa['msg']['error']);
            return 0;
        }
    }

    if (!data) {
        // alert('updateCartDisplay');
        $.ajax({
            url: "/query?cmd=cart_info&businessUserId=" + businessUserId,
            beforeSend: function () {
                $('body').append('<p class="form_response_loader"></p>');
            }
        }).done(function (d) {
            try {
                data = JSON.parse(d);
            } catch (err) {
                return false;
            }

            $('.inpageShoppingCart').html(data['html']);
            $('#carts_count_id').html(data['totalQuantity']);
            $('#voucher_totalprice').html(data['totalVoucherPrice'].toFixed(2));
            $('#carts_totalprice').html(data['totalMenuPrice'].toFixed(2));

            $('#tp').html("$" + (parseFloat(data['totalVoucherPrice']) + parseFloat(data['totalMenuPrice'])).toFixed(2));
        }).always(function () {
            $('.form_response_loader').remove();
        });
    } else {

        try {
            data = JSON.parse(data);
            //alert('here'+data);
        } catch (err) {
            return false;
        }
        //alert(data['html']);
        $('.inpageShoppingCart').html(data['html']);
        $('#carts_count_id').html(data['totalQuantity']);
        $('#voucher_totalprice').html(data['totalVoucherPrice'].toFixed(2));
        $('#carts_totalprice').html(data['totalMenuPrice'].toFixed(2));
        $('#tp').html("$" + (parseFloat(data['totalVoucherPrice']) + parseFloat(data['totalMenuPrice'])).toFixed(2));
    }
    return 1;

}

function fix_bar_position(aa) {
    var top = $('.col-l').position().top;
    var top1 = $('#content01').position().top;

    var menu_height = $('#box-prod').height();


    var header_height = $('#head-business-box').height();
    var before = $(window).scrollTop();
    var bottom_top = top + menu_height - $(window).height() - $('#foot_menu').height() - 10;

    $(window).scroll(function () {
        if ($(window).scrollTop() > (header_height - top)) {
            //scrolltop 表示目前当前屏幕距离最开始向下滚动的像素值，比如鼠标展示下面的屏幕，这个值越来越大
            // top 是指当前上面的菜单调距离顶部的距离
            // bottom_top : 也就是可以滚动的距离 （他是主区扣掉底部菜单高度，并扣掉当前屏幕高度（这部分已经显示，无需滚动））。

            //	$('.col-l').removeClass('fixed'); //当屏幕滚动距离小于固定条时，保持fix ,否则解除fix .
            // $('.col-l').css("margin-top",header_height);
        } else {
            // $('.col-l').css("margin-top",0);
            $('.col-l').addClass('fixed');
        }
        /*

            if($(window).scrollTop()<(header_height+top)){
                $('#head-business-box').css('opacity','0.8');
            }else{
                $('#head-business-box').css('opacity','0.1');
            }  */

        if (window.click_category == 0) {
            var windowTop = $(window).scrollTop();

            var after = $(window).scrollTop();


            if (before < after || after < 200) {
                //console.log('上');
                before = after;
                //alert(window.screen.height);
                windowTop = windowTop + window.screen.height - 60;  //这个时计算当前屏幕最底部可见区域的 高度像素值
            }
            ;
            if (before > after) {
                // console.log('下');
                before = after;


            }
            ;

            var target;
            var categoryIndex = 0;
            $('.positionPin' + current_business_id).each(function (index, value) {
                var elementTop = $(value).offset().top;  //这个是 菜单里面的每一个品类显示的位置。 （也就是距顶端的距离）
                if (windowTop > elementTop - 100) {
                    target = $(value).data('target');
                    categoryIndex = index;
                }
            })
            $('a.s1').css('background', '');
            $('a.s1[data-target-id=' + target + ']').css('background', '#5bc0de');
            if (categoryIndex > 5) {
               $("#leftnav" + current_business_id).scrollTop(500);
            } else {
                $("#leftnav" + current_business_id).scrollTop(0);
            }
        }
    })

}