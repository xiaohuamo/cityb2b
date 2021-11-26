<?php
define("WX_APPID",		"wx2a9d55ef6a586842");
define("WX_APPSECRET",	"3bc94bf8795a814adc63fc1e611bafc8");

$wxURL="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".WX_APPID."&redirect_uri=https://ubonus365.com/";
$wxPARAM="&response_type=code&scope=snsapi_base&state=1#wechat_redirect";


$txlmenu='{
 "button":[
 {
       "type":"view",
	    "name":"微奖",
	   "url":"https://ubonus365.com/"
  },
  {
       "type":"view",
	    "name":"购物节",
	   "url":"https://ubonus365.com/shoppingday"
  },
  {
       "name":"我",
       "sub_button":[
        {
           "type":"view",
           "name":"我的订单",
		   "url":"https://ubonus365.com/member/myorders"
        },
         {
           "type":"view",
           "name":"个人中心",
		   "url":"https://ubonus365.com/member/index"
        },
       {
           "type":"view",
           "name":"我要推广",
		   "url":"https://ubonus365.com/coupon1/4894"
        },
		
        {
           "type":"view",
           "name":"我要开店",
		   "url":"https://ubonus365.com/member/mingxingdian_index"
        },
		 {
           "type":"view",
           "name":"客户订单",
		   "url":"https://ubonus365.com/company/customer_orders"
        }]
   }]
}';


$TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".WX_APPID."&secret=".WX_APPSECRET;

$json=file_get_contents($TOKEN_URL);
$result=json_decode($json,true);
$ACC_TOKEN=$result['access_token'];
//=================================================

$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$ACC_TOKEN;
$result = https_request($url, $txlmenu);
var_dump($result);

function https_request($url,$data = null){
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

?>