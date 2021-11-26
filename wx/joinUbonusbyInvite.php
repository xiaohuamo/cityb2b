<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<TITLE>加入班级、创建班级</TITLE>
<META charset="utf-8" name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
<script src="js/jquery-2.1.1.min.js"></script>
<script src="js/myTools.js"></script>
</head>
<body>
<?php
	$code=(isset($_GET['code']))?$_GET['code']:"";
	
	if(!empty($code)){
		require_once "wxjssdk.php";//引入微信类
		$weixin	=getOpenID($code);
	}
	if(empty($weixin)) echo "请关注ubonus，微信号：ubonus";
	else{
		setcookie("usertoken", $weixin, time()+360000);	// 100小时coockie
		require_once 'mysql_bae.func.php';
		if(isset($_GET['state'])){
			$inviterOpenID=$_GET['state'];
			mysql_query("INSERT INTO invitation (inviterOpenID,InviteeOpenID,jtime) VALUES($inviterOpenID,'$weixin',now()) ");
			
			$select_res=mysql_query("SELECT * from wxinfor WHERE OpenID='$weixin' ");
			if($row=mysql_fetch_assoc($select_res)){
				$subscribe=$row['subscribe'];
			}else $subscribe=0;
		}
?>




</body>
</html>
