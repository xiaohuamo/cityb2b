<?php

function u2g ($s)
{ 
	return mb_convert_encoding($s, "gb2312", "utf-8");
}

function g2u ($s)
{
	return mb_convert_encoding($s, "utf-8", "gb2312");
}

function left ($str, $len, $rev = false)
{
	if($len > strlen($str))
	{
		$len = strlen($str);
	}
	if ($rev)
	{
		return right($str, strlen($str) - $len);
	}

	return substr($str, 0, $len);
}

function right ($str, $len, $rev = false)
{
	if($len > strlen($str))
	{
		$len = 0;
	}
	if ($rev)
	{
		return left($str, strlen($str) - $len);
	}

	return substr($str, strlen($str) - $len);
}

function mid ($s, $st, $l = null)
{
	if($l == null) return substr($s, $st);
	if($l > strlen($s) - $st) return substr($s, $st);
	return substr($s, $st, $l);
}

function interception ($s, $st, $se)
{
	if ($se < $st) return substr($s, $st);
	return substr($s, $st, $se - $st);
}

/**
* 自定义获取字符串长度，中文按照实际所占字符计算
* 适合小段文字，大段文字速度较慢
*/
function nwstrlen( $str ) {
	$i = 0;
	for ( $j = 0; $j < strlen( $str ); $j++ ) {
		$i++;
		$ascii = ord( substr( $str, $j, 1 ) );
		if ( $ascii >= 224 ) {
			$j += 2;
		}
		else if ( $ascii >= 192 ) {
			$j++;
		}
	}
	return $i;
}

function cutstr ($str, $len, $pad = '...')
{
	if ( strlen($str) <= $len ) return $str;

	$tmpstr = '';

	for ($i = 0; $i < $len; $i++)
	{
		$ascii = ord( substr( $str, $i, 1 ) );
		if ( $ascii >= 224 ) {
			$tmpstr .= substr($str, $i, 3);
			$i += 2;
		}
		else if ( $ascii >= 192 ) {
			$tmpstr .= substr($str, $i, 2);
			$i++;
		}
		else {
			$tmpstr .= substr($str, $i, 1);
		}
	}

	return $tmpstr.$pad;
}

function utf8substr($str, $len, $from = 0, $pad = '..')
{
	$str1 = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s', '$1',$str);
	if (strlen($str1) < strlen($str)) return $str1.$pad;
	else return $str1;
}

function utf8substr_removeHTML($str, $len, $from = 0, $pad = '..')
{
	$str	= strip_tags($str);
	$str1	= preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s', '$1',$str);
	if (strlen($str1) < strlen($str)) return $str1.$pad;
	else return $str1;
}

function rebuild_array ($arr)
{
	static $tmp = array();

	for ($i = 0; $i < count($arr); $i++)
	{
		if (is_array($arr[$i])) rebuild_array($arr[$i]);
		else $tmp[] = $arr[$i];
	}

	return $tmp;
}

//remove repeat elements
function array_distinct ($arr)
{
	$tmp_arr = array();

	for($i = 0; $i < count($arr); $i++)
	{
		if(!in_array($arr[$i], $tmp_arr)) $tmp_arr[] = $arr[$i];
	}

	return $tmp_arr;
}

//when the get_file_contents() failed! you can use this function.
function get_http ($host, $url)
{

	//$host : 'mp3.sogou.com'
	//$url  : '/music.so?query=xxx'
	$fp = fsockopen($host, 80, $errno, $errstr, 30);

	if(!$fp) echo "$errstr ($errno)<br />\n";
	else
	{
		$out	= "GET $url HTTP/1.0\r\n";
		$out	.= "Host: $host\r\n";
		$out	.= "Connection: Close\r\n\r\n";
		fputs($fp, $out);

		$str = "";
		while(!feof($fp)) $str .= fgets($fp, 128);
			fclose($fp);
	}

	return $str;
}

function ip ()
{
	$unknown = 'unknown';

	if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown) )
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown))
		$ip = $_SERVER['REMOTE_ADDR'];
	$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches)?$matches[0]:$unknown;
	if ( false !== strpos($ip, ',') ) $ip = reset( explode(',', $ip) );

	return $ip;
}

function md6 ($s)
{
	global $CONFIG;
	return md5( $CONFIG['KEY_'] . strrev($s) . $CONFIG['_KEY'] );
}

