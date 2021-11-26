

$(document).on('click', '.title-ul li', function () {
  let index = $(this).index()
  $(this).addClass('active').siblings().removeClass('active');
  $('.content-ul>li').eq(index).css('display', 'flex').siblings().hide()
})
$(document).on('click', '.left-ul>li', function () {
  let index = $(this).index();
  $(this).addClass('active').siblings().removeClass('active');
  $(this).parent().siblings().children().eq(index).addClass('right-li-active').siblings().removeClass('right-li-active')
})
$(document).on('click', '.lable>li', function () {
  let index = $(this).index();
  $(this).addClass('lable-active').siblings().removeClass('lable-active');
  $(this).parent().siblings('.commodity-details').eq(index).addClass('commodity-details-active').siblings('.commodity-details').removeClass('commodity-details-active')
})

var realTotalPrice = 0;
var oldTotalPrice = 0;
var spNum = 0;

// 点击日期
$(document).on('click','.date-select',function(){
	$('.delivery-date').css('top','0px');
})
$(document).on('click','.del-top-btn',function(){
	$('.delivery-date').css('top','-100%');
})
// 点击购物车弹出购物车商品详单
$(document).on('click','.cart-price',function(){
	$('.shopping-cart').css('bottom','0px');
	$('.mask').fadeIn(400);
})
$(document).on('click','.empty',function(){
	$('.shopping-cart').css('bottom','-90%');
	
	$('.mask').fadeOut(400);
	$('.commodity-con').remove();
	$('.fixed-buttom').removeClass('commod-yes');
	$('.fixed-buttom .other-price').addClass('other-price-no');
})
$(document).on('click','.mask',function(){
		
	$('.shopping-cart').css('bottom','-90%');
	$('.commodity-Det').css('bottom','-0%');
	$('.fixed-buttom').css('background','#F6F6F6');
	$('.mask').fadeOut(400);
})


// 点击信用卡购买声明·退货政策
$(document).on('click','.service-description',function(){
	$('.statement').css('bottom','0%');
})
// 点击信用卡购买声明·退货政策叉号
$(document).on('click','.statement-close',function(){
	$('.statement').css('bottom','-80%');
})


// 点击信用卡购买声明·退货政策
$(document).on('click','.companyDescription',function(){
	$('.statement_companyDescription').css('bottom','0%');
})
// 点击信用卡购买声明·退货政策叉号
$(document).on('click','.statement_companyDescription-close',function(){
	$('.statement_companyDescription').css('bottom','-80%');
})
// 点击信用卡购买声明·退货政策
$(document).on('click','.serviceSum-description',function(){
	$('.statementSum').css('bottom','0%');
})
// 点击信用卡购买声明·退货政策叉号
$(document).on('click','.statementSum-close',function(){
	$('.statementSum').css('bottom','-80%');
})





// 购物车点击减
$(document).on('click','.commodity-con>li .jian',function(){
	let num = parseInt($(this).parents('.list-num-out').find('.list-num').html())-1;
	if(num>0){
		$(this).parents('.list-num-out').find('.list-num').html(num);
	}else{
		$(this).parents('.commodity-list').remove();
		if($('.commodity-con li').length<=0){//购物车没有商品了
			$('.shopping-cart').css('bottom','-80%');
			$('.mask').fadeOut(400);
		}
	}
	
	
})
// 购物车点击加
$(document).on('click','.commodity-con>li .jia',function(){
	$(this).parents('.list-num-out').find('.list-num').html(parseInt($(this).parents('.list-num-out').find('.list-num').html())+1)
})

//店铺列表 点击顶部下拉
$(document).on('click','.shop-select',function(){
	if($('.shop-navbar').attr('flag') == undefined || $('.shop-navbar').attr('flag') == 'false'){
		$('.shop-navbar').css('top','52px');
		$('.stop-mask').fadeIn(400);
		$('.shop-navbar').attr('flag','true');
	}else{
		$('.shop-navbar').css('top','-256px');
		$('.stop-mask').fadeOut(400);
		$('.shop-navbar').attr('flag','false');
	}
	
})

$(document).on('click','.stop-mask',function(){
	$('.shop-navbar').css('top','-256px');
	$('.stop-mask').fadeOut(400);
	$('.shop-navbar').attr('flag','false');
})

let timer = null;//防抖动的 timer
function debounceSearch(keywords){//定义防抖的函数
	timer = setTimeout(function(){
		console.log(keywords);
		// 这里添加搜索接口
	},500)
}
$('.search-ipt').on('input',function(e){
	clearTimeout(timer)
	debounceSearch($(this).val())
})

// 点击删除历史记录
$('.alert-close').click(function(){
	$('.alert-box').show();
	$('.alert-box').removeClass('fadeOut');
	$('.alert-box').addClass('fadeIn');
	$('.alert-mask').fadeIn(200);
})
// 点击确定
$('.alert-box .confirm').click(function(){
	$('.alert-box').removeClass('fadeIn');
	$('.alert-box').addClass('fadeOut');
	$('.alert-mask').fadeOut(180,function(){
		$('.alert-box').hide();
	});
	// 这里填写删除的方法
})
// 点击取消
$('.alert-box .cancel').click(function(){
	$('.alert-box').removeClass('fadeIn');
	$('.alert-box').addClass('fadeOut');
	$('.alert-mask').fadeOut(180,function(){
		$('.alert-box').hide();
	});
})