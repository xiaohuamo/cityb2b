<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<TITLE>快来抢积分</TITLE>
<META charset="utf-8" name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
<link rel="stylesheet" href="js/jquery.mobile.structure-1.4.5.min.css" />
<script src="js/jquery-2.1.1.min.js"></script>
<script src="js/jquery.mobile-1.4.5.min.js"></script>
<style type="text/css">
<!--
.HLBLUE  {
	color: #00F;
	font-weight: bold;
}
-->
</style>

</head>
<body>
<?php
//		$weixin	= (isset($_GET['token']))?$_GET['token']:"";
		$code=(isset($_GET['code']))?$_GET['code']:"";
		if(!empty($code)){
			require_once "wxjssdk.php";//引入微信类
			$weixin	=getOpenID($code);
		}
		if(empty($weixin) && isset($_COOKIE["usertoken"]) ) $weixin=$_COOKIE["usertoken"];
		if(empty($weixin)) echo "ubonus，微信号：ubonus";
		else{
		setcookie("usertoken", $weixin, time()+360000);	// 100小时coockie
		}

?>


<div data-role="page" data-theme="c">
  <div data-role="content">
    <h2 align="center">Ubonus抢积分进行中</h2>
        <form method="post" >
		 <label for="words" >输入你要说的话：</label>
		<textarea id="words" rows="8" style="width:100%;"></textarea>
		  <div><br />点右上角微信菜单，<span class="HLBLUE">发送给朋友</span><br/>
		  </div>
        </form>
        <div><img src='pic/logo.jpg' width='100%' /></div>
   </div>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"> </script>

<script type="text/javascript">
	var weixin="<?php echo $weixin ?>";

	$(document).ready(function(){ 
		$('#words').text("Ubonus抢积分进行中，请点击加入吧！");
	});

<?php
	$signatureObj = new WXjsSDK();//实例化微信类
	$signature = $signatureObj->getSignature();
?>  
  var signature = "<?php echo $signature ?>"; 

  wx.config({
      debug: false,		// true,false
      appId: '<?php echo WX_APPID ?>',
      timestamp: '<?php echo WX_TIMESTAMP ?>',
      nonceStr: '<?php echo WX_NONCESTR ?>',
      signature: '<?php echo $signature ?>',
      jsApiList: [
        'checkJsApi',
//        'onMenuShareTimeline',
        'onMenuShareAppMessage',
//        'onMenuShareQQ',
//        'onMenuShareWeibo',
        'closeWindow',
        'hideMenuItems',
        'hideOptionMenu',
        'showOptionMenu'
      ]
  });
	wx.ready(function () {
		var shareData = {
			title: "Ubonus抢积分进行中", // 分享标题
			desc: "",//$.trim($("#words").text()), // 分享描述
			link: "<?php echo 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.WX_APPID.'&redirect_uri=http://'.$_SERVER['HTTP_HOST'].'/joinUbonusbyInvite.php&response_type=code&scope=snsapi_base&state='.$weixin.'#wechat_redirect' ?>", // 分享链接
			imgUrl: "<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/pic/logo.jpg' ?>", // 分享图标

		  trigger: function (res) {
			shareData.desc=document.getElementById('words').value;
		  },
		  success: function (res) {
			alert('你的邀请已经发出去了啦！');
		    wx.closeWindow();
		  },
		};
		wx.onMenuShareAppMessage(shareData);
		wx.onMenuShareTimeline(shareData);
		wx.showOptionMenu(); 
		wx.hideMenuItems({
		  menuList: [
			'menuItem:readMode', // 阅读模式
			'menuItem:share:timeline', // 分享到朋友圈
			'menuItem:copyUrl' // 复制链接
		  ]
		});
	});

	
</script>

</body>
</html>