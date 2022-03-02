<?php

class mdl_system_mail_template extends mdl_base
{
	private $tpl;

	private $langxml;

	private $templateLang;

	function __construct(){	

		require_once(CORE_DIR."smarty/Smarty.class.php");

		$this->tpl = new Smarty();

		$this->tpl->config_dir		= &$GLOBALS['TPL_SM_CONFIG_DIR'];
		$this->tpl->caching			= &$GLOBALS['TPL_SM_CACHEING'];
		$this->tpl->template_dir	= &$GLOBALS['TPL_SM_TEMPLATE_DIR'];
		$this->tpl->compile_dir		= &$GLOBALS['TPL_SM_COMPILE_DIR'];
		$this->tpl->cache_dir		= &$GLOBALS['TPL_SM_CACHE_DIR'];
		$this->tpl->left_delimiter	= &$GLOBALS['TPL_SM_DELIMITER_LEFT'];
		$this->tpl->right_delimiter	= &$GLOBALS['TPL_SM_DELIMITER_RIGHT'];
		$this->tpl->force_compile	= true;

		/**
		 * language1 model
		 */
		//$this->templateLang=$GLOBALS['default_lang'];
		$this->templateLang= $this->getLang();
	}
	
	public function getTemplateLang()
	{
		return $this->templateLang;
	}

	public function setTemplateLang($lang)
	{
		if(in_array($lang, $GLOBALS['langs'])){
			$this->templateLang=$lang;
		}else{
			$this->templateLang=$GLOBALS['default_lang'];
		}
		return $this;
	}

	private function setData ($data, $name = null)
	{
		if (!isset($name) || $name === false) $name = 'data';
		$this->tpl->assign($name, $data);
	}

	private function fetch ($page = null)
	{	
		$this->setData(UPLOAD_PATH, "UPLOAD_PATH");
		$this->setData(STATIC_PATH, "STATIC_PATH");
		$this->setData(HTTP_ROOT_WWW.'themes/'.STYLE, "SKIN_PATH");

		/**
		 * language1 model
		 */
		$this->langxml = simplexml_load_file(CORE_DIR.'lang/'.$this->getTemplateLang().'.xml');
		$this->setData($this->langxml, 'lang');
		$this->setData( $this->getTemplateLang(), 'langStr' );

		if (file_exists($this->tpl->template_dir.'/'.$page.'.htm'))
			return $this->tpl->fetch($page.".htm");
		else
			return NULL;
	}

	public function example()
	{
		$this->setData('this is an example html email template ', "title");
		$this->setData('chris wang', "name");
		return $this->fetch('email/example');
	}

