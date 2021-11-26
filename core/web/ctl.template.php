<?php
	class ctl_template extends cmsPage{
		
		

		function index_action(){
			if($this->getUserDevice()!='desktop'){
				$this->_index_mobile();
			}else{
				$this->_index_pc();
			}
		}
		
		function template_show_action(){
			$this->index_action();
		}

		private function _index_pc(){
			
			$reftag = trim(get2('reftag'));
			$this->setData($reftag,'reftag');
			$businessUserId = trim(get2('id'));
	        $mdl_coupon = $this->loadModel('coupons');
	        $mdl_user = $this->loadModel('user');


            //转tvb 
			
			if ($businessUserId ==23965) { 
				$businessUserId =218775;
			}
	        $businessUser=$mdl_user->get($businessUserId);

	       // if(!$mdl_user->isBusiness($businessUserId))$this->sheader(null, '商家不存在');

	        $this->setData($businessUser,'businessUser');
			$businessName01=$mdl_user->getBusinessDisplayName($businessUserId,$this->getLangStr());

	        $this->setData($businessName01,'businessDisplayName');

	        $this->setData($mdl_user->getPickupLocationName($businessUserId,$this->getLangStr()),'pickupLocationName');

	        /**
	         * 商家名称
	         */
	       
	        $this->setData($businessName01,'businessDisplayName');
			 $this->setData($businessUser['busi_weixin_scancode'],'busi_weixin_scancode');
			

	        /**
	         * 商品分类
	         */
	        $mdl_cc= $this->loadModel('customizableCategory');
	        $mdl_cc->setUserId($businessUserId);
	        $customizableCategory =$mdl_cc->getCategoryTree();
			foreach ($customizableCategory as $key =>$value) {
				if($this->getLangStr()=='en') {
					if( $customizableCategory[$key]['name_en']) {
						
						$customizableCategory[$key]['name']=$value['name_en'];
					}
					
				}
				
				
			}
			
	        $this->setData($customizableCategory,'customizableCategory');
			
			//var_dump($customizableCategory);exit;
			
			
		
			if($businessUser['business_type_miss']==1) {
			
					// 获得当前佳丽运营商所有列表
					
					$sql ="select id,person_first_name,displayName,businessName,(select count(*) from cc_coupons b where b.createUserId=a.id and b.isApproved=1 and b.status=4) as count  from cc_user a  where user_belong_to_agent= 210362 and isSuspended=0 ";
					
					$customizableCategory=$mdl_coupon->getListBySql($sql);
					foreach ($customizableCategory as $key =>$value) {
						if($customizableCategory[$key]['displayName']) {
							$customizableCategory[$key]['name']= $customizableCategory[$key]['displayName'];
							
						}else{
							$customizableCategory[$key]['name']= $customizableCategory[$key]['businessName'];
						}
						
					}
					//var_dump($customizableCategory);exit;
					$this->setData($customizableCategory,'customizableCategory');
					/*佳丽商家处理结束*/
					
					
					}
					
		
	        /**
	         * 商品数据
	         */
	        $orderBy = trim(get2('orderBy'));
	        if(!$orderBy)$orderBy='id';//$orderBy='bonusType';
	        $cc = trim(get2('cc'));


	        $this->setData($orderBy,'orderBy');
	        $this->setData($cc,'cc');

	        $this->setData($this->parseUrl()->set('orderBy')->set('cc'),'orderByUrl');
	        $this->setData($this->parseUrl()->set('orderBy')->set('cc'),'ccUrl');

	        $column = array('title','title_en','coupon_summery_description','id','pic','hits',
	            'bonusType','voucher_deal_amount','voucher_original_amount');

	        $where['isApproved']=1;
	        $where['status']=4;
            $currentTime=strtotime ('now');
	        $where[]=" !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime)) ";

	        $where['createUserId']=$businessUserId;

	        if($orderBy)
            {
	            $order = $orderBy." DESC " ;
            }
	        if($cc){
	            $data = $mdl_coupon->getCouponListByCustomCategory($businessUserId,$cc);
	            $this->setData($this->loadModel('customizableCategory')->get($cc),'selectedCustomizableCategory');
	        }else{
	            $data = $mdl_coupon->getList( $column, $where, $order, 500);
	        }

			/**
			如果该商家为佳丽则再增加一组数据集
			**/
			
				/*  如果商家是华姐选美佳丽
			
			
			
			('title','coupon_summery_description','id','pic','hits',
						'bonusType','voucher_deal_amount','voucher_original_amount');
			*/
			
			if($businessUser['business_type_miss']==1) {
			
			if($cc){
				
				$sql= " select title, title_en,coupon_summery_description,id,pic,hits,bonusType,voucher_deal_amount,voucher_original_amount from cc_coupons where createUserId =$cc  and isApproved=1 and status=4 limit 100";
				
			}else{
				$sql= " select title, title_en,coupon_summery_description,id,pic,hits,bonusType,voucher_deal_amount,voucher_original_amount from cc_coupons where createUserId in( select id from cc_user where user_belong_to_agent = 210362) and isApproved=1 and status=4 limit 100";
		
			}
			
			$data=$mdl_coupon->getListBySql($sql);
			
			//var_dump($sql);exit;
			
			}
			/**end**/
			
	        foreach ($data as $key =>$value) {
	            
				$mdl_coupon->caculatePriceAndPoint($data[$key]);
			     if($this->getLangStr()=='en') {
					if( $data[$key]['title_en']) {
						
						$data[$key]['title']=$value['title_en'];
					}
					
				}

			}

			
			
			

	        /**
	  		 * referral_product_program
	  		 */
	  		$referral_product_list =$this->loadModel('referral_product_program')->getAvailableProductsByCustomCategory($businessUserId,$cc);

	  		foreach ($referral_product_list as $key =>$value) {
	            $mdl_coupon->caculatePriceAndPoint($referral_product_list[$key]);
	        }

	  		$this->setData($referral_product_list,'referral_product_list');

	        $this->setData($data,'coupon');
	        /**
	         * fav
	         */
	        if(get2('autoaction')=='autowatch')
	            if( $this->loginUser )
	                if(!$this->loadModel('fav')->exist($this->loginUser['id'], $businessUserId, 'store'))
	                    $this->loadModel('fav')->add($this->loginUser['id'], $businessUserId, 'store');


	        if ( $this->loginUser )
	            $this->setData($this->loadModel('fav')->exist($this->loginUser['id'], $businessUserId, 'store'), 'faved');


	        $this->setData($mdl_user->getBusienssNotice($businessUserId),'busienssNotice');

	        
	        if($businessUser['IsTransform'])$this->setData($businessUser['id'],'businessChatId'); //overwrite basecontroller  default: Harry' Id as ubonus support
			
			
			//获取页面tilte 描述等动态信息
			$this->set_pagetitle($businessUser['cityId'],$data,$businessName01);
			
			
			
	        $this->display('merchantstore/index');
		}
		
		
		private function set_pagetitle($cityId,$data,$businessName01){
			
			//获得该用户的城市名称序列
			
			$cityNameArray= $this->loadModel('city')->getCitynameandParentCityname($cityId,$this->getLangStr());
			
			if($cityNameArray[0]){
				
			//	$title =$businessName01 . ' '. $cityNameArray[0];
			$title =$businessName01;
				$desc=$title;
			}
			
			if($cityNameArray[1]){
				
				if($cityNameArray[0]) {
					$title .=' | '.$businessName01. ' '.$cityNameArray[1] ;
				}else{
					$title =$businessName01 . ' '. $cityNameArray[0];
				}
				
			}
		   
		    $i=0;
		    foreach($data as $key=>$value){
				$i++;
			
				if($i==1){
					//将影响seo格式的字符替换掉
					$title01=$data[$key]['title'];
	              	$title01=str_replace("|","",$title01);
		            $title01=str_replace(","," ",$title01);
					$desc1 = $title01;
				}else{
					$title01=$data[$key]['title'];
	              	$title01=str_replace("|","",$title01);
		            $title01=str_replace(","," ",$title01);
					$desc1 .=', '.$title01;
					//var_dump($desc1);exit;	
				}
				
				if($i==5)break;
				
			}
			//var_dump($desc1);exit;		
			
			$this->setData( $desc, 'h1' );
            if($desc1) {
				$desc= $desc.', '.$desc1;
			}					
            $this->setData( $title, 'pageTitle' );
		    $this->setData( $title, 'pageKeywords' );
		    $this->setData($desc,'pageDescription');
			
		}
		
		private function _index_mobile(){
			$businessUserId = trim(get2('id'));
			//zhuan tvb
			if ($businessUserId ==23965) {
				$businessUserId =218775;
			}
			$reftag = trim(get2('reftag'));
			$this->setData($reftag,'reftag');
			
			//放置调用链接是否来自开店程序
			$open_shop = trim(get2('open_shop'));
			$this->setData($open_shop,'open_shop');

			$mdl_coupon = $this->loadModel('coupons');
			$mdl_user = $this->loadModel('user');

			$businessUser=$mdl_user->get($businessUserId);
			
			//if(!$mdl_user->isBusiness($businessUserId))$this->sheader(null, '商家不存在');

			$this->setData($businessUser,'businessUser');

		    $businessName01=$mdl_user->getBusinessDisplayName($businessUserId);

	        $this->setData($businessName01,'businessDisplayName');

	        $this->setData($mdl_user->getPickupLocationName($businessUserId),'pickupLocationName');

	        /**
	         * 商家名称
	         */
	       
	      
			/**
			 * 商品数据
			 */
			$orderBy = trim(get2('orderBy'));
			if(!$orderBy)$orderBy='id';
			$cc = trim(get2('cc'));

			$this->setData($orderBy,'orderBy');
			$this->setData($cc,'cc');

			$this->setData($this->parseUrl()->set('orderBy')->set('cc'),'orderByUrl');
			$this->setData($this->parseUrl()->set('orderBy')->set('cc'),'ccUrl');

			$column = array('title','title_en','coupon_summery_description','id','pic','hits',
						'bonusType','voucher_deal_amount','voucher_original_amount');

			$where['isApproved']=1;
	        $where['status']=4;
            $currentTime=strtotime ('now');
	        $where[]=" !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime)) ";

	        $where['createUserId']=$businessUserId;
			
			if($orderBy)$order = $orderBy." DESC " ;

			if($cc){
				$data=$mdl_coupon->getCouponListByCustomCategory($businessUserId,$cc);
				$this->setData($this->loadModel('customizableCategory')->get($cc),'selectedCustomizableCategory');
			}else{
				$data	= $mdl_coupon->getList( $column, $where, $order, 100);
			}
			
			
			/*  如果商家是华姐选美佳丽
			
			
			
			('title','coupon_summery_description','id','pic','hits',
						'bonusType','voucher_deal_amount','voucher_original_amount');
			*/
			
			if($businessUser['business_type_miss']==1) {
			
			if($cc){
				
				$sql= " select title,title_en,coupon_summery_description,id,pic,hits,bonusType,voucher_deal_amount,voucher_original_amount from cc_coupons where createUserId =$cc  and isApproved=1 and status=4 limit 100";
				
			}else{
				$sql= " select title,title_en,coupon_summery_description,id,pic,hits,bonusType,voucher_deal_amount,voucher_original_amount from cc_coupons where createUserId in( select id from cc_user where user_belong_to_agent = 210362) and isApproved=1 and status=4 limit 100";
		
			}
			
			$data=$mdl_coupon->getListBySql($sql);
			
			//var_dump($sql);exit;
			
			
			
			
			// 获得当前佳丽运营商所有列表
			
			$sql ="select id,person_first_name,displayName,businessName,(select count(*) from cc_coupons b where b.createUserId=a.id and b.isApproved=1 and b.status=4) as count  from cc_user a  where user_belong_to_agent= 210362 and isSuspended=0 ";
			
			$customizableCategory=$mdl_coupon->getListBySql($sql);
			foreach ($customizableCategory as $key =>$value) {
				if($customizableCategory[$key]['displayName']) {
					$customizableCategory[$key]['name']= $customizableCategory[$key]['displayName'];
					
				}else{
					$customizableCategory[$key]['name']= $customizableCategory[$key]['businessName'];
				}
				
			}
			//var_dump($customizableCategory);exit;
			$this->setData($customizableCategory,'customizableCategory');
			/*佳丽商家处理结束*/
			
			}
        
	  		foreach ($data as $key =>$value) {
	  			$mdl_coupon->caculatePriceAndPoint($data[$key]);
				if($this->getLangStr()=='en') {
				   if ($data[$key]['title_en']) {
					   $data[$key]['title'] =  $data[$key]['title_en'];
					   
				   }
				  
			  }
	  		}

	  		$this->setData($data,'coupon');

          // var_dump($data);exit;
	  		/**
	  		 * referral_product_program
	  		 */
	  		$referral_product_list =$this->loadModel('referral_product_program')->getAvailableProductsByCustomCategory($businessUserId,$cc);

	  		foreach ($referral_product_list as $key =>$value) {
	            $mdl_coupon->caculatePriceAndPoint($referral_product_list[$key]);
	        }
	        
	  		$this->setData($referral_product_list,'referral_product_list');


	  		/**
	  		 * 商品分类
	  		 */
	  		if(!$businessUser['business_type_miss']) {
			
	  		$mdl_cc= $this->loadModel('customizableCategory');
	  		$mdl_cc->setUserId($businessUserId);
			$customizableCategory =$mdl_cc->getCategoryTree();

			$this->setData($customizableCategory,'customizableCategory');
			}
	  		/**
			 * 优惠券数据
			 */
	  		$column = array('title','title_en','coupon_summery_description','id','pic','hits',
						'bonusType','voucher_deal_amount','voucher_original_amount');
			$where 	= array('isApproved' => 1,
							'status' => 4
							 );
			$where[]=" (bonusType=2 or bonusType=4 ) and createUserId=$businessUserId";

			$order = '';

	  		$data= $mdl_coupon->getList($column,$where,$order,8);
	  		$this->setData($data,'voucher');

	  		/**
	  		 * 折扣码
	  		 */
	        $data=$this->loadModel('wj_promotion_code')->getList(null,"( is_for_public=1) and is_expired=0");
	        $this->setData($data,'promotion_code');


	        /**
	  		 * 社区
	  		 */
	  		
	  		$sql = "SELECT g.id,g.name,g.status,g.max_user_group,g.condition_level,g.allow_user_group,c.pic FROM `cc_group_buy_status` as g left join cc_coupons as c on g.coupon_id=c.id";

			$sql .= " where g.parentId=0 and g.status !=5 and g.status !=2 "; //child 自开团不显示 //已完成不显示//关闭的不显示

			$sql .= " ORDER BY
					   CASE g.status
					      WHEN '1' THEN 1
					      WHEN '3' THEN 2
					      WHEN '4' THEN 3
					      WHEN '5' THEN 4
					      WHEN '2' THEN 5
					      WHEN '0' THEN 6
					   END, id desc";

			$list = $this->loadModel('group_buy')->getListBySql($sql);

			foreach ($list as $key => $value) {
				$list[$key]['size'] = end(array_keys(unserialize($value['condition_level'])));
			}

			$this->setData($list,'group');
		  		

	        /**
	  		 * 商家
	  		 */
	  		$this->setData($this->loadModel('wj_customer_rating')->getAvgScore($businessUserId),'score_avg');


	  		$this->setData($mdl_user->getBusienssNotice($businessUserId),'busienssNotice');
	  		
	  		/**
	  		 * fav
	  		 */
	  		if(get2('autoaction')=='autowatch')
				if( $this->loginUser ) 
					if(!$this->loadModel('fav')->exist($this->loginUser['id'], $businessUserId, 'store'))
						$this->loadModel('fav')->add($this->loginUser['id'], $businessUserId, 'store');
			

			if ( $this->loginUser ) 
				$this->setData($this->loadModel('fav')->exist($this->loginUser['id'], $businessUserId, 'store'), 'faved');


			//wx share
			require_once "wx/wxjssdk.php";
	        $jssdk = new WXjsSDK();
	        $signPackage = $jssdk->GetSignPackage();
	        $this->setData($signPackage,'signPackage');
        
	        if($businessUser['IsTransform'])$this->setData($businessUser['id'],'businessChatId'); //overwrite basecontroller  default: Harry' Id as ubonus support
			
			$sql= "select title,title_en from cc_coupons where createUserId =" .$businessUser['id'];
			$data1= $mdl_coupon->getListBySql($sql);
			
        	$this->set_pagetitle($businessUser['cityId'],$data1,$businessName01);
			$this->display('merchantstore/store_mobile');
		}
}
?>