<?php
require_once 'mysql_bae.func.php';
define("WX_APPID",		"wx7a7df86983f3dc7f");
define("WX_APPSECRET",	"5b7dc262abeb22d48cb580dbe14e9e1d");
define("WX_TIMESTAMP",	"1420774989");
define("WX_NONCESTR",	"2nDgiWM7gCxhL8v0");
define("OPENID_TEDDY",	"ooeQYtxlPXx6wf1DdDyj7ROXxHPc");
define("OPENID_SUNNY",	"ooeQYty9u8gIpDKSfEJPzm5Z6aDw");

class WXjsSDK  {
	private function getAccessToken() //生成access_token，并存入数据库       　
	{
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".WX_APPID."&secret=".WX_APPSECRET;

		$data = https_request($url);//通过自定义函数https_request得到https的内容
		$resultArr = json_decode($data, true);//转为数组           //access_token插入数据库做缓存
		$token=$resultArr["access_token"];
									
		mysql_query("UPDATE wxtoken SET token='$token' WHERE tname='token' ");


		return $token;//获取access_token
	}
	public function getToken()//如果access_token有数据库缓存，就从数据库取，如果没有就重新取
	{
		//return $this->getAccessToken();//重新获取
		$row=mysql_fetch_assoc(_select_data("SELECT *,(unix_timestamp(now())-unix_timestamp(ttime)) seconds from wxtoken WHERE tname='token' "));
		if($row['seconds']<3600){
			return $row['token'];//从数据库取
		}else{
			return $this->getAccessToken();//重新获取
		}
	}
	public function getJSApi_ticket(){//获取JSApi_ticket
		$row=mysql_fetch_assoc(_select_data("select *,(unix_timestamp(now())-unix_timestamp(ttime)) seconds FROM wxtoken where tname='JSticket' "));
		if($row['seconds']<3600){
			if(!empty($row['token'])) return $row['token'];//从数据库取
		}                //如果数据库没有保存有效的jsApiTicket，就重新生成并存入数据库
			$accessToken = $this->getToken();//获取access_token
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$accessToken."&type=jsapi";
					
			$data = https_request($url);//通过自定义函数https_request得到https的内容
			$resultArr = json_decode($data, true);//转为数组                //插入数据库做缓存
			$ticket=$resultArr["ticket"];
			mysql_query("UPDATE wxtoken SET token='$ticket' WHERE tname='JSticket' ");
			return $ticket;//获取jsApi_ticket
		
	}
	public function getSignature(){

		$jsApi_ticket = $this->getJSApi_ticket();//获取access_token

		if($_SERVER['QUERY_STRING']){
			$url= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];

		}else{
			$url= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		}                        
		$str = "jsapi_ticket=".$jsApi_ticket."&noncestr=".WX_NONCESTR."&timestamp=".WX_TIMESTAMP."&url=".$url;
		$signature = "";
		$signature=sha1($str);
		return $signature;
	}

	public function getSignPackage() {
	    $jsapiTicket = $this->getJSApi_ticket();

	    // 注意 URL 一定要动态获取，不能 hardcode.
	    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

	    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
	    $string = "jsapi_ticket=$jsapiTicket&noncestr=".WX_NONCESTR."&timestamp=".WX_TIMESTAMP."&url=$url";

	    $signature = sha1($string);

	    $signPackage = array(
	      "appId"     => WX_APPID,
	      "nonceStr"  => WX_NONCESTR,
	      "timestamp" => WX_TIMESTAMP,
	      "url"       => $url,
	      "signature" => $signature,
	      "rawString" => $string
	    );
	    return $signPackage; 
	  }

}

function getOpenID($code)
{
	$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".WX_APPID."&secret=".WX_APPSECRET."&code=".$code."&grant_type=authorization_code";
	$wxuser = json_decode(https_request($url), true);//转为数组
	return isset($wxuser["openid"])?$wxuser["openid"]:"";
}

function getUserInfor($weixin)
{
	$wxObj = new WXjsSDK();//实例化微信类

	$access_token = $wxObj->getToken();

	$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$weixin."&lang=zh_CN";

	$ret=https_request($url);
	$wxuser = json_decode($ret, true);//转为数组 
	if(isset($wxuser['errorcode'])) return $ret;
	$subscribe	=isset($wxuser["subscribe"])?$wxuser["subscribe"]:0;
			
	if($subscribe){

		$nickname	=addslashes($wxuser["nickname"]);	// nickname中可能有'号
		$sex		=$wxuser["sex"];
		$language	=$wxuser["language"];
		$city		=$wxuser["city"];
		$province	=$wxuser["province"];
		$country	=$wxuser["country"];
		$headimgurl	=$wxuser["headimgurl"];
		$subscribe_time=date('Y-m-d H:i:s', $wxuser["subscribe_time"]);
		$sql= " INSERT INTO wxinfor (OpenID,nickname,sex,language,city,province,country,headimgurl,subscribe,subscribe_time,renew_time ) "
			. " VALUES('$weixin','$nickname','$sex','$language','$city','$province','$country','$headimgurl','$subscribe','$subscribe_time',now() ) "
			. " ON DUPLICATE KEY UPDATE nickname='$nickname',sex='$sex',language='$language',city='$city',province='$province', "
			. " country='$country',headimgurl='$headimgurl',subscribe='$subscribe',subscribe_time='$subscribe_time',renew_time=now() ";
		mysql_query($sql);			// 存入数据库
		return $wxuser;
	}else{
		mysql_query("UPDATE wxinfor SET subscribe=0 WHERE openID='$weixin' ");
		return "unsuscribe";
	}
}

function getUserList()
{
	$wxObj = new WXjsSDK();//实例化微信类
	$access_token = $wxObj->getToken();
	$url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$access_token";
	$userList = json_decode(https_request($url), true);
	return $userList;
}

function sendMsg2openID($openID,$msg)
{
	$txt='{"touser":"'.$openID.'","msgtype":"text","text": { "content":"'.$msg.'" }	}';

	$wxObj = new WXjsSDK();//实例化微信类
	$access_token = $wxObj->getToken();
	$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
	$ret=json_decode(https_request($url,$txt), true);//转为数组
	return $ret;	 
}

function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
function dataPost($post_string, $url)
{//POST方式提交数据
	$context = array ('http' => array ('method' => "POST", 'header' => "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) \r\n Accept: */*", 'content' => $post_string ));
	$stream_context = stream_context_create ( $context );
	$data = file_get_contents ( $url, FALSE, $stream_context );
	return $data;
} 

?>
