<?php 
class ctl_food extends cmsPage
{

	public function index_action()
	{	
		
		// 获得是否有推荐人,如果有推荐人,生成的查询链接都会加相关的tag
		
		$reftag = trim( get2( 'reftag' ) );
		$this->setData( $reftag, 'reftag' );
		
		/**
		 * 产品分类
		 * @var [type]
		 */
		$alias = trim( get2( 'alias' ) );
		
		if (!$alias)$alias='106126';
		$this->setData( $alias,'alias');

		 $currentTime=strtotime ('now');
		
		$restaurant_menu = trim( get2( 'restaurant_menu' ) );
	
		if($restaurant_menu =='restaurant_menu') {
			$this->setData( '线上餐厅','restaurant_menu');
			$where_restaurant = " and c.EvoucherOrrealproduct = 'restaurant_menu' ";
			
		}else{
		   $where_restaurant = " and c.EvoucherOrrealproduct <> 'restaurant_menu' ";	
			
		}
		/**
		 * 排序规则
		 * @var [type]
		 */
		$orderby = trim( get2( 'orderby' ) );
		if ( ! in_array( $orderby, array( 'default','id', 'hits', 'buy','pricehigh','pricelow' ) ) ) $orderby = 'default';

		$this->setData( $orderby, 'orderby' );


		/**
		 * 搜索关键字
		 * @var [type]
		 */
		$searchKeywords = trim( get2( 'key' ) );
		if(!$searchKeywords)$searchKeywords='';

		$this->setData( $searchKeywords, 'searchKeywords' );

		/**
		 * 过滤coupon type
		 * @var [type]
		 */
		$couponType = trim( get2( 'couponType' ) );
		if ( ! in_array( $couponType, array( '2','4', '7', '9','10' ) ) ) $couponType = '';

		$this->setData( $couponType, 'couponType' );


		/**
		 * 经纬度
		 */
		$latitude=get2('latitude');
		$longitude=get2('longitude');

		/**
		 * cityId
		 */
		$cityId= get2('cityid');
		if($cityid)$cityid='556';//default melbourne

		/**
		 * food coupons_addon search
		 */

		$coupons_addon_search = false;


		/**
		 * get whole page
		 */
		$wholePage=get2('wholepage');
		if(!$wholePage){
			$wholePage=false;
		}else{
			$wholePage=true;
		}

		$meal_type=get2('meal_type');
		$guest_limit=get2('guest_limit');
		$apportmant_required=get2('apportmant_required');
		$time_limit=get2('time_limit');
		$available_on_holiday=get2('available_on_holiday');
		$sharable=get2('sharable');
		$private_room=get2('private_room');

		if($meal_type||$guest_limit||$apportmant_required||$time_limit||$available_on_holiday||$sharable||$private_room)
			$coupons_addon_search=true;



		/**
		 * 产品分类模组
		 */
		$mdl_infoclass = $this->loadModel( 'infoClass' );

		/**
		 * 产品类型模组
		 */
		$mdl_coupon_type = $this->loadModel('coupon_type');

		/**
		 * 高级检索模组
		 */
		$mdl_advancedKeySearch=$this->loadModel('advancedKeySearch');

		/**
		 * 产品模组
		 */
		$mdl_coupons = $this->loadModel( 'coupons' );

		/**
		 * 用户模组
		 */
		$mdl_user = $this->loadModel( 'user' );
		
		/**
		 * 评价模组
		 */
		$mdl_customer_rating =$this->loadModel('wj_customer_rating');

      

		/**
		 * 页面分类动态显示
		 */
		$category=$mdl_infoclass->getByAlias( $alias );//single

		$parent_category = $mdl_infoclass->getParentListArray( $category['id'] );//array

		$child_category = $mdl_infoclass->getChild4( $category['id'] );//array
		if(!$child_category)$child_category=$mdl_infoclass->getChild4(substr($category['alias'], 0,-3));

		$this->setData( $category, 'category' );

		$this->setData( $parent_category, 'parents' );

		$parent = $mdl_infoclass->getByAlias( 106126);
		$parent['name']='菜系分类';
		$this->setData( $parent, 'parent' );//parent is always 美食

		$this->setData( $child_category, 'childs' );

		/**
		 * search base condition
		 */

		$mdl_advancedKeySearch->advancedKeyCalculation($searchKeywords);

		
		if($orderby=='default')$orderby='id Desc';
		if($orderby=='hits')$orderby='hits desc';
		if($orderby=='buy')$orderby='buy desc';
		if($orderby=='id')$orderby='id desc';
		if($orderby=='pricehigh')$orderby='voucher_deal_amount desc';
		if($orderby=='pricelow')$orderby='voucher_deal_amount asc';


		$pageSql="select * from ( 
			SELECT '$restaurant_menu' as restaurant_menu ,c.EvoucherOrrealproduct,c.id,c.categoryName,c.cityName,0 as subid,c.title,c.coupon_summery_description,c.searchKeywords,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount,c.businessName,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city
			FROM cc_coupons as c
			WHERE c.isApproved=1 and c.status=4 ". $where_restaurant." and c.categoryId like '%106126%' 
			UNION
			SELECT '$restaurant_menu' as restaurant_menu,c.EvoucherOrrealproduct,c.id,c.categoryName,c.cityName,b.id as subid,b.title as title,c.coupon_summery_description,c.searchKeywords,IF(LENGTH(b.picture)>0,b.picture,c.pic),c.createUserId,c.bonusType,c.hits,b.buy,b.customer_amount as voucher_deal_amount,b.original_amount as voucher_original_amount,c.businessName,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city 
			FROM cc_coupons_sub as b left join cc_coupons as c  on  b.parent_coupon_id =c.id
			WHERE c.isApproved=1 and c.status=4 ". $where_restaurant." and c.categoryId like '%106126%' 
			) as u ";