    public function customerOrderNotification($orderId,$language1,$lang1)
    {
		$mdl_user = loadModel( 'user' );

        $order = loadModel( 'order' )->getByWhere(array('orderId'=>$orderId));
  		$this->setData(date('Y-m-d H:i:s', $order['createTime']), 'createTime');
	  	$this->setData($order['first_name'].' '.$order['last_name'], 'customerName');
  
	  	$this->setData($lang->thanks_for_choose_ubonus, 'message');
		$this->setData($order['redeem_code'], 'redeemCode');
		$this->setData(redeemQRCode($order['redeem_code']),'redeemQRCode');
	  	$this->setData($order['orderId'],'order_id');
		if ($order['logistic_delivery_date']) {
			$this->setData(date('Y-m-d ', $order['logistic_delivery_date']),'logistic_delivery_date');
		}else{
			$this->setData((string)$this->lang->follow_deliver_intr,'logistic_delivery_date');
		}
		
	  	$this->setData($order['order_name'], 'productName');
	  	$this->setData($this->getEmailComponent_orderDetail($order['orderId']),'itemTable');
	  	$this->setData($order['money'], 'productPrice');
	  	$this->setData($order['delivery_fees'], 'deliveryFees');
	  	$this->setData($order['booking_fees'], 'bookingFees');
	  	$this->setData($order['promotion_total'], 'promotionTotal');
	  	$this->setData($order['confirmedMoneyAppliedAmount'],'useMoney');
	  	$this->setData($order['surcharge'], 'surcharge');
		$this->setData($lang1, 'lang');

	  	$this->setData($mdl_user->getInitPasswordById($order['userId']),'initPassword');
	    $this->setData($order['payment'],'payment');
	    $this->setData($order['status'],'status');
		 $this->setData($order['message_to_business'],'message_to_business');

	    $delivery_option=($order['customer_delivery_option']);
	    $this->setData($delivery_option,'delivery_option');

	    $sql ="select c.redeemProcedure,c.finePrint,c.refund_policy,c.redeemProcedure_en,c.finePrint_en,c.refund_policy_en from cc_wj_customer_coupon b, cc_coupons c  where c.id=b.bonus_id  and b.order_id ='".$order['orderId']."'";
	    $business_deliver_pickup_payment_desc =loadModel('coupons')->getListBySql($sql);

        if ($language1=='en' && $business_deliver_pickup_payment_desc[0]['finePrint_en']) {
			$finePrint=$business_deliver_pickup_payment_desc[0]['finePrint_en'];			
		}else{
			$finePrint=$business_deliver_pickup_payment_desc[0]['finePrint'];			
		}
		
		if ($language1=='en' && $business_deliver_pickup_payment_desc[0]['redeemProcedure_en']) {
			$redeemProcedure=$business_deliver_pickup_payment_desc[0]['redeemProcedure_en'];				
		}else{
			$redeemProcedure=$business_deliver_pickup_payment_desc[0]['redeemProcedure'];		
		}
		
		if ($language1=='en' && $business_deliver_pickup_payment_desc[0]['refund_policy_en']) {
			$refund_policy=$business_deliver_pickup_payment_desc[0]['refund_policy_en'];				
		}else{
			$refund_policy=$business_deliver_pickup_payment_desc[0]['refund_policy'];	
		}
		
		$businessUser=$mdl_user->get( $order['business_userId'] );
		if($language1 =='en') {
			if ($businessUser['pickup_des_en']) {
				$pickup_des=$businessUser['pickup_des_en'];
			}else{
				$pickup_des=$businessUser['pickup_des'];
			}
			
			if ($businessUser['delivery_description_en']) {
				$delivery_description=$businessUser['delivery_description_en'];
			}else{
				$delivery_description=$businessUser['delivery_description'];
			}
			
			if ($businessUser['offline_pay_des_en']) {
				$offline_pay_des=$businessUser['offline_pay_des_en'];
			}else{
				$offline_pay_des=$businessUser['offline_pay_des'];
			}
		}else{
			$pickup_des=$businessUser['pickup_des'];
			$delivery_description=$businessUser['delivery_description'];
			$offline_pay_des=$businessUser['offline_pay_des'];
		}

	    if($delivery_option==2){
			if($language1 =='en') {
				$this->setData('pick up','delivery_option_desc');
            }else{
				$this->setData('自取','delivery_option_desc');
			}
			
	    	$this->setData($pickup_des,'delivery_description');
    		$business_staff = $mdl_user->get($order['business_staff_id']);
		  	if($business_staff){
		  		$this->setData($business_staff['contactPersonNickName'],'pickupnickname');
		  		$this->setData($business_staff['contactMobile'],'pickupphone');
		  		$this->setData($business_staff['googleMap'],'pickupaddress');
		  	}
	    }elseif($delivery_option==1){
	    	$this->setData($lang1->business_deliver,'delivery_option_desc');
	    	$this->setData($delivery_description,'delivery_description');
	    }else{
	    	$this->setData((string)$lang->face_to_face_payment,'delivery_option_desc');
	    }

		$this->setData($finePrint,'finePrint');
		$this->setData($refund_policy,'refund_policy');
		$this->setData($redeemProcedure,'redeemProcedure');
	    if($order['payment']=='offline'){
	    	$this->setData($offline_pay_des,'offline_pay_des');
	    }

		$staff =( $order['business_staff_id'] > 0 )?$mdl_user->get( $order['business_staff_id'] ):$mdl_user->get( $order['business_userId'] );
	  	$this->setData($staff['businessName'], 'businessName');
	  	$this->setData($staff['contactMobile'],'business_phone');
	  	$this->setData($staff['googleMap'],'business_address');
	    $this->setData($order['message_to_business'],'customer_message');
        
	  	return  $this->fetch( 'email/apply_confirmation_email' );
    }

