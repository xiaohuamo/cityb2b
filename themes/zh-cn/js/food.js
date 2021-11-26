    function add_fav(obj){
      var coupon_id = $(obj).data('id');

      if($(obj).hasClass('faved')){
        $(obj).removeClass('faved');
        $.get(
          "/query?cmd=fav_remove", 
          {'itemId': coupon_id,'userId':'','type':'coupon'}, 
          function(data){
             $(obj).find('small').html('收藏')
        });

      }else{
        $(obj).addClass('faved');
        $.get(
          "/query?cmd=fav_add", 
          {'itemId': coupon_id,'userId':'','type':'coupon'}, 
          function(data){
             $(obj).find('small').html('已收藏')
        });
      }
      
    }
	
	
	
	function display_search_filter(list) {
     
     var html='';
     for (var i = list.length - 1; i >= 0; i--) {
        var data = list[i];
        if(data.length<=0)continue;

        var searchArg=data.split('=');
        var value = searchArg.pop();
        var key=searchArg.pop();

        var originalKey = key;
        var originalValue = value;

        var filter_list ={};

        if(!key)continue;

        if(key!='key'&&key!='orderby'&&key!='cityid'&&key!='alias'&&key!='latitude'&&key!='longitude'){
            key=key.replace('meal_type','用餐时段')
			 key=key.replace('restaurant_menu','线上点餐')
            key=key.replace('guest_limit','用餐人数')
            key=key.replace('apportmant_required','预定')
            key=key.replace('time_limit','限时')
            key=key.replace('available_on_holiday','公共假期')
            key=key.replace('sharable','同享')
            key=key.replace('private_room','包间')

            value=value.replace('breakfast','早餐');
			value=value.replace('restaurant_menu','餐厅列表');
			
            value=value.replace('lunch','午餐');
            value=value.replace('dinner','晚餐');
            value=value.replace('midnight','夜宵');
            value=value.replace('1','单人');
            value=value.replace('2-3','2-3人');
            value=value.replace('4-5','4-5人');
            value=value.replace('6-8','6-8人');
            value=value.replace('9','9人以上');
            value=value.replace('apportmant_require','需要预约');
            value=value.replace('apportmant_not_required','不需要预约');
            value=value.replace('no_time_limit','不限时');
            value=value.replace('time_limit','限时');
            value=value.replace('available_on_holiday','可用');
            value=value.replace('not_available_on_holiday','不可用');
            value=value.replace('sharable','同享');
            value=value.replace('not_sharable','不同享');
            value=value.replace('has_private_room','有');
            value=value.replace('no_private_room','没有');
        }

        if(value=='106121102')continue;
        if(key=='alias'){
          key='菜系';
          switch(value){
            case "106121102125":
                value='代金券';
                break;
              case "106121102102":
                value='蛋糕甜点';
                break;
              case "106121102103":
                value='火锅';
                break;
              case "106121102104":
                value='自助餐';
                break;
              case "106121102105":
                value='烧烤烤肉烤串';
                break;
              case "106121102106":
                value='日韩料理';
                break;
              case "106121102107":
                value='西餐';
                break;
              case "106121102108":
                value='东北菜';
                break;
              case "106121102109":
                value='川湘菜';
                break;
              case "106121102110":
                value='江浙菜';
                break;
              case "106121102111":
                value='香锅烤鱼';
                break;
              case "106121102112":
                value='粤菜';
                break;
              case "106121102113":
                value='西北菜';
                break;
              case "106121102114":
                value='云贵菜';
                break;
              case "106121102115":
                value='东南亚菜';
                break;
              case "106121102116":
                value='海鲜';
                break;
              case "106121102117":
                value='酱菜卤菜';
                break;
              case "106121102118":
                value='汤粥炖菜';
                break;
              case "106121102119":
                value='咖啡酒吧';
                break;
              case "106121102120":
                value='创意菜';
                break;
              case "106121102121":
                value='聚餐宴请';
                break;
              case "106121102128":
                value='北京菜';
                break;
              case "106121102122":
                value='素食';
                break;
              case "106121102124":
                value='奶茶';
                break;
              case "106121102126":
                value='粥';
                break;
              case "106121102127":
                value='湖北菜';
                break;
              case "106121102123":
                value='更多美食';
                break;
          }
        }

        if(value=='556')continue;
        if(key=='cityid'){
            key='位置';
            switch(value){
              case "639": 
                value='CBD';
                break;
              case "640": 
                value='BOX HILL ';
                break;
              case "641": 
                value='GLEN WAV';
                break;
              case "645": 
                value='Balwyn/kew附近';
                break;
              case "640": 
                value='BOX HILL ';
                break;
              case "657": 
                value='Carlton';
                break;
              case "653": 
                value='Carnegie';
                break;
              case "639": 
                value='CBD';
                break;
              case "655": 
                value='Chadstone';
                break;
              case "644": 
                value='Clayton附近';
                break;
              case "649": 
                value='Dandenong附近';
                break;
              case "654": 
                value='Dockland';
                break;
              case "652": 
                value='Foodscary/sunshine附近';
                break;
              case "650": 
                value='Frankston 附近';
                break;
              case "641": 
                value='GLEN WAV';
                break;
              case "646": 
                value='Malvern附近';
                break;
              case "642": 
                value='Preston周边';
                break;
              case "651": 
                value='Richmond附近';
                break;
              case "643": 
                value='Ringwood附近';
                break;
              case "648": 
                value='SpringVale附近';
                break;
              case "647": 
                value='ST Kilda附近';
                break;
              case "656": 
                value='Toorak';
                break;
            }

            if(html.indexOf(value)>0)continue;
          }

        if(key=='orderby'){
            key='热度';
            switch(value){
              case "default": value='综合';
                break;
              case "hits": value='热门';
                break;
              case "buy": value='销量';
                break;
              case "id": value='最新';
                break;
			 case "pricelow": value='价格（从低到高）';
                break;
              case "pricehigh": value='价格（从高到低）';
                break;
              
            }
        }

        if(key=='longitude'||key=='latitude'){
              key='离我最近';
              value=" ";

              originalKey='nearme';
              originalValue='nearme'
              if(html.indexOf(key)>0)continue;
        }

        html= html +"<span onClick='filterDisplayRemove(this);' data-okey='"+originalKey+"' data-ovalue='"+originalValue+"'>"+key+":"+value+"<i class='fa fa-close'  ></i></span>";
     }
    

     if(html){
      $('.filter-display').html('筛选：'+html+"<small class='filter-display-clear' onClick='filterDisplayClear();'>清空 <i class='fa fa-close'></i> </small>");
     }else{
      $('.filter-display').html(' ');
     }
  }

  function menu_reverse_highlight(url) {
      //reset all highlight
      $('a.ajax .item').removeClass('active');
      $('.filter-section span').removeClass('selected');
      $('.filter-menu span').each(function(k,v){
        $(v).html($(v).data('default'));
      })

      var search = url.slice(url.indexOf('?')+1);

      var searchArgList = search.split('&');
      
      display_search_filter(searchArgList);

      for (var i = searchArgList.length - 1; i >= 0; i--) {
        if(searchArgList[i].length<=0)continue;

        var searchArg=searchArgList[i].split('=');
        var value = searchArg.pop();
        var key=searchArg.pop();

        if(key=='longitude'||key=='latitude'){
          $target = $('.nearme');
          $target.find('.item').addClass('active');
        }else{

          if(key=='meal_type'||key=='guest_limit'){
            var valueList = value.split(',');
            for (var n = valueList.length - 1; n >= 0; n--) {
              $target=$('.ajax[data-value='+valueList[n]+']');

              $target.find('.item').addClass('active');
              $('.filter-section span[data-value='+valueList[n]+']').addClass('selected');
            }
          }else if(key=='key'){
              $('.search-box small').html(value);
          }else{
            $target=$('.ajax[data-value='+value+']');

            $target.find('.item').addClass('active');
            $('.filter-section span[data-value='+value+']').addClass('selected');
          }
          
        }

         try{
            var c = $target.parent('.search-by-list').attr('class').split(' ').pop();
            $('.filter-menu[data-target='+c+']').find('span').html($target.data('name'));
          }catch(e){
            //doing nothing
          }
      }
  }
  



  function wxShareTimeline(){
     wx.onMenuShareTimeline({
      title: shareTitle, // 分享标题
      link: shareLink, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
      imgUrl: shareImg, // 分享图标
      success: function () { 
          // 用户确认分享后执行的回调函数
      },
      cancel: function () { 
          // 用户取消分享后执行的回调函数
      }
    });
  }
  function wxShareMessage(){
     wx.onMenuShareAppMessage({
      title: shareTitle, // 分享标题
      desc: desc, // 分享描述
      link: shareLink, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
      imgUrl: shareImg, // 分享图标
      type: '', // 分享类型,music、video或link，不填默认为link
      dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
      success: function () { 
          // 用户确认分享后执行的回调函数
      },
      cancel: function () { 
          // 用户取消分享后执行的回调函数
      }
    });
  }
  
  function searchNearMe(position) {
    var latitude=position.coords.latitude;
    var longitude=position.coords.longitude;

    var date = new Date();
    date.setTime(date.getTime() + (5 * 60 * 1000));

    $.cookie("latitude",latitude,{ expires : date });
    $.cookie("longitude",longitude,{ expires : date });

    var url = '&latitude='+latitude+'&longitude='+longitude;
    $('.nearme').attr('href',url);
    $('#pageloading').remove();

    $('.nearme.ajax').trigger('click');
}

