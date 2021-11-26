<?php

class ctl_broadcast extends cmsPage
{
	
	function index_action() {
		$broadcastId = get2('id');
		$this->setData($broadcastId,'id');

		$businessId = substr($broadcastId, 4);
		$coupons = $this->loadModel('coupons')->getMostRecentOfUser($businessId,8);
		$this->setData($coupons , 'coupons' );

		$random=  $this->loadModel('coupons')->getRandom(4);
		$this->setData($random , 'random' );

		$isOwner = ('UUID'.$this->loginUser['id']==$broadcastId)?1:0;
		$this->setData($isOwner,'isOwner');

		$liveUserList=$this->loadModel('user')->getBroadcastingUserList();
		$this->setData($liveUserList,'userList');

		$data = $this->loadModel('user')->getBroadcastInfo($businessId);
		$this->setData($data,'broadcastInfo');


		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');
	        
		$this->display_pc_mobile('broadcast/broadcast','broadcast/broadcast_mobile');

	}

	function test_action(){
		require_once(CORE_DIR."/broadcastAPI/include/config.php");

		$APPNAME = 'live';

		$wowzaApplication = new com\wowza\Application($APPNAME);
		$response=$wowzaApplication->get();
		//$this->dump($response);

		$sf = new com\wowza\StreamFile();
		$response = $sf->getAll();
		//$this->dump($response);

		$wowza = new com\wowza\Wowza();
		$url=WOWZA_HOST."/servers/".WOWZA_SERVER_INSTANCE."/vhosts/".WOWZA_VHOST_INSTANCE."/applications/".$APPNAME."/streamfiles";
		$response = $wowza->getRequest($url);
		//$this->dump($response);

		$wowza = new com\wowza\Wowza();
		$url=WOWZA_HOST."/servers/".WOWZA_SERVER_INSTANCE."/vhosts/".WOWZA_VHOST_INSTANCE."/applications/".$APPNAME."/streamfiles/matt123";
		$response = $wowza->getRequest($url);
		//$this->dump($response);


		$wowza = new com\wowza\Wowza();
		$url=WOWZA_HOST."/servers/".WOWZA_SERVER_INSTANCE."/vhosts/".WOWZA_VHOST_INSTANCE."/applications/".$APPNAME."/publishers";
		$response = $wowza->getRequest($url);
		//$this->dump($response);

		$wowza = new com\wowza\Wowza();
		$url=WOWZA_HOST."/servers/".WOWZA_SERVER_INSTANCE."/vhosts/".WOWZA_VHOST_INSTANCE."/applications/".$APPNAME."/dvr";
		$response = $wowza->getRequest($url);
		//$this->dump($response);


		$wowza = new com\wowza\Wowza();
		$url=WOWZA_HOST."/servers/".WOWZA_SERVER_INSTANCE."/mediacache/stores";
		$response = $wowza->getRequest($url);
		//$this->dump($response);

		$wowza = new com\wowza\Wowza();
		$url=WOWZA_HOST."/servers/".WOWZA_SERVER_INSTANCE."/vhosts/_defaultVHost_";
		$response = $wowza->getRequest($url);
		$this->dump($response);

		

		

	}
	
}