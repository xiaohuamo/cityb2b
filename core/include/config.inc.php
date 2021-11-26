<?php
/**
 * Session Handel
 */
ob_start();
session_start();

$now = time();
if (isset($_SESSION['LAST_ACTIVITY']) && $now >( $_SESSION['LAST_ACTIVITY'] +3600*24*100000)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['LAST_ACTIVITY'] = $now ;

header("Content-Type:text/html;charset=utf-8;");


/**
 * 报错设置
 */
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING));


$CONFIG = ARRAY();

/**
 * 数据库设置
 */
$CONFIG['DB_HOST']	= 'localhost';
$CONFIG['DB_USER']	= 'ubonusco_root';	
$CONFIG['DB_PASS']	= 'fWMGWK}og*W5';
$CONFIG['DB_NAME']	= 'ubonusco_tinybonus';
$CONFIG['DB_CHAR']	= 'UTF8';
$CONFIG['DB_PRE']	= 'cc_';


/**
 * 目录设置
 */
define('BASE_DIR', '');

if ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) {
	$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
}

/**
 * HTTPS
 */
if($_SERVER['HTTPS']){
	define('APPLICATION_PROTOCOL', "https://");
}else{
	define('APPLICATION_PROTOCOL', "http://");
}


define('HTTP_ROOT_WX', APPLICATION_PROTOCOL."ubonus365.com/");//same as wx security config
define('HTTP_ROOT', APPLICATION_PROTOCOL.$_SERVER['HTTP_HOST'].'/'.BASE_DIR);
define('HTTP_ROOT_WWW', $_SERVER['REQUIST_URI'].'/'.BASE_DIR);

define('DOC_DIR', $_SERVER['DOCUMENT_ROOT'].'/'.BASE_DIR);

define('CORE_DIR', DOC_DIR.'core/');
define('DATA_DIR', DOC_DIR.'data/');
define('UPDATE_DIR', DOC_DIR.'data/upload/');

define('STATIC_PATH', '/'.BASE_DIR.'static/');
define('UPLOAD_PATH', '/'.BASE_DIR.'data/upload/');

define('CLASS_LEN', 3);

/**
 * 语言模板
 */
define( 'LANGS', serialize( array( array( 'id' => 'zh-cn', 'name' => 'CN' ),array( 'id' => 'en', 'name' => 'EN' ) ) ) );
$langs = array('zh-cn','en');
$default_lang='zh-cn';

/*前台语言*/
$lang = trim( $_GET['lang'] );
if(!$lang)$lang = $_COOKIE['lang'];
if ( ! in_array( $lang, $langs ) ) $lang = $default_lang;
setcookie( 'lang', $lang, time() + 60 * 60 ,'/' );

/*后台语言*/
$admin_lang = trim( $_GET['admin_lang'] );
if(!$admin_lang)$admin_lang = $_COOKIE['admin_lang'];
if ( ! in_array( $admin_lang, $langs ) ) $admin_lang = $default_lang;
setcookie( 'admin_lang', $admin_lang, time() + 60 * 60 ,'/' );



$style = $default_lang;
define( 'STYLE', $style.'/' );
define( 'TPL_DIR', DOC_DIR.'themes/'.STYLE);

//加密前后辍，不能修改
$KEY_	= 'abc';
$_KEY	= 'def';

$TPL_SM_CONFIG_DIR		= CORE_DIR."Smarty/Config_File.class.php";
$TPL_SM_CACHEING		= false;
$TPL_SM_TEMPLATE_DIR	= TPL_DIR;
$TPL_SM_COMPILE_DIR		= DATA_DIR.'tpl_compile';
$TPL_SM_CACHE_DIR		= DATA_DIR.'tpl_cache';
$TPL_SM_DELIMITER_LEFT	= '<{';
$TPL_SM_DELIMITER_RIGHT	= '}>';

require_once CORE_DIR.'include/WebUtility.php';
require_once CORE_DIR.'v2.1/ParseURL.php';
require_once CORE_DIR.'include/db_mysql.php';
require_once CORE_DIR."include/class.file.php";
require_once CORE_DIR."smarty/Smarty.class.php";
require_once CORE_DIR.'include/global.php';



?>