    public function  businessOrderNotification($orderId,$language1)
    {
        $mdl_user = loadModel( 'user' );

        $order = loadModel( 'order' )->getByWhere(array('orderId'=>$orderId));

	  	$this->setData(date('Y-m-d H:i:s', $order['createTime']), 'createTime');
		if($language1='zh-cn'){
	  		$this->setData('新用户购买了您在Ubonus上的产品：', 'message');
		}else{
			$this->setData('Uesr purchased items ：', 'message');	
		}
	  	$this->setData($order['redeem_code'], 'redeemCode');
	  	$this->setData($order['orderId'],'order_id');
	  	$this->setData($order['order_name'], 'productName');
	  	$this->setData($this->getEmailComponent_orderDetail($order['orderId']),'itemTable');
	  	$this->setData($order['money'], 'productPrice');
	  	$this->setData($order['delivery_fees'], 'deliveryFees');
	  	$this->setData($order['booking_fees'], 'bookingFees');
	  	$this->setData($order['promotion_total'], 'promotionTotal');
	  	$this->setData($order['confirmedMoneyAppliedAmount'],'useMoney');
	  	$this->setData($order['surcharge'], 'surcharge');
	    $this->setData($order['payment'],'payment');
	    $this->setData($order['status'],'status');

	    $delivery_option=($order['customer_delivery_option']);
 		$this->setData($delivery_option,'delivery_option');
	  	if($delivery_option==2){
	    	if($language1 =='en') {
				$this->setData('pick up','delivery_option_desc');
            }else{
				$this->setData('自取','delivery_option_desc');
			}
			
	    	$business_staff = $mdl_user->get($order['business_staff_id']);
		  	if($business_staff){
		  		$this->setData($business_staff['nickname'],'pickupnickname');
		  		$this->setData($business_staff['googleMap'],'pickupaddress');
		  	}
	    }elseif($delivery_option==1){
	    	$this->setData((string)$this->lang->business_deliver,'delivery_option_desc');
	    }else{
	    	$this->setData((string)$this->lang->face_to_face_payment,'delivery_option_desc');
	    }
	  	
	  	$this->setData($order['first_name'].' '.$order['last_name'], 'customerName');
	    $this->setData($order['address'],'address');
	    $this->setData($order['phone'],'phone');
	    $this->setData($order['email'],'email');
	    $this->setData($order['postalcode'],'postalcode');
	    $this->setData($order['message_to_business'],'customer_message');
	  	
	  	return  $this->fetch( 'email/apply_confirmation_email_business' );
    }

    public function  customerCancelOrderNotification($orderId,$language1)
    {
        $order = loadModel( 'order' )->getByWhere(array('orderId'=>$orderId));
        $emailName =$this->getemailName($order);
        $this->setData($emailName, 'customerName');
      //  $this->setData(redeemQRCode($order['redeem_code']),'redeemQRCode');
        $this->setData($order['orderId'],'order_id');
        $this->setData(2,'type');//模板类别：2：取消订单
        $this->setData($order['status'],'status');

        echo  $this->fetch( 'email/email_template' );
        exit;
    }

    public function  businessCancelOrderNotification($orderId)
    {
        $order = loadModel( 'order' )->getByWhere(array('orderId'=>$orderId));
        $this->setData($order['first_name'].' '.$order['last_name'], 'customerName');
        $this->setData(redeemQRCode($order['redeem_code']),'redeemQRCode');
        $this->setData($order['orderId'],'order_id');
        $this->setData(3,'type');//模板类别
        $this->setData($order['status'],'status');

        $pic= loadModel('coupons')->getListBySql("SELECT cus.order_id,cou.pic FROM `cc_coupons` AS cou LEFT JOIN `cc_wj_customer_coupon` AS cus ON cou.id=cus.bonus_id WHERE cus.order_id='$orderId' LIMIT 4");
        if (count($pic)>0)
        {
            $this->setData($pic,'pic');
        }

        $delivery_option=($order['customer_delivery_option']);

        if($delivery_option==2){
            $this->setData('自取-pick up','delivery_option_desc');
        }elseif($delivery_option==1){
            $this->setData((string)$this->lang->business_deliver,'delivery_option_desc');
        }else{
            $this->setData((string)$this->lang->face_to_face_payment,'delivery_option_desc');
        }
        return  $this->fetch( 'email/email_template' );
    }

    public function  customerRegistryNotification($systemId)
    {
        $user = loadModel( 'user' )->get($systemId);
      
        $this->setData($user,'user');
       
      // return  $this->fetch( 'email/email_template_customer_registry' );
        return  $this->fetch( 'email/wide/html/account_welcome' );

    }