function getLocationDeny(error) {
  console.log(error);
  $('#pageloading').remove();
}

function isNumber(n) { return !isNaN(parseFloat(n)) && !isNaN(n - 0) }

function getLocation() {
    var latitude = $.cookie("latitude");
    var longitude = $.cookie("longitude");

    if(isNumber(latitude)&&isNumber(longitude)){
      console.log('location from cookie');
      
      var url = '&latitude='+latitude+'&longitude='+longitude;
      $('.nearme').attr('href',url);

    }else{
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(searchNearMe,getLocationDeny);
        $('body').append("<i id='pageloading' class='fa fa-spinner fa-pulse fa-5x' style='position:fixed;top:50%;left:40%;z-index:9999;color:#fc3'></i>");
      } else {
          alert("Geolocation is not supported by this browser.");
      }
    }
    
}


function filterDisplayClear(){
        $(".search-by-list .item").removeClass('active')
        $('.filter-btn.btn-reset').trigger('click');
        $('.filter-btn.btn-confirm').trigger('click');
  }

  function filterDisplayRemove(obj){
    var key = $(obj).data('okey');
    var value = $(obj).data('ovalue');


    $("a.ajax[data-value='"+value+"'] .item").removeClass('active');
    $(".filter-section span[data-value='"+value+"']").removeClass('selected');

    $(obj).remove();
    $('.filter-btn.btn-confirm').trigger('click');
  }