		$pageSql.="
			WHERE !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime))
			and categoryId like '%,".$category['id']."%'".
			" and (city like '%,".$cityId."%' or city='')";

		if($searchKeywords)
			$pageSql.=" and (title like '%$searchKeywords%' or businessName like '%$searchKeywords%' or coupon_summery_description like '%$searchKeywords%' or searchKeywords like '%$searchKeywords%') ";

		$pageSql.=" order by ".$orderby;

		if($latitude&&$longitude&&!$coupons_addon_search){
			$pageSql="
			SELECT '$restaurant_menu' as restaurant_menu,c.EvoucherOrrealproduct,c.id,c.categoryName,c.cityName,c.title,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount,c.coupon_summery_description,c.businessName,
			6378.138 * 2 * ASIN( SQRT( POW( SIN( ( $latitude * PI() / 180 - u.latitude * PI() / 180 ) / 2 ), 2 ) + COS($latitude * PI() / 180) * COS(u.latitude * PI() / 180) * POW( SIN( ( $longitude * PI() / 180 - u.longitude * PI() / 180 ) / 2 ), 2 ) ) ) *1000 AS distance 
			FROM cc_user as u right join cc_coupons as c on u.id = c.createUserId 
			WHERE c.isApproved=1 
			and c.status=4
			". $where_restaurant."
			and !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime))
			and c.categoryId like '%,".$category['id']."%' 
			ORDER BY distance asc";

			//over write city display
			$c['name']='离我最近';
			$this->setData($c,'city');
		}

		if($latitude&&$longitude&&$coupons_addon_search){
			$pageSql="select * from ( 
			   SELECT '$restaurant_menu' as restaurant_menu,c.EvoucherOrrealproduct,c.id,c.categoryName,c.cityName,0 as subid,c.title,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount,c.businessName,c.coupon_summery_description,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city,ca.* ,IFNULL(6378.138 * 2 * ASIN( SQRT( POW( SIN( ($latitude * PI() / 180 - user.latitude * PI() / 180 ) / 2 ), 2 ) + COS($latitude * PI() / 180) * COS(user.latitude * PI() / 180) * POW( SIN( ( $longitude * PI() / 180 - user.longitude * PI() / 180 ) / 2 ), 2 ) ) ) *1000,0) AS distance 
			   FROM cc_coupons as c left join (cc_coupons_addon as ca,cc_user as user) on (user.id =c.createUserId  and  c.id =ca.couponid ) 
			   WHERE c.isApproved=1 and c.status=4 ". $where_restaurant." and c.categoryId like '%106126%' 
			   UNION
			   SELECT '$restaurant_menu' as restaurant_menu,c.EvoucherOrrealproduct,c.id,c.categoryName,c.cityName,b.id as subid,b.title as title ,IF(LENGTH(b.picture)>0,b.picture,c.pic),c.createUserId,c.bonusType,c.hits,b.buy,b.customer_amount as voucher_deal_amount,b.original_amount as voucher_original_amount,c.businessName,c.coupon_summery_description,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city,ca.* ,IFNULL(6378.138 * 2 * ASIN( SQRT( POW( SIN( ($latitude * PI() / 180 - user.latitude * PI() / 180 ) / 2 ), 2 ) + COS($latitude * PI() / 180) * COS(user.latitude * PI() / 180) * POW( SIN( ( $longitude * PI() / 180 - user.longitude * PI() / 180 ) / 2 ), 2 ) ) ) *1000,0) AS distance 
			   FROM cc_coupons as c left join (cc_coupons_addon as ca,cc_coupons_sub as b,cc_user as user) on (user.id =c.createUserId and b.id =ca.couponid and c.id=b.parent_coupon_id) 
			   WHERE c.isApproved=1 and c.status=4 ". $where_restaurant." and  c.categoryId like '%106126%' and ca.sub='s'
			   ) as u";

			   $pageSql.="
				WHERE !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime))
				and categoryId like '%,".$category['id']."%'".
				" and (city like '%,".$cityId."%' or city='')";

				if($meal_type){
					$meal_type_parts=explode(',', $meal_type);
					foreach ($meal_type_parts as $key => $value) {
						$meal_type_parts[$key]= " u.meal_type like '%$value%'";
					}
					$pageSql.=" and (". join(' or ',$meal_type_parts).")";
				} 
				
				if($guest_limit){
					$guest_limit_parts=explode(',', $guest_limit);
					foreach ($guest_limit_parts as $key => $value) {
						$guest_limit_parts[$key]= "u.guest_limit like '%$value%'";
					}
					$pageSql.=" and ( ". join(' or ',$guest_limit_parts).")";
				}
				
				if($apportmant_required)
					$pageSql.= " and u.apportmant_required='$apportmant_required'";

				if($time_limit)
					$pageSql.= " and u.time_limit='$time_limit'";

				if($available_on_holiday)
					$pageSql.= " and u.available_on_holiday='$available_on_holiday'";

				if($sharable)
					$pageSql.= " and u.sharable='$sharable'";

				if($private_room)
					$pageSql.= " and u.private_room='$private_room'";

				$pageSql.=" order by distance";
		}

		if($coupons_addon_search&&(!$latitude&&!$longitude)){
			$pageSql="select * from ( 
			SELECT '$restaurant_menu' as restaurant_menu,c.EvoucherOrrealproduct,c.id,c.categoryName,c.cityName,0 as subid,c.title,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount,c.businessName,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city,ca.* 
			FROM cc_coupons as c left join cc_coupons_addon as ca on c.id =ca.couponid 
			WHERE c.isApproved=1 and c.status=4 ". $where_restaurant." and c.categoryId like '%106126%' 
			UNION
			SELECT '$restaurant_menu' as restaurant_menu,c.EvoucherOrrealproduct,c.id,c.categoryName,c.cityName,b.id as subid,b.title as title ,IF(LENGTH(b.picture)>0,b.picture,c.pic),c.createUserId,c.bonusType,c.hits,b.buy,b.customer_amount as voucher_deal_amount,b.original_amount as voucher_original_amount,c.businessName,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city,ca.* 
			FROM cc_coupons as c left join (cc_coupons_addon as ca,cc_coupons_sub as b) on (b.id =ca.couponid and c.id=b.parent_coupon_id) 
			WHERE c.isApproved=1 and c.status=4 ". $where_restaurant." and c.categoryId like '%106126%' and ca.sub='s'
			) as u ";

			$pageSql.="
			WHERE !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime))
			and categoryId like '%,".$category['id']."%'".
			" and (city like '%,".$cityId."%' or city='')";

			if($meal_type){
				$meal_type_parts=explode(',', $meal_type);
				foreach ($meal_type_parts as $key => $value) {
					$meal_type_parts[$key]= " u.meal_type like '%$value%'";
				}
				$pageSql.=" and (". join(' or ',$meal_type_parts).")";
			} 
			
			if($guest_limit){
				$guest_limit_parts=explode(',', $guest_limit);
				foreach ($guest_limit_parts as $key => $value) {
					$guest_limit_parts[$key]= "u.guest_limit like '%$value%'";
				}
				$pageSql.=" and ( ". join(' or ',$guest_limit_parts).")";
			}
			
			if($apportmant_required)
				$pageSql.= " and u.apportmant_required='$apportmant_required'";

			if($time_limit)
				$pageSql.= " and u.time_limit='$time_limit'";

			if($available_on_holiday)
				$pageSql.= " and u.available_on_holiday='$available_on_holiday'";

			if($sharable)
				$pageSql.= " and u.sharable='$sharable'";

			if($private_room)
				$pageSql.= " and u.private_room='$private_room'";

			$pageSql.=" order by ".$orderby;

		}