    public function  customerRegistryNotificationNew($user,$password)
    {
        $user = loadModel( 'user' )->get($user['id']);
        $user['decodepass'] =$password;

        $this->setData($user,'user');


        // return  $this->fetch( 'email/email_template_customer_registry' );
        return  $this->fetch( 'email/wide/html/account_welcome' );

    }

    public function emailVerificationCodeNotification($code)
    {
    	$this->setData($code,'code');

    	return  $this->fetch( 'email/wide/html/account_verification' );
    }

    public function  businessDeliveryNotification($orderId)
    {	
    	require_once( DOC_DIR.'static/4pxAPI.php' );

        $order = loadModel( 'order' )->getByWhere(array('orderId'=>$orderId));
        $this->setData($order['first_name'].' '.$order['last_name'], 'customerName');
        $this->setData(redeemQRCode($order['redeem_code']),'redeemQRCode');
        $this->setData($order['orderId'],'order_id');
        $this->setData(5,'type');//模板类别
        $this->setData($order['status'],'status');
        $this->setData($order['tracking_id'],'trackNo');

        if ($order['logisitic_schedule_time'] > 0) {
        	$this->setData(date('F j, Y, g:i a', $order['logisitic_schedule_time']),'eta');
        }

        if($order['tracking_operator']=='fastway'){
        	$this->setData("https://www.fastway.com.au/tools/track?l=".$order['tracking_id'],'trackingLink');
        }elseif($order['tracking_operator']=='auspost'){
        	$this->setData("https://auspost.com.au/parcels-mail/track.html#/track?id=".$order['tracking_id'],'trackingLink');
        }elseif(FourpxAPI::isFourpxTrackingOperator($order['tracking_operator'])){
        	$this->setData("http://track.4px.com/query/".$order['tracking_id'],'trackingLink');
        }

        $pic= loadModel('coupons')->getListBySql("SELECT cus.order_id,cou.pic FROM `cc_coupons` AS cou LEFT JOIN `cc_wj_customer_coupon` AS cus ON cou.id=cus.bonus_id WHERE cus.order_id='$orderId' LIMIT 4");
        if (count($pic)>0)
        {
            $this->setData($pic,'pic');
        }

        $delivery_option=($order['customer_delivery_option']);

        if($delivery_option==2){
            $this->setData('自取-pick up','delivery_option_desc');
        }elseif($delivery_option==1){
            $this->setData((string)$this->lang->business_deliver,'delivery_option_desc');
        }else{
            $this->setData((string)$this->lang->face_to_face_payment,'delivery_option_desc');
        }

        return  $this->fetch( 'email/email_template' );
    }

    public function  businessBalanceNotification($orderId)
    {//需要chris对接结算表来生成通知

    }

    public function customerSubscribeNotification($systemId)
    {//客户订阅邮件
        $this->setData(loadModel('info')->getListByClass('111',10,'ordinal'),'bannerData1');
        $this->setData(loadModel('info')->getListByClass('112',10,'ordinal'),'bannerData2');
		$coupon = loadModel('coupons')->getByWhere(array('id'=>5266));
        $this->setData($coupon,'coupon');
	    $user = loadModel('subscribe')->getByWhere(array('userid'=>$systemId));
		//var_dump($user);exit;
		
        $this->setData($user,'user');
        $this->setData(md5($user['email']),'sign');
	    $mdl_explosion = loadModel( 'explosion' );
        $this->setData($mdl_explosion->getListBySql("SELECT e.id,c.title,e.couponid,e.sort,pt.pagename,pnt.name,c.voucher_deal_amount,voucher_original_amount,c.pic FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId LEFT JOIN cc_pagetype AS pt ON pt.id=e.pagetype LEFT JOIN cc_panaltype AS pnt ON pnt.id=e.panaltype WHERE e.pagetype=6 ORDER BY sort"), 'explosion');
        return  $this->fetch( 'email/email_template_batch' );
    }

    private function getEmailComponent_orderDetail($orderId)
    {
    	$data =loadModel('wj_customer_coupon')->getItemsInOrder($orderId);
	  	$this->setData($data,'data');
	  	return $this->fetch('email/inc/order_item_table');
    }
}
?>