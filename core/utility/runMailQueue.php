<?php 
	$_SERVER['DOCUMENT_ROOT']='/mnt/www/';
	$_SERVER['REQUIST_URI']='https://ubonus365.com';
	$_SERVER['HTTP_HOST']='ubonus365.com';
	require_once('/mnt/www/core/include/config.inc.php');
	$mail_services =loadModel('system_mail_queue');
	$mail_services->run();
	
	$myfile = fopen("/mnt/www/log/mailQueueLog.txt", "a") or die("Unable to open file!");
	fwrite($myfile, "mail Queue working ".date('Y-m-d H:i:s')."\n");
	fclose($myfile);
 ?>