function process_variables (&$val, $key)
{
	if (is_array($val))
	{
		foreach ($val as $k => $v)
		{
			process_variables($v, $k);
		}
	}
	else
	{
		$val = addslashes($val);
	}
}

function formatDate ($ymd, $date)
{
	$date = strtotime($date) ? strtotime($date) : $date;
	return date($ymd, $date);
}

function limitInt ($int, $min, $max = null)
{
	if ($max == null)
	{
		if ($int < $min) return $min;
		return $int;
	}
	if ($min >= $max) return $max;

	if ($int < $min) $int = $min;
	if ($int > $max) $int = $max;

	return $int;
}

function get2 ($key = null)
{
	if (isset($key)) return $_GET[$key];
	return $_GET;
}

function post ($key = null)
{
	if (isset($key)) return $_POST[$key];
	return $_POST;
}

function request ($key = null)
{
	if (isset($key)) return $_REQUEST[$key];
	return $_REQUEST;
}

function files ($key = null)
{
	if (isset($key)) return $_FILES[$key];
	return $_FILES;
}

function session ($key = null)
{
	if (isset($key)) return $_SESSION[$key];
	return $_SESSION;
}

function is_post ()
{
	return $_SERVER["REQUEST_METHOD"] == "POST";
}

function getModel ($sys_name = null)
{
	if (empty($sys_name) || !isset($sys_name)) return false;

	$sys_path 		= null;
	$sys_className	= null;

	if (strpos($sys_name, '/'))
	{
		$sys_name	= explode('/', $sys_name);
		foreach ($sys_name as $key=>$value)
		{
			if ($key == count($sys_name) - 1)
			{
				if (empty($value)) $value = 'index';
				$sys_path		.= "mdl.$value.php";
				$sys_className	= "mdl_$value";
			}
			else
			{
				$sys_path	.= "$value/";
			}
		}
	}
	else
	{
		$sys_path		= "mdl.$sys_name.php";
		$sys_className	= "mdl_$sys_name";
	}

	return array(
		'path'		=> $sys_path,
		'classname'	=> $sys_className
	);
}

function loadModel ($mdl_name)
	{
		if ($mdl_info = getModel($mdl_name))
		{
			if (file_exists(CORE_DIR."model/".$mdl_info['path']))
			{
				include_once CORE_DIR."model/mdl.base.php";
				include_once CORE_DIR."model/".$mdl_info['path'];
				$mdl = new $mdl_info['classname']($this);
				return $mdl;
			}
			else die('model not fount'. var_dump($mdl_info));
		} else return null;
	}

function warning ($msg, $url = '')
{
	include_once(CORE_DIR.'include/class.httpService.php');
	$httpService = new httpService(HTTP_ROOT."?con=admin&ctl=common/warning&msg=".urlencode($msg)."&url=".urlencode($url));
	echo $httpService->result();
	unset($httpService);
	exit;
}

function getMethodsArray ($methods)  //映射对象的方法
{
	$mArray = array();
	foreach ($methods as $meth) $mArray[] = $meth->getName();
	return $mArray;
}

function getIntervalDays ($type)
{
	switch ($type)
	{
		case 'today' :
			return array(
				mktime(0, 0, 0, date('m'), date('d'), date('y')),
				mktime(23, 59, 59, date('m'), date('d'), date('y'))
			);
			break;
		case 'week' :
			return array(
				mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('y')),
				mktime(23, 59, 59, date('m'), date('d') - date('w') + 7, date('y'))
			);
			break;
		case 'lastweek' :
			return array(
				mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('y')),
				mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('y'))
			);
			break;
		case 'month' :
			return array(
				mktime(0, 0 , 0, date('m') , 1, date('y')),
				mktime(23, 59, 59, date('m'), date('t'), date('y'))
			);
		case 'lastmonth' :
			return array(
				mktime(0, 0 , 0, date('m') - 1, 1, date('y')),
				mktime(23, 59, 59, date('m'), 0, date('y'))
			);
		default : return array();
	}
}

function getFullUrl ($list, $parentUrl)  //获取完整的URL地址
{
	if (is_array($list))
	{
		$array = array();
		foreach ($list as $key=>$value)
		{
			$url = filter_url($parentUrl, $value);
			if (!empty($url)) $array[] = $url;
		}
		return $array;
	}
	else
	{
		return filter_url($parentUrl, $list);
	}
}

