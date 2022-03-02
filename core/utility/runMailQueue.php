<?php 
	/*

	$_SERVER['DOCUMENT_ROOT']='/www/wwwroot/www/';
	$_SERVER['REQUIST_URI']='https://cityb2b.com';
	$_SERVER['HTTP_HOST']='cityb2b.com';
	require_once('/www/wwwroot/www/core/include/config.inc.php');
	$mail_services =loadModel('system_mail_queue');
	$mail_services->run();
	
	$myfile = fopen("/www/wwwroot/www/log/mailQueueLog.txt", "a") or die("Unable to open file!");
	fwrite($myfile, "mail Queue working ".date('Y-m-d H:i:s')."\n");
	fclose($myfile);

	*/

	$_SERVER['DOCUMENT_ROOT']='C:/xampp/htdocs/cityb2b2';
	$_SERVER['REQUIST_URI']='www.ozsupply.com';
	$_SERVER['HTTP_HOST']='www.ozsupply.com';
	require_once('C:/xampp/htdocs/cityb2b2/core/include/config.inc.php');
	$mail_services =loadModel('system_mail_queue');
	$mail_services->run();

	$myfile = fopen("C:/xampp/htdocs/cityb2b2/log/mailQueueLog.txt", "a") or die("Unable to open file!");
	fwrite($myfile, "mail Queue working ".date('Y-m-d H:i:s')."\n");
	fclose($myfile);
 ?>
