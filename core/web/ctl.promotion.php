<?php

class ctl_promotion extends cmsPage
{

    function ctl_promotion()
    {
        parent::cmsPage();
    }
	
	function  index_action (){ //接收参数显示相关的 promotion 页面
	
		
		$getpromotion = trim(get2('getpromotion'));
		
		
		
		$this->setData('Ubonus 优惠券', 'pagename');
        $this->setData('优惠券专享 - ' . $this->site['pageTitle'], 'pageTitle');
		
		
		$freshfood_lists ="(219383,219199,218997,218856,218812,218800,218772,218767,218643,218639,218630,218517,217777,217364,217093,217005)";
        $sql ="SELECT u.id, u.`name`,u.displayName,c.pic,c.categoryId,m.promotion_desc   FROM `cc_user` u left join cc_coupons as c on u.id =c.createUserId left join cc_restaurant_promotion_manjian m on u.id=m.restaurant_id 

 WHERE u.`business_type_freshfood` =1 and c.EvoucherOrrealproduct='restaurant_menu'  and u.id 
 in $freshfood_lists
 order by categoryId  desc";
	    
		$mdl_user =$this->loadModel('user');
		$data = $mdl_user->getListBySql($sql);
		
		
		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        $shareUrl = HTTP_ROOT_WX."promotion/index";
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');
        
        $this->setData( get2('action'), 'returnAction' );

        $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');

		
		
		// 检查用户是否已经领取了优惠券
		if ($this->loginUser['id']) {
			$mdl_wj_promotion_code =$this->loadModel('wj_promotion_code');
			$sql =" select count(*) as count from cc_wj_promotion_code where real_user_id =".  $this->loginUser['id'] . " and promotion_des ='Ubonusfresh2020'";
			$user_promotion_count = $mdl_wj_promotion_code->getListBySql($sql);
			
		
		  if ($user_promotion_count[0]['count']>=3) {
			  
			  $getpromotion=2;
			  
		  }
	}
		
		//var_dump($sql);exit;
		$this->setData($data,'data');
		
		$ua =$this->getUserDevice();
			if ($ua=='wechat') {
				
				$this->setData($ua,'ua');
			}
		//	var_dump($getpromotion);exit;
		$this->setData($getpromotion,'getpromotion');
		$this->display('promotion/index');
	}
	
	
	
	function  	new_promotion_code_action(){  // 接收指令产生优惠码
		 
		if (!$this->loginUser) {
            $this->sheader(HTTP_ROOT_WWW . 'member/login?returnUrl=' . urlencode($_SERVER['REQUEST_URI']));
        }
		
		
		$getpromotion =get2('getpromotion');
		
		
		
		$mdl_wj_promotion_code =$this->loadModel('wj_promotion_code');
		
		
		//gencode
		
		$rnd=$this->createRndStr(10);
		//var_dump($rnd);exit;
		
		
		
		//加入检测代码，该用户是否已经领取过，如果领取过，则提示已经领取过了。
		
		
		$sql =" select count(*) as count from cc_wj_promotion_code where real_user_id =".  $this->loginUser['id'] . " and promotion_des ='Ubonusfresh2020'";
		$user_promotion_count = $mdl_wj_promotion_code->getListBySql($sql);
		//var_dump($user_promotion_count[0]['count']);exit;
	  if ($user_promotion_count[0]['count']>=3) {
		  
		  $this->sheader(HTTP_ROOT_WWW . 'promotion/index?getpromotion=2');
		  
	  }else {
		
		$data1 =array(
		 'promotion_des'=>'Ubonusfresh2020',
		 'user_id'=>'-1',
		 'coupon_id'=> '-1',
		 'gen_time'=> time(),
		 'is_expired'=> 0,
		 'type'=>'fixed' ,
		 'value'=>7 ,
		 'apply_condition'=>'none' ,
		 'apply_condition_value'=>'0',
		 'expire_type'=>'unlimited',
		 'expire_value'=>30,
		 'applied_times'=>0,
		 'promotion_code'=>$rnd,
		 'is_for_public'=>'0',
		 'single_use_per_user'=>1,
		 'real_user_id'=> $this->loginUser['id']
		);
		
		$mdl_wj_promotion_code->insert($data1);
		
		
		$rnd=$this->createRndStr(10);
		$data1['value']=5;
		$data1['promotion_code']=$rnd;
		$mdl_wj_promotion_code->insert($data1);
		
		$data1['value']=3;
		$rnd=$this->createRndStr(10);
		$data1['promotion_code']=$rnd;
		$mdl_wj_promotion_code->insert($data1);
		
		
		$this->sheader(HTTP_ROOT_WWW . 'promotion/index?getpromotion=1');
	  }
	  
	  
		
		
		
		 
	 }
	 
	
private  function createRndStr ($length = 6)
	{
		$rnd = '';
		$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		while (strlen($rnd) < $length)
		{
			$rnd .= $str[rand( 0, strlen( $str ) - 1 )];
		}
		if (strlen($rnd) > $length) $rnd = left($rnd, $length);
		return $rnd;
	}

}