function filter_url ($cUrl, $url)
{
	$outUrl		= '';
	$cUrls		= parse_url($cUrl);
	$host		= ((!isset($cUrls['port']) || $cUrls['port'] == '80') ? $cUrls['host'] : $cUrls['host'].':'.$cUrls['port']);
	$baseUrl	= $host;

	$paths		= explode('/', eregi_replace("^http://", '', $cUrl));
	$cnt		= count($paths);

	for ($i = 1; $i < ($cnt - 1); $i++)
	{
		if(!ereg("[\?]", $paths[$i])) $baseUrl .= '/'.$paths[$i];
	}
	if(!ereg("[\?\.]", $paths[$n - 1]))
	{
		$baseUrl .= '/'.$paths[$n - 1];
	}

	$p	= strpos($url, "#");
	if ($p > 0) $url = substr($url, 0, $p);
	if ($url[0] == '/')
	{
		$outUrl = $host.$url;
	}
	else if ($url[0] == '.')
	{
		if (strlen($url) <= 2) return '';
		else if ($url[1] == '/') $outUrl = $baseUrl.ereg_replace('^.', '', $url);
		else $outUrl = $baseUrl.'/'.$url;
	}
	else
	{
		if (strlen($url) < 7) $outUrl = $baseUrl.'/'.$url;
		else if (eregi('^http://', $url)) $outUrl = $url;
		else $outUrl = $baseUrl.'/'.$url;
	}
	$outUrl = eregi_replace('^http://', '', $outUrl);
	$outUrl = 'http://'.eregi_replace('/{1,}', '/', $outUrl);

	return $outUrl;
}

function get_url_content ($url)
{
	$curlHandle = curl_init();
	curl_setopt($curlHandle, CURLOPT_URL, $url);
	curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
	$result = curl_exec($curlHandle);
	curl_close($curlHandle);
	return $result;
}

//发送邮件
function send_mail ( $email, $subject, $content, $header, $from ) {
	/*if ( empty( $header ) ) {
		$header = "MIME-Version: 1.0\n";
		$header .= "Content-type: text/html; charset=iso-utf-8\n";
		$header .= "X-Priority: 3\n";
		$header .= "X-MSMail-Priority: Normal\n";
		$header .= "X-Mailer: PHP/"."MIME-Version: 1.0\n";
		$header .= "From: " . $from . "\n";
		$header .= "Content-Type: text/html\n";
	}*/
	if ( empty( $header ) ) {
		return mail( $email, $subject, $content );
	}
	else return mail( $email, $subject, $content, $header );
}

function debug_filelog($msg){
	$myfile = fopen(DOC_DIR."log/"."FileDebug.txt", "a");
	fwrite($myfile, $msg."\n");
	fclose($myfile);
}
function filelog($msg,$filename=null){
	$myfile = fopen(DOC_DIR."log/".$filename, "a");
	fwrite($myfile, $msg."\n");
	fclose($myfile);
}

function dbArrayToSimpleArray($dbArray){
	$array = [];
	foreach ($dbArray as $key => $value) {
		array_push($array, $value[0]);
	}
	return $array;
}

require_once( DOC_DIR.'static/phpqrcode/qrlib.php' );//rqcode engine

define('PNG_TEMP_DIR', DATA_DIR.'qrcode/');
define('PNG_WEB_DIR',HTTP_ROOT.'data/qrcode/');

function generateQRCode($data, $type="URL", $errorCorrectionLevel=QR_ECLEVEL_L, $matrixPointSize=4, $padding=2){
	if($data==null)
		return null;

	if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);

    $file_name = md5($data).'.png';

    $file = PNG_TEMP_DIR.$file_name;
    $url =PNG_WEB_DIR.$file_name;
	QRcode::png($data,$file,$errorCorrectionLevel, $matrixPointSize, $padding,false);   //ref http://phpqrcode.sourceforge.net/docs/html/class_q_rcode.html

	if ($type==="URL") {
		return $url;
	} elseif ($type === "FILE") {
		return $file;
	} elseif ($type === "BOTH") {
		return [
			"url"=>$url,
			"file"=>$file
		];
	}
	
}
function currentpageqrlink(){
	$actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$actual_link = str_replace("/coupon/","/coupon1/",$actual_link);
	return generateQRCode($actual_link);
}

