<?php

//header("Content-type:text/html;charset=utf-8");
//require_once './includes/configure.php';
// pclist.lsecret


/**
 * MySQL示例，通过该示例可熟悉BAE平台MySQL的使用（CRUD）
 */

/***配置数据库名称***/
/*替换为你自己的数据库名（可从管理中心查看到）*/
if(empty($dbname)){$dbname = "cityb2b";}	// test   txl
 
/*从环境变量里取出数据库连接需要的参数*/
//$host = getenv('HTTP_BAE_ENV_ADDR_SQL_IP');
//$port = getenv('HTTP_BAE_ENV_ADDR_SQL_PORT');
//$user = getenv('HTTP_BAE_ENV_AK');
//$pwd = getenv('HTTP_BAE_ENV_SK');
$host = 'localhost';
$port = '3306';
$user = 'ubonusco_root';
$pwd = 'fWMGWK}og*W5';

/*接着调用mysql_connect()连接服务器*/
$link = @mysql_connect("{$host}:{$port}",$user,$pwd,true);
if(!$link) {
  die("Connect Server Failed: " . mysql_error());
}
/*连接成功后立即调用mysql_select_db()选中需要连接的数据库*/
if(!mysql_select_db($dbname,$link)) {
  die("Select Database Failed: " . mysql_error($link));
}

mysql_query("set names utf8");

/*至此连接已完全建立，就可对当前数据库进行相应的操作了*/
/*！！！注意，无法再通过本次连接调用mysql_select_db来切换到其它数据库了！！！*/
/* 需要再连接其它数据库，请再使用mysql_connect+mysql_select_db启动另一个连接*/
 
/**
* 接下来就可以使用其它标准php mysql函数操作进行数据库操作
*/

//创建一个数据库表
function _create_table($sql){
	mysql_query($sql) or die('创建表失败，错误信息：'.mysql_error());
	return "创建表成功";
}

//插入数据
function _insert_data($sql){
  	if(!mysql_query($sql)){
    	return 0;	//插入数据失败
    }else{
      	if(mysql_affected_rows()>0){
      		return 1;	//插入成功
      	}else{
      		return 2;	//没有行受到影响
      	}
    }
}

//删除数据
function _delete_data($sql){
  	if(!mysql_query($sql)){
    	return 0;	//删除失败
  	}else{
      	if(mysql_affected_rows()>0){
      		return 1;	//删除成功
      	}else{
      		return 2;	//没有行受到影响
      	}
    }
}

//修改数据
function _update_data($sql){
  	if(!mysql_query($sql)){
    	return 0;	//更新数据失败
    }else{
      	if(mysql_affected_rows()>0){
      		return 1;	//更新成功;
      	}else{
      		return 2;	//没有行受到影响
      	}
    }
}

//检索数据
function _select_data($sql){
	$ret = mysql_query($sql) or die('SQL语句有错误，错误信息：'.mysql_error());
	return $ret;
}

//删除表
function _drop_table($sql){
	mysql_query($sql) or die('删除表失败，错误信息：'.mysql_error());
	return "删除表成功";
}


function getLSInfor($str,$LS_bit0)
{
	return ($str=="")?"":($LS_bit0?$str:"***");
}

function getNameString($name,$admin)
{
	$prex="&nbsp;&nbsp;";		// &nbsp;表示空格
	if($admin&ID_MANAGER){ $prex="#";}	// 要用admin控制输出格式
	elseif($admin&ID_REGISTER){ $prex="*";}
	if($admin&ID_TEACHER){ $t1="<span class='TEACHER'>";$t2="</span>";}			// 要用admin控制输出格式
	else{$t1="";$t2="";}
	return $t1.$prex.$name.$t2; 
}

function getPhoneString($mphone,$admin)
{
	if($admin&ID_TEACHER){ $t1="<span class='TEACHER'>";$t2="</span>";}			// 要用admin控制输出格式
	else{$t1="";$t2="";}
	return $t1.$mphone.$t2; 
}
	$ProvECname= array(
		'heilongjiang' => '黑龙江',
		'jilin' => '吉林', 
		'liaoning' => '辽宁', 
		'hebei' => '河北', 
		'shandong' => '山东', 
		'jiangsu' => '江苏', 
		'zhejiang' => '浙江', 
		'anhui' => '安徽', 
		'henan' => '河南', 
		'shanxi' => '山西', 
		'shaanxi' => '陕西', 
		'gansu' => '甘肃', 
		'hubei' => '湖北', 
		'jiangxi' => '江西', 
		'fujian' => '福建', 
		'hunan' => '湖南', 
		'guizhou' => '贵州', 
		'sichuan' => '四川', 
		'yunnan' => '云南', 
		'qinghai' => '青海', 
		'hainan' => '海南', 
		'shanghai' => '上海', 
		'chongqing' => '重庆', 
		'tianjin' => '天津', 
		'beijing' => '北京', 
		'ningxia' => '宁夏', 
		'neimongol' => '内蒙古', 
		'guangxi' => '广西', 
		'xinjiang' => '新疆', 
		'xizang' => '西藏', 
		'guangdong' => '广东', 
		'hongkong' => '香港', 
		'taiwan' => '台湾', 
		'macau' => '澳门',
		'overseas' => '国外',
		'other' => ''
	);

?>