// var_dump($pageSql);exit;
		$pageUrl	= $this->parseUrl()->set('page')->set( 'listType','coupons');
		$pageSize	= 20;
		$maxPage	= ($this->getUserDevice()=='desktop')?10:0;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage,$wholePage);
		$data		= $mdl_coupons->getListBySql($page['outSql']);

		$mdl_customer_rating =$this->loadModel('wj_customer_rating');
		$mdl_coupons_addon=$this->loadModel('coupons_addon');
		$mdl_fav = $this->loadModel( 'fav' );

		
		$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");
		
		
		foreach ($data as $key => $value) {
			$mdl_coupons->caculatePriceAndPoint($data[$key]);
			$data[$key]['ratting_score']= $mdl_customer_rating->getAvgScore($value['createUserId']);
			$data[$key]['businessDisplayName']=$mdl_user->getBusinessDisplayName($value['createUserId']);
			if($value['subid']){
				$data[$key]['addontext']=$mdl_coupons_addon->getAddonText($value['subid'],'s');
			}else{
				$data[$key]['addontext']=$mdl_coupons_addon->getAddonText($value['id']);
			}
			$data[$key]['faved']= $mdl_fav->getCount( array( 'userId' => $this->loginUser['id'], 'productId' => $value['id'], 'type' => 'coupon' ) );
			
			// 如果产品类型为代金券或者团购券 ,那么使用快速链接
			if ($value['bonusType']==7 or $value['bonusType']==18) {
				
				$data[$key]['a_link'] = 'coupon7m/'.$value['id'].'?id='.$value['id'].'&reftag='.$reftag;
			}else { //否则使用标准链接
				$data[$key]['a_link'] = 'coupon1/'.$value['id'].'?reftag='.$reftag;
				
			}
			if($data[$key]['EvoucherOrrealproduct']=='restaurant_menu' ) {
				
				$manjian=$mdl_restaurant_promotion_manjian->getbyWhere(array('restaurant_id'=>$data[$key]['createUserId']));
			
				$data[$key]['businessDisplayName'] =$data[$key]['title'];
				//使用直接到餐馆的链接
				$data[$key]['a_link'] = 'restaurant2/'.$value['createUserId'].'?id='.$value['createUserId'].'&reftag='.$reftag;
			
				// 如果是线上餐厅 ,加入显示线上餐厅deal的信息
				if ($manjian){
					if($manjian['discount']>0) {
						$data[$key]['title'] ='全店满减<strong>'.$manjian['discount'].'%Off</strong>';
					}
					if($manjian['promotion_desc']) {
						$data[$key]['title'] =$manjian['promotion_desc'];
					}
				}
			}
		}
		$this->setData( $page, 'pager' );

		$this->setData( $data, 'data' );

		if($this->getUserDevice()=='desktop'){
			$result_section = $this->fetch('food_result_tpl_pc');
		}else{
			$result_section = $this->fetch('food_result_tpl');
		}

		if(get2('ajax')){
			echo $result_section;
			exit;
		}