function redeemQRCode($redeemCode){
	$data = HTTP_ROOT.'company/customer_order_redeem_qrscan?qrscanredeemcode='.$redeemCode;
	//echo $data;
	return generateQRCode($data);
}
function PcLoginQRCode($returnUrl,$pcLoginId){
	/**
	 * 手机端在成功扫码以后不需要跳转。$returnUrl暂时无用
	 */
	if($returnUrl){
		$data = HTTP_ROOT.'?pcLoginId='.$pcLoginId;
	}else{
		$data = HTTP_ROOT.'?pcLoginId='.$pcLoginId;
	}
	return generateQRCode($data);
}
function bindWxQRCode($userId){
		$url =HTTP_ROOT_WX."member/bind_wx?userid=".$userId;

		$query = array(
				'appid' => 'wx7a7df86983f3dc7f',
				'redirect_uri' =>$url,
				'response_type' => 'code',
				'scope' => 'snsapi_userinfo',
				'state' => 1
				);
			
		$query = http_build_query( $query );
		$url = 'http://open.weixin.qq.com/connect/oauth2/authorize?'.$query.'#wechat_redirect';

		return generateQRCode($url);
}


function pprof_log($msg='init'){
	static $pprof_time_point;
	$now = microtime(true);
	$time_diff = $now - $pprof_time_point;
	if($msg!='init')echo $time_diff;
	echo $msg;
	echo "<br / >";
	$pprof_time_point  =$now;
}

function addBaseHref($str){
	return preg_replace('/\/data\/upload\//', 'https://www.cityb2b.com/data/upload/', $str);
}

function verify_recaptcha($g_recaptcha_response)
{
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array('secret' => '6LeufUMUAAAAAGCBF9IcAN947Jj-HI6HpymGs81f', 'response' => $g_recaptcha_response);

	$options = array(
	    'http' => array(
	        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	        'method'  => 'POST',
	        'content' => http_build_query($data)
	    )
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) return false;

	$result=json_decode($result,true);

	return $result['success'];
}

function send_sms($to,$content)
{	
	//https://dashboard.clicksend.com
	//ubonus_sms
	//ubonus_sms000
	
	if(!$to||!$content)return false;

	$url = 'https://api-mapper.clicksend.com/http/v2/send.php';
	$data = array('method' => 'http', 'username' => 'jun_mxh@yahoo.com.au','key' => '3F94400B-AEF3-5BEA-886A-C396466399CB','to' => $to,'message' =>$content);

	// use key 'http' even if you send the request to https://...
	$options = array(
	    'http' => array(
	        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	        'method'  => 'POST',
	        'content' => http_build_query($data)
	    )
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) { return false; }

	return $result;


}

function watermarkImage ($SourceFile, $WaterMarkText='Copyright cityb2b.com', $DestinationFile=null) { 
  	   if(!$DestinationFile)$DestinationFile=$SourceFile;

	   list($width, $height) = getimagesize($SourceFile);
	   $image_p = imagecreatetruecolor($width, $height);

		$image_state = getimagesize( $SourceFile);
		switch ( $image_state[2] ) {
			case 1 : $image = imagecreatefromgif( $SourceFile ); break;
			case 2 : $image = imagecreatefromjpeg( $SourceFile ); break;
			case 3 : $image = imagecreatefrompng( $SourceFile ); break;
		}

	   imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width, $height); 

	   $color = imagecolorallocatealpha($image_p, 255, 204, 51, 90);
	   $font = DOC_DIR.'fonts/verdana.ttf';
	   $font_size = ($width+$height)/30; 

	   imagettftext($image_p, $font_size, -rad2deg(atan2($height,$width)), 0.1*$width, 0.1*$height, $color, $font, $WaterMarkText);
	  	
	  	switch ( $image_state[2] ) {
			case 1 : imagegif( $image_p, $DestinationFile ); break;
			case 2 : imagejpeg( $image_p, $DestinationFile, 100 ); break;
			case 3 : imagepng( $image_p, $DestinationFile ); break;
		}

	   imagedestroy($image); 
	   imagedestroy($image_p); 
}

?>