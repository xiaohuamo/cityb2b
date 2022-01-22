<?php

class ctl_index_old extends cmsPage
{
	function index_action() {
		
		$type=get2('display_type');
	
     	$refTag = trim(get2('reftag'));
		
	    $this->setData($refTag,'reftag');
		
/*
		
		if (!$type) {
		
		if($this->getUserDevice()!='desktop'){  

		$this->display('mobile/index');
		 exit;
		}
		
		}
		
*/
        $mdl_coupons = $this->loadModel('coupons');

        $currentTime=strtotime ('now');
	    //$cityid=','.$this->city['id'].',';
        $this->setData($this->loadModel('info')->getListByClass('107',10,'ordinal'),'bannerData');
        $this->setData($this->loadModel('info')->getListByClass('113',10,'ordinal'),'bannerDataMobile');


       if($this->getLangStr()=='en')
       {
            $lang=" and lang=1";
       }
      else
      {
            $lang=" and lang=0";
      }

   
	

      if($this->cookie->getCookie('bannerOpened')){
            $this->setData( $this->cookie->getCookie('bannerOpened'),'bannerOpened');
      }else{
             $this->cookie->setCookie('bannerOpened','true',3600*24);
      }

	    $bid = trim(get2('bid'));

            //save bid in cookie
            if(!$bid){
              $bid = $this->cookie->getCookie('store_display_bid');
			 // var_dump('bid is null and get from cookie: ' .$bid);exit;
            }else{
              $this->cookie->setCookie( 'store_display_bid', $bid, 60 * 60 * 24 * 180);
			 // var_dump($bid);exit;
            }
	      
	   
	  $mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");
	  
	  
	  
	  		// 获取最新的10个2级验证商家的一个产品。
			
			$sql_new_listing ="select a.id,a.EvoucherOrrealproduct,b.pic as pic1,a.createUserId,a.pic, ((a.voucher_original_amount-a.voucher_deal_amount)/a.voucher_original_amount)*100 as priceoff,a.title,a.title_en,a.hits,a.voucher_deal_amount,a.voucher_original_amount from cc_coupons a ,cc_user b where a.isApproved=1 and a.status=4 ". $this->get_multiLanguage_where('a')." and b.id=a.createUserId and b.trustlevel=2 order by a.createTime desc  ";
			$new_listing = $mdl_coupons->getListBySql($sql_new_listing);
			//var_dump($sql_new_listing);exit;
			$business_count=0;
			$business_old=0;
			$business_new=0;
			$new_listing_dispay_data =array();
			
			foreach ($new_listing as $key => $value) {
				if($business_count==10) break;
				$business_new = $value['createUserId'];
				if ($business_new <> $business_old) {
					$new_listing_dispay_data[$business_count]['id']=$value['id'];
					$new_listing_dispay_data[$business_count]['pic']=$value['pic'];
					 if($this->getLangStr()=='en' && $new_listing[$key]['title_en']){
							$new_listing_dispay_data[$business_count]['title']=$value['title_en'];
					 }else{
							$new_listing_dispay_data[$business_count]['title']=$value['title'];
						}
					$new_listing_dispay_data[$business_count]['hits']=$value['hits'];
					$new_listing_dispay_data[$business_count]['price1']=$value['voucher_deal_amount'];
					$new_listing_dispay_data[$business_count]['price2']=$value['voucher_original_amount'];
					$new_listing_dispay_data[$business_count]['EvoucherOrrealproduct']=$value['EvoucherOrrealproduct'];
					$new_listing_dispay_data[$business_count]['pic1']=$value['pic1'];	
					$business_count ++;
					
					$business_old=$business_new;
				}
				
				
			}
			$this->setData($new_listing_dispay_data,'new_listing');
			
          //  var_dump($new_listing_dispay_data);exit;
		if($this->getUserDevice()=='desktop'){  
            //$id=(get2('cityid')==null)?1:get2('cityid');(c.city LIKE '%$cityid%' OR c.city='')

            $mdl_group_pin=$this->loadModel('group_pin');
            $sql = "Select c.title,c.pic,c.voucher_deal_amount,c.voucher_original_amount, gp.coupon_id,gp.group_size_each,gp.group_size_total,gp.reward_type,gp.reward_value from cc_group_pin as gp left join cc_coupons as c on c.id = gp.coupon_id where c.isApproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) ORDER BY gen_date DESC limit 9";
            $group_pin_list=$mdl_group_pin->getListBySql($sql);
            $this->setData($group_pin_list, 'GroupPurchase');



            $list58=$this->get_data("SELECT c.createUserId,u.pic as pic1,u.logo,e.type,e.isStore,c.createUserId,c.id, c.title,c,title_en, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.categoryId,e.panaltype,c.city FROM cc_explosion e LEFT JOIN cc_coupons c ON (c.id=e.couponid or c.createUserId=e.couponid ) LEFT JOIN cc_user u ON u.id=c.createUserId WHERE c.isapproved=1 ". $this->get_multiLanguage_where('c')." and c.status=4  AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) AND e.pagetype=2 and e.panaltype=58 ORDER BY sort limit 9");
            $this->setData($list58,'list58');



            $list=$this->get_data("SELECT c.coupon_summery_description,u.pic as pic1,u.logo,e.type,e.isStore,c.EvoucherOrrealproduct,c.createUserId,c.id, c.title, c.title_en,c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.bonusType,c.categoryId,e.panaltype,c.city FROM cc_explosion e LEFT JOIN cc_coupons c ON (c.id=e.couponid or c.createUserId=e.couponid ) LEFT JOIN cc_user u ON u.id=c.createUserId WHERE c.isapproved=1 ". $this->get_multiLanguage_where('c')."  and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) AND  (e.pagetype=3 OR e.pagetype=4 OR e.pagetype=1)  ORDER  BY sort");

			
	
			foreach ($list as $key => $value) {
              $mdl_coupons->caculatePriceAndPoint($list[$key]);
			  
			  if($this->getLangStr()=='en') {
				   if ($list[$key]['title_en']) {
					   $list[$key]['title'] =  $list[$key]['title_en'];
					   
				   }
				  
			  }
			  //检查该coupon 是否为线上餐厅
			
			if ($list[$key]['EvoucherOrrealproduct']=='restaurant_menu'){
				  $manjian=$mdl_restaurant_promotion_manjian->getbyWhere(array('restaurant_id'=>$list[$key]['createUserId']));
			  	// 如果是线上餐厅 ,加入显示线上餐厅deal的信息
				
				if($manjian['discount']>0) {
					$list[$key]['promotion_desc'] ='全店满减<strong>'.$manjian['discount'].'%Off</strong>';
					
				}
				if($manjian['promotion_desc']) {
					$list[$key]['promotion_desc'] .=$manjian['promotion_desc'];
					
				}
				   
				
			}
            }

            $this->setData($list,'data');

         
            
			  if($this->getLangStr()=='en'){
	  			$this->setData('cityb2b masks  Hand sanitizer  ','h1_footer'); 
			 }else{
				 $this->setData('墨尔本 团购 | 墨尔本 打折','h1_footer');
				
			 }
			
			if($type==1) {
					$this->display('index-pc');
		
			}else{
			        $this->display('index-pc');
			}

		}else{
            $mdl_group_pin=$this->loadModel('group_pin');
            $sql = "Select c.title,c.pic,c.voucher_deal_amount,c.voucher_original_amount, gp.coupon_id,gp.group_size_each,gp.group_size_total,gp.reward_type,gp.reward_value from cc_group_pin as gp left join cc_coupons as c on c.id = gp.coupon_id where c.isApproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) ORDER BY gen_date DESC limit 9";
            $group_pin_list=$mdl_group_pin->getListBySql($sql);
            $this->setData($group_pin_list, 'GroupPurchase');



            $list=$this->get_data("SELECT u.logo,u.pic as pic1,e.type,e.isStore,c.EvoucherOrrealproduct,c.createUserId,c.id, c.title, c.title_en,c.pic,c.bonusType,c.coupon_summery_description, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.categoryId,e.panaltype,c.city FROM cc_explosion e LEFT JOIN cc_coupons c ON (c.id=e.couponid or c.createUserId=e.couponid ) LEFT JOIN cc_user u ON u.id=c.createUserId WHERE c.isapproved=1 ". $this->get_multiLanguage_where('c')." and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) AND  (e.pagetype=4 OR e.pagetype=1 OR e.pagetype=3 )  ORDER  BY sort");

            foreach ($list as $key => $value) {
              $mdl_coupons->caculatePriceAndPoint($list[$key]);
			  
			   if($this->getLangStr()=='en') {
				   if ($list[$key]['title_en']) {
					   $list[$key]['title'] =  $list[$key]['title_en'];
					   
				   }
				  
			  }
			   // var_dump($list);exit;
			  if ($list[$key]['EvoucherOrrealproduct']=='restaurant_menu'){
				  $manjian=$mdl_restaurant_promotion_manjian->getbyWhere(array('restaurant_id'=>$list[$key]['createUserId']));
			  	// 如果是线上餐厅 ,加入显示线上餐厅deal的信息
				
				if($manjian['discount']>0) {
					$list[$key]['promotion_desc'] ='全店满减<strong>'.$manjian['discount'].'%Off</strong>';
					
				}
				if($manjian['promotion_desc']) {
					$list[$key]['promotion_desc'] .=$manjian['promotion_desc'];
					
				}
				   
				
			}
            }

            $this->setData($list,'data');
			//var_dump($list);exit;
			
			  if($this->getLangStr()=='en'){
	  			$this->setData('cityb2b masks  Hand sanitizer  ','h1_footer'); 
			 }else{
				 $this->setData('墨尔本 团购 | 墨尔本 打折','h1_footer');
				
			 }
			
            //千人千面
            $mdl_user=$this->loadModel('user');

          

            $buser = $mdl_user->get($bid);

            //auto watch bid
            if($buser&&$this->loginUser){
              if(!$this->loadModel('fav')->exist($this->loginUser['id'], $bid, 'store'))
                $this->loadModel('fav')->add($this->loginUser['id'], $bid, 'store');
            }

            if($buser&&$buser['role'==3]){
              $businessUser=$buser;
            }elseif($this->loginUser['role']==3){
              $businessUser=$this->loginUser;
            }

            if($businessUser){
              $this->setData($businessUser,'businessUser');
              $this->setData($mdl_user->getBusinessDisplayName($businessUser['id']),'businessDisplayName');

              $w['isApproved']=1;
              $w['status']=4;
              $currentTime=strtotime ('now');
              $w[]=" !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime)) ";
              $w['createUserId']=$businessUser['id'];
              $data = $mdl_coupons->getList( null, $w, null, 4);

              $referral_product_list =$this->loadModel('referral_product_program')->getAvailableProductsByCustomCategory($businessUser['id'],$cc,true,4);

              foreach ($referral_product_list as $key =>$value) {
                  $mdl_coupons->caculatePriceAndPoint($referral_product_list[$key]);
              }

              $data = array_merge($data,$referral_product_list);

              $this->setData($data,'businessCouponList');

              $this->setData($this->loadModel('wj_customer_rating')->getAvgScore($businessUser['id']),'score_avg');
              $this->setData($mdl_user->getBusienssNotice($businessUser['id']),'busienssNotice');
            }


            //wx share
            require_once "wx/wxjssdk.php";
            $jssdk = new WXjsSDK();
            $signPackage = $jssdk->GetSignPackage();
            $this->setData($signPackage,'signPackage');

            $this->setData( 'index', 'mobile_menu' );
	    if($type==1) {
					$this->display('mobile/index');
		
			}else{
			        $this->display('mobile/index');
			}
			  
		}
	}
	
	public function get_miaoqiang_data(){
		
		  $mdl_coupons = $this->loadModel('coupons');
		  
		
	}

    private function get_data($sql)
    {
        $mdl_coupons= $this->loadModel("coupons");
        return $mdl_coupons->getListBySql($sql);
    }
}