/*
if($alias =='106126' && $restaurant_menu !='restaurant_menu'){
		$recommend=$mdl_coupons->getListBySql("SELECT '$restaurant_menu' as restaurant_menu,c.coupon_summery_description,u.logo,e.isStore,c.createUserId,c.id,c.categoryName,c.cityName, c.title, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.bonusType,c.categoryId,e.panaltype,c.city FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId WHERE c.isapproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) AND   e.panaltype=20  ORDER  BY sort limit 6");

        foreach ($recommend as $key => $value) {
          $mdl_coupons->caculatePriceAndPoint($recommend[$key]);
          $recommend[$key]['ratting_score']= $mdl_customer_rating->getAvgScore($value['createUserId']);
		  $recommend[$key]['businessDisplayName']=$mdl_user->getBusinessDisplayName($value['createUserId']);
		  
        }

        $this->setData($recommend,'recommend');
}
*/

/*
		//千人千面
            $mdl_user=$this->loadModel('user');

            $bid = trim(get2('bid'));
            $buser = $mdl_user->get($bid);
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

          */  

		$this->setData( $result_section, 'result_section' );//var_dump($data);exit();

		$this->setData($this->loadModel('recharge')->getBalanceOfUser($this->loginUser['id'] ),'userMoneyBalance');

		$this->setData( $mdl_user->getBusinessDisplayName($businessUser['id']).'-Ubonus美食生活', 'pageTitle' );


		$this->setData( $this->parseUrl()->set( 'page' )->set( 'listType' ), 'listTypeUrl' );
		
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'couponType' ), 'couponTypeUrl' );
		
		$currentUrl =$this->parseUrl()->set( 'page' )->set( 'ajax' );
		

		$this->setData( $this->parseUrl()->set( 'page' )->set( 'ajax' ), 'currentUrl' );

		
		$searchUrl = $this->parseUrl()->set( 'page' )->set( 'key' )->set('meal_type')->set('guest_limit')->set('apportmant_required')->set('time_limit')->set('available_on_holiday')->set('sharable')->set('private_room');
        if(strpos($searchUrl,'https://')) {
		
		}else if (strpos($searchUrl,'http://')){
			str_replace('http://','https://',$searchUrl);
			
		}
		$this->setData( $searchUrl,'searchUrl' );
		
		
		$this->setData( $this->parseUrl()->set( 'page' )->set('meal_type')->set('guest_limit')->set('apportmant_required')->set('time_limit')->set('available_on_holiday')->set('sharable')->set('private_room')->set( 'key' ), 'filterUrl' );

    	$this->setData($this->loadModel('info')->getListByClass('115',10,'ordinal'),'bannerData');
/*
    	 //wx share
        require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');
    	*/
        $this->display_pc_mobile('food/food','food');
    	
	}
	
	
	 function restaurant_action() {
		//var_dump('ddd'); exit;
		$this->display('food');
		
		
	}
}
 ?>
