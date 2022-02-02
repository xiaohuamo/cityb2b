<?php 
class ctl_restaurant extends cmsPage
{
	function ctl_restaurant()
	{
		parent::cmsPage();
		$mdl_restaurant =$this->loadModel('restaurant_info');
		$restaurant =$mdl_restaurant->getByWhere(array('userId'=>$this->loginUser['id']));
		$this->setData($restaurant,'restaurant');
		
		 $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
		
	
		
	}	
	
	public function index_action($value='')
	{
		
		
		// 获得是否有推荐人,如果有推荐人,生成的查询链接都会加相关的tag
		
		$reftag = trim( get2( 'reftag' ) );
		$this->setData( $reftag, 'reftag' );
		
		/**
		 * 产品分类
		 * @var [type]
		 */
		$alias = trim( get2( 'alias' ) );
		if (!$alias)$alias='106121102';
		$this->setData( $alias,'alias');

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

		$parent = $mdl_infoclass->getByAlias( 106121102);
		$parent['name']='菜系分类';
		$this->setData( $parent, 'parent' );//parent is always 美食

		$this->setData( $child_category, 'childs' );

		/**
		 * search base condition
		 */

		$mdl_advancedKeySearch->advancedKeyCalculation($searchKeywords);

		
		if($orderby=='default')$orderby='id desc';
		if($orderby=='hits')$orderby='hits desc';
		if($orderby=='buy')$orderby='buy desc';
		if($orderby=='id')$orderby='id desc';
		if($orderby=='pricehigh')$orderby='voucher_deal_amount desc';
		if($orderby=='pricelow')$orderby='voucher_deal_amount asc';


		$pageSql="select * from ( 
		SELECT c.id,0 as subid,c.title,c.coupon_summery_description,c.searchKeywords,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount,c.businessName,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city
		FROM cc_coupons as c
		WHERE c.isApproved=1 and c.status=4 and c.EvoucherOrrealproduct <> 'restaurant_menu' and c.categoryId like '%106121102%' 
		UNION
		SELECT c.id,b.id as subid,b.title as title,c.coupon_summery_description,c.searchKeywords,IF(LENGTH(b.picture)>0,b.picture,c.pic),c.createUserId,c.bonusType,c.hits,b.buy,b.customer_amount as voucher_deal_amount,b.original_amount as voucher_original_amount,c.businessName,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city 
		FROM cc_coupons_sub as b left join cc_coupons as c  on  b.parent_coupon_id =c.id
		WHERE c.isApproved=1 and c.status=4 and c.EvoucherOrrealproduct <> 'restaurant_menu' and c.categoryId like '%106121102%' ) as u ";

		$pageSql.="
		WHERE !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime))
		and categoryId like '%,".$category['id']."%'".
		"and (city like '%,".$cityId."%' or city='')";

		if($searchKeywords)
			$pageSql.=" and (title like '%$searchKeywords%' or businessName like '%$searchKeywords%' or coupon_summery_description like '%$searchKeywords%' or searchKeywords like '%$searchKeywords%') ";

		$pageSql.=" order by ".$orderby;

		if($latitude&&$longitude&&!$coupons_addon_search){
			$pageSql="
			SELECT c.id,c.title,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount,c.coupon_summery_description,c.businessName,
			6378.138 * 2 * ASIN( SQRT( POW( SIN( ( $latitude * PI() / 180 - u.latitude * PI() / 180 ) / 2 ), 2 ) + COS($latitude * PI() / 180) * COS(u.latitude * PI() / 180) * POW( SIN( ( $longitude * PI() / 180 - u.longitude * PI() / 180 ) / 2 ), 2 ) ) ) *1000 AS distance 
			FROM cc_user as u right join cc_coupons as c on u.id = c.createUserId 
			WHERE c.isApproved=1 
			and c.status=4
			and c.EvoucherOrrealproduct <> 'restaurant_menu'
			and !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime))
			and c.categoryId like '%,".$category['id']."%' 
			ORDER BY distance asc";

			//over write city display
			$c['name']='离我最近';
			$this->setData($c,'city');
		}

		if($latitude&&$longitude&&$coupons_addon_search){
			$pageSql="select * from ( 
			SELECT c.id,0 as subid,c.title,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount,c.businessName,c.coupon_summery_description,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city,ca.* ,IFNULL(6378.138 * 2 * ASIN( SQRT( POW( SIN( ($latitude * PI() / 180 - user.latitude * PI() / 180 ) / 2 ), 2 ) + COS($latitude * PI() / 180) * COS(user.latitude * PI() / 180) * POW( SIN( ( $longitude * PI() / 180 - user.longitude * PI() / 180 ) / 2 ), 2 ) ) ) *1000,0) AS distance 
			FROM cc_coupons as c left join (cc_coupons_addon as ca,cc_user as user) on (user.id =c.createUserId  and  c.id =ca.couponid ) 
			WHERE c.isApproved=1 and c.status=4 and c.EvoucherOrrealproduct <> 'restaurant_menu' and c.categoryId like '%106121102%' 
			UNION
			SELECT c.id,b.id as subid,b.title as title ,IF(LENGTH(b.picture)>0,b.picture,c.pic),c.createUserId,c.bonusType,c.hits,b.buy,b.customer_amount as voucher_deal_amount,b.original_amount as voucher_original_amount,c.businessName,c.coupon_summery_description,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city,ca.* ,IFNULL(6378.138 * 2 * ASIN( SQRT( POW( SIN( ($latitude * PI() / 180 - user.latitude * PI() / 180 ) / 2 ), 2 ) + COS($latitude * PI() / 180) * COS(user.latitude * PI() / 180) * POW( SIN( ( $longitude * PI() / 180 - user.longitude * PI() / 180 ) / 2 ), 2 ) ) ) *1000,0) AS distance 
			FROM cc_coupons as c left join (cc_coupons_addon as ca,cc_coupons_sub as b,cc_user as user) on (user.id =c.createUserId and b.id =ca.couponid and c.id=b.parent_coupon_id) 
			WHERE c.isApproved=1 and c.status=4 and c.EvoucherOrrealproduct <> 'restaurant_menu' and c.categoryId like '%106121102%' and ca.sub='s') as u";

			$pageSql.="
			WHERE !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime))
			and categoryId like '%,".$category['id']."%'".
			"and (city like '%,".$cityId."%' or city='')";

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
			SELECT c.id,0 as subid,c.title,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount,c.businessName,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city,ca.* 
			FROM cc_coupons as c left join cc_coupons_addon as ca on c.id =ca.couponid 
			WHERE c.isApproved=1 and c.status=4 and c.EvoucherOrrealproduct <> 'restaurant_menu' and c.categoryId like '%106121102%' 
			UNION
			SELECT c.id,b.id as subid,b.title as title ,IF(LENGTH(b.picture)>0,b.picture,c.pic),c.createUserId,c.bonusType,c.hits,b.buy,b.customer_amount as voucher_deal_amount,b.original_amount as voucher_original_amount,c.businessName,c.startTime,c.endTime,c.autoOffline,c.categoryId,c.city,ca.* 
			FROM cc_coupons as c left join (cc_coupons_addon as ca,cc_coupons_sub as b) on (b.id =ca.couponid and c.id=b.parent_coupon_id) 
			WHERE c.isApproved=1 and c.status=4 and c.EvoucherOrrealproduct <> 'restaurant_menu' and c.categoryId like '%106121102%' and ca.sub='s') as u ";

			$pageSql.="
			WHERE !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime))
			and categoryId like '%,".$category['id']."%'".
			"and (city like '%,".$cityId."%' or city='')";

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


		$pageUrl	= $this->parseUrl()->set('page')->set( 'listType','coupons');
		$pageSize	= 20;
		$maxPage	= ($this->getUserDevice()=='desktop')?10:0;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage,$wholePage);
		$data		= $mdl_coupons->getListBySql($page['outSql']);

		$mdl_customer_rating =$this->loadModel('wj_customer_rating');
		$mdl_coupons_addon=$this->loadModel('coupons_addon');
		$mdl_fav = $this->loadModel( 'fav' );

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

		}

		$this->setData( $page, 'pager' );

		$this->setData( $data, 'data' );

		$result_section = $this->fetch('food_result_tpl');

		if(get2('ajax')){
			echo $result_section;
			exit;
		}


		$recommend=$mdl_coupons->getListBySql("SELECT c.coupon_summery_description,u.logo,e.isStore,c.createUserId,c.id, c.title, c.pic, c.hits, c.voucher_deal_amount,c.voucher_original_amount,c.bonusType,c.categoryId,e.panaltype,c.city FROM cc_explosion e LEFT JOIN cc_coupons c ON c.id=e.couponid LEFT JOIN cc_user u ON u.id=c.createUserId WHERE c.isapproved=1 and c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) AND   e.panaltype=20  ORDER  BY sort limit 6");

		foreach ($recommend as $key => $value) {
			$mdl_coupons->caculatePriceAndPoint($recommend[$key]);
			$recommend[$key]['ratting_score']= $mdl_customer_rating->getAvgScore($value['createUserId']);
			$recommend[$key]['businessDisplayName']=$mdl_user->getBusinessDisplayName($value['createUserId']);
		}

		$this->setData($recommend,'recommend');

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

		$this->setData( $result_section, 'result_section' );//var_dump($data);exit();

		$this->setData($this->loadModel('recharge')->getBalanceOfUser($this->loginUser['id'] ),'userMoneyBalance');

		$this->setData( $mdl_user->getBusinessDisplayName($businessUser['id']).'-Ubonus美食生活', 'pageTitle' );


		$this->setData( $this->parseUrl()->set( 'page' )->set( 'listType' ), 'listTypeUrl' );

		$this->setData( $this->parseUrl()->set( 'page' )->set( 'couponType' ), 'couponTypeUrl' );

		$this->setData( $this->parseUrl()->set( 'page' )->set( 'ajax' ), 'currentUrl' );

		$this->setData( $this->parseUrl()->set( 'page' )->set( 'key' )->set('meal_type')->set('guest_limit')->set('apportmant_required')->set('time_limit')->set('available_on_holiday')->set('sharable')->set('private_room'), 'searchUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set('meal_type')->set('guest_limit')->set('apportmant_required')->set('time_limit')->set('available_on_holiday')->set('sharable')->set('private_room')->set( 'key' ), 'filterUrl' );

		$this->setData($this->loadModel('info')->getListByClass('115',10,'ordinal'),'bannerData');

    	 //wx share
		require_once "wx/wxjssdk.php";
		$jssdk = new WXjsSDK();
		$signPackage = $jssdk->GetSignPackage();
		$this->setData($signPackage,'signPackage');


		$this->display('food');
	}


	/**
	 * 开启线上餐厅
	 */
	public function setup_restaurant_action()
	{
		if(is_post()){
			// 生成线上餐厅记录数据

			$title=trim(post('title'));

			if($title){
				$data['name']=$title;
			}else{
				$business_name = $this->loadModel('user')->getBusinessDisplayName($this->loginUser['id']);
				$data['name']=$business_name.'线上餐厅';
			}

			$data['is_approved']=1;
			$data['status']=4;
			$data['userID']=$this->loginUser['id'];

			$mdl_restaurant_info =$this->loadModel('restaurant_info');
			$restaurant_info = $mdl_restaurant_info->getByWhere(array('userID'=>$this->loginUser['id']));

			if($restaurant_info) {
				$mdl_restaurant_info->update($data,$restaurant_info['id']);

			}else{
				$mdl_restaurant_info->insert($data);
			}

			// 更新餐厅优惠数据库数据

			$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");

			$restaurant_promotion_manjian = $mdl_restaurant_promotion_manjian->getByWhere(array('restaurant_id'=>$this->loginUser['id']));

			if( $restaurant_promotion_manjian) {

				$this->form_response(200,'线上餐厅成功开启',HTTP_ROOT_WWW.'company/coupons_edit?coupon_type=7&restaurant=1');

			}else{
				$data_manjian['restaurant_id'] =$this->loginUser['id'];
				$data_manjian['createUserId'] =$this->loginUser['id'];
				if($mdl_restaurant_promotion_manjian->insert($data_manjian))  {
					$this->form_response(200,'线上餐厅成功开启',HTTP_ROOT_WWW.'company/coupons_edit?coupon_type=7&restaurant=1');
				}else{
					$this->form_response_msg('线上餐厅开启失败，请稍候再试');
				}
			}

		}else{
			$this->setData('启动线上餐厅 - ' . $this->site['pageTitle'], 'pageTitle');
			$this->display('restaurant/setup_restaurant');
		}
	}

	/**
	 * 餐厅显示页面
	 */
	function restaurant_action() {
		$id = (int)get2( 'id' );
		
		$mdl = $this->loadModel('user');
		$curr_user =$mdl->get($id);
		
		if ($curr_user['shop_version']==1 || get2( 'dy' )){
			return self::fresh_action();
		}

		
			
	
		$cart = (int)get2( 'cart' );

		if($id==217005) {
			$id =219842;
		}
		if($id==218639) {
			$id =218812;
		}
 
		$force = (int)get2( 'force' ); //后台刷新的时候，强制调用get_menu
		$this->setData( $force, 'force' );


		//插入一段获取某用户购买历史的程序
		if($this->loginUser) {
			$where =array(
				'createUserId' => $id,
				'EvoucherOrrealproduct' =>'restaurant_menu'
			);

			$mdl_coupons =$this->loadModel("coupons");
			$restaurant_coupon= $mdl_coupons->getByWhere($where);

			$this->setData( $restaurant_coupon['id'], 'restaurant_couponID' );
			$deliveryTime = $this->cookie->getCookie('DispCenterUserSelectedDeliveryDate');
			$menu_bought_list = $this->loadModel("restaurant_menu")->getUserBoughtMenu($this->loginUser['id'],$id, $deliveryTime, $this->lang['lang'][0]);
			$this->setData($menu_bought_list,'menu_bought_list');
		}



		/**
		 * 加载模组
		 */
		$mdl_user=$this->loadModel('user');

		if(!$id)$this->sheader(null,'没有商家被选择');

		// 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
		$where =array(
			'createUserId' => $id,
			'EvoucherOrrealproduct' =>'restaurant_menu'
		);

		$mdl_coupons =$this->loadModel("coupons");

		$restaurant_coupon= $mdl_coupons->getByWhere($where);

		if(($restaurant_coupon['isApproved']==1 && $restaurant_coupon['status']==4) || $restaurant_coupon['createUserId']==$this->loginUser['id']  || $_SESSION['coupon_private_view_allowed']==$restaurant_coupon['id']) {
			$mdl_user =$this->loadModel("user");
			$business_user =$mdl_user->get($restaurant_coupon['createUserId']);
			$restaurant_coupon['business']=$business_user;

			$this->setData( $restaurant_coupon, 'coupon' );
			//获得自己店的其它产品
		}else{
			//检查如果该登陆用户为店铺的授权用户则可以进入；
			// 此处仅作： owner of sale channel

			$sql00 = "select count(*) as count from cc_factory2c_list where customer_id=$id and factroy_id =".$this->loginUser['id'];
			$factory_rec = $this->loadModel('factory2c_list')->getListBySql($sql00);
			if($factory_rec [0]['count']) {
				$mdl_user =$this->loadModel("user");
				$business_user =$mdl_user->get($restaurant_coupon['createUserId']);
				$restaurant_coupon['business']=$business_user;

				$this->setData( $restaurant_coupon, 'coupon' );
			}else{
				$this->sheader(HTTP_ROOT_WWW.'coupon1/coupon_private_view_gate?id='.$restaurant_coupon['id']);
				$this->sheader(null,'当前商家还未开启,请稍后..');
			}


		}

		$refresh_code =$business_user['business_store_refresh_code'];
		$this->setdata($refresh_code,'refresh_code');

		$where1=array(
			'restaurant_id' => $id,
		);
		$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");

		$restaurant_promotion_manjian=$mdl_restaurant_promotion_manjian->getByWhere($where1);
		//var_dump($id);exit;
		if($restaurant_promotion_manjian) {
			$restaurant_promotion_manjian_rates =$restaurant_promotion_manjian[discount]/100;
		}else{
			$restaurant_promotion_manjian_rates=0;
		}

		if($this->getLangStr()=='en') {
			if($restaurant_promotion_manjian['promotion_desc_en']){

				$restaurant_promotion_manjian['promotion_desc']=$restaurant_promotion_manjian['promotion_desc_en'];
			}

		}
		$this->setData($restaurant_promotion_manjian,'restaurant_promotion_manjian');

		$this->setData( $restaurant_promotion_manjian_rates*100, 'restaurant_promotion_manjian_rates' );
		$this->setData( $id, 'restaurant_id' );
		$this->setData( $cart, 'cart' );
		$this->setData( $menu_totalprice, 'totalprice' );
		$this->setData( $voucher_totalprice, 'voucher_totalprice' );
		
  	 // var_dump($restaurant_coupon);exit;
		$title = str_replace('|', '', $restaurant_coupon['title']);
		$this->setData($title, 'pageTitle');
		$this->setData($restaurant_coupon['searchKeywords'], 'pageKeywords');
		$this->setData($restaurant_promotion_manjian['promotion_desc'], 'pageDescription');


	   /*
		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        $shareUrl = HTTP_ROOT_WX."restaurant/$id?reftag=".$this->loginUser['id'];
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');

        $this->setData( get2('action'), 'returnAction' );

        $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');
       */
		//$this->display_pc_mobile('/mobile/restaurant/restaurant-site/'.$id,'/mobile/restaurant/restaurant-site/'.$id); return;

		 $this->loadModel('freshfood_disp_suppliers_schedule');

		// 获取 统配店商家号  // 获取供应商商家列表
			$centreId = DispCenter::getDispCenterIdOfSupplier($id);
			$data_suppliers = DispCenter::getSupplierListWithRefreshCode($centreId);
			$data_suppliers_count =count($data_suppliers);
			$this->setData($data_suppliers, 'data_suppliers');
			$this->setData($data_suppliers_count, 'data_suppliers_count');







		$businessDispSchedule = DispCenter::getBusinessDispSchedule($id);
		//var_dump($businessDispSchedule);exit;
		$businessDispScheduleFilledWithContinueDates = DispCenter::getFollowingNDaysIncludeAvailableDeliver($businessDispSchedule);
		//var_dump($businessDispScheduleFilledWithContinueDates);exit;
		$this->setData($businessDispScheduleFilledWithContinueDates, 'businessDispSchedule');
		$this->setData(in_array($id, DispCenter::getSupplierList()), 'isDispCenterBusiness');

		$this->setData(join(DispCenter::getPostcode(DispCenter::getDispCenterIdOfSupplier($id)),','), 'postcodeSupported'); //使用统配商家邮编信息
		$this->setData(DispCenter::isSingleSupplierDispCenter($id),'isSingleSupplierDispCenter');
		
		
		
		
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        $shareUrl = HTTP_ROOT_WX."restaurant/$id?reftag=".$this->loginUser['id'];
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');

        $this->setData( get2('action'), 'returnAction' );

        $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');
		
      
      
		
	  
       
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');
        
       
      //  $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');
		
		 //获得当前店铺得 订货时间
		 $mdl = $this->loadModel('freshfood_disp_suppliers_schedule');
				$where = [];
				$where['business_id'] = $id;
				$where['centre_business_id'] = DispCenter::getDispCenterIdOfSupplier($id);
				$list = $mdl->getList(null, $where);
				
				
			
			foreach ($list as $key =>$value) {
				$order_start_of_date_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['order_start_of_date']);
				$order_cut_of_date_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['order_cut_of_date']);
				$delivery_date_of_week_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['delivery_date_of_week']);
				
				$list[$key]['order_start_of_date_cn'] = $order_start_of_date_cn ;
				$list[$key]['order_cut_of_date'] =$order_cut_of_date_cn  ;
				$list[$key]['delivery_date_of_week_cn'] = $delivery_date_of_week_cn ;
				$list[$key]['ordertime_cn'] = '从'.$order_start_of_date_cn.$value['order_start_of_time'] .' 到 '.$order_cut_of_date_cn.$value['order_cut_of_time'];
				$list[$key]['ordertime'] = 'From '.$value['order_start_of_date'].' '.$value['order_start_of_time'] .' To '.$value['order_cut_of_date'].' '.$value['order_cut_of_time'];
		       
			}
				
				
				
				
            $this->setData($list,'current_business_tuangou_time');
		
		
		
		
        $this->display_pc_mobile('mobile/restaurant/coupon_detail_coupon','mobile/restaurant/coupon_detail_coupon');
    }

    public  function init_page_action() {


    	$mdl_user=$this->loadModel('user');

    	$id = (int)get2( 'businessUserId' );

    	if(!$id)$this->sheader(null,(string)$this->lang->choose_rihgt_business);

		// 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
    	$where =array(
    		'createUserId' => $id,
    		'EvoucherOrrealproduct' =>'restaurant_menu'
    	);

    	$mdl_coupons =$this->loadModel("coupons");

    	$restaurant_coupon= $mdl_coupons->getByWhere($where);
    	$currentTime=strtotime ('now');
    	if(($restaurant_coupon['isApproved']==1 && $restaurant_coupon['status']==4) || $restaurant_coupon['createUserId']==$this->loginUser['id']  || $_SESSION['coupon_private_view_allowed']==$restaurant_coupon['id']) {
    		$mdl_user =$this->loadModel("user");
    		$business_user =$mdl_user->get($restaurant_coupon['createUserId']);
    		$restaurant_coupon['business']=$business_user;
			//获得自己店的其它产品
    	}/*else if($currentTime < $restaurant_coupon['startTime']){
			$this->sheader(null,'当前产品还未开放，暂时不接受订单,请稍后..');

		}else if($currentTime > $restaurant_coupon['endTime']) {
		$this->sheader(null,'当前产品已过开放时间，暂时不接受订单！');}

		*/


		$refresh_code =$business_user['business_store_refresh_code'];
		$this->setdata($refresh_code,'refresh_code');


		$this->setData( $restaurant_coupon, 'coupon' );

		$where1=array(
			'restaurant_id' => $id,
		);
		$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");

		$restaurant_promotion_manjian=$mdl_restaurant_promotion_manjian->getByWhere($where1);
		if($restaurant_promotion_manjian) {
			$restaurant_promotion_manjian_rates =$restaurant_promotion_manjian[discount]/100;
		}else{
			$restaurant_promotion_manjian_rates=0;
		}
		$this->setData($restaurant_promotion_manjian,'restaurant_promotion_manjian');

		if($this->loginUser) {

	     	//清除购物车其它产品
			$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');
			$where =array (
				'businessUserId <>' . $id,
				'userId '=> $this->loginUser['id']
			);
    		//$mdl_wj_user_temp_carts->deleteByWhere($where);


			$userId=$this->loginUser['id'];


			// 获取该商家关联的配送中心所有商家列表，并将用户在这些相关的商家列表中的购买清单全部列出来

			$suppliers_list = $this->get_same_dispatching_centre_suppliers_list($id);


			$sql ="SELECT b.restaurant_category_id,category_sort_id,category_cn_name,category_en_name,a.*,b.menu_pic as pic,d.pic as coupon_pic ,e.displayName,e.businessName FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id   left join cc_user e on e.id=a.businessUserId where a.userId=" .$userId."   and businessUserId in $suppliers_list order by businessUserId,category_sort_id,b.menu_id";
			$cartItems=$mdl_wj_user_temp_carts->getListBySql($sql);

			// 对临时购物车的记录进行校验 ，比如当前购物车的产品已经下线，或者当前购物车的产品价格已经更新，或者当前购物车里的产品库存超过库房库存 。
			$mdl_restaurant_menu =$this->loadModel('restaurant_menu');
			//var_dump( $sql);
			foreach ($cartItems as $key => $val) {
				$menu_rec =$mdl_restaurant_menu->get($val['menu_id']);
				//console.log($menu_rec);
				if(!$menu_rec){
					$mdl_wj_user_temp_carts->delete($val['id']); // 如果菜单中没有这个，那么从临时购物车中删除。
					continue;
				}else{

					// 如果找到菜单中的项目，检查是否已经下线，如果下线，则删除

					if (!$menu_rec['visible']) {
						//var_dump($menu_rec['visible'].$menu_rec['id']);
						$mdl_wj_user_temp_carts->delete($val['id']);

					}

                   ///如果是特价产品
				   if($val['onSpecial']) {

					   if($menu_rec['speical_price'] != $val['single_amount']) {


						$new_item_price_data= array(
							'single_amount' =>$menu_rec['speical_price']
						);
						$mdl_wj_user_temp_carts->update($new_item_price_data,$val['id']);

					}




				   }else{

					  	// 如果在菜单中找到该产品，检查价格是否和菜单中描述一致，不一致，则更新
					if($menu_rec['price'] != $val['single_amount']) {




						$new_item_price_data= array(
							'single_amount' =>$menu_rec['price']
						);
						$mdl_wj_user_temp_carts->update($new_item_price_data,$val['id']);

					}



				   }



					// 如果在菜单中找到该产品，检查库存是否溢出，如果溢出，则把库存调整到当前最大值

					if($menu_rec['qty'] < $val['quantity']) {
						$new_item_quantity_data= array(
							'quantity' =>$menu_rec['qty']
						);
						$mdl_wj_user_temp_carts->update($new_item_quantity_data,$val['id']);


					}

				}

			}



			/* 获得该用户订单数量,总额 ,同时 生成点击订单按钮的显示数据  */



			$cartItems=$mdl_wj_user_temp_carts->getListBySql($sql);


			$cartTotalPrice = 0;


			$old_category='0';
			$totalQuantity=0;
			$old_business_id=0;
			foreach ($cartItems as $key => $val) {
				$totalQuantity+=$val['quantity'];
				if ($val['restaurant_category_id'] !== $old_category) {
						if(!$val['restaurant_category_id']) { // 没有分类 就是团购套餐

							$cartItems[$key]['category_cn_name']=(string)$this->lang->group_buy;

						}

						$cartItems[$key]['new_cat']=1;
						if($old_business_id==0 || ($cartItems[$key]['businessUserId'] <> $old_business_id) ) {
							if($cartItems[$key]['displayName']){
								$cartItems[$key]['business_name'] =$cartItems[$key]['displayName'];
							}else{
								$cartItems[$key]['business_name'] =$cartItems[$key]['businessName'];
							}
							$old_business_id =$cartItems[$key]['businessUserId'];
						}
					}else{
						$cartItems[$key]['new_cat']=0;

					}
					$old_category=$val['restaurant_category_id'];
					if(!$val['restaurant_category_id']) {
						$cartItems[$key]['pic']=$val['coupon_pic'];
					}

					if ($val['main_coupon_id']== $restaurant_coupon['id']){

						$menu_totalprice += $val['single_amount']*$val['quantity'];
					}else{

						$voucher_totalprice += $val['single_amount']*$val['quantity'];

					}


					$cartTotalPrice+=$val['single_amount']*$val['quantity'];
				}
				//var_dump($cartTotalPrice);exit;

				$this->setData($cartTotalPrice,'totalPrice');
				$this->setData($totalQuantity,'totalQuantity');

				$this->setData($cartItems,'items');
				$this->setData($businessUserId,'businessUserId');



				$us=$this->getUserDevice();
				if($us=='desktop'){
					$html = $this->fetch('/ajax_cart');
				}else{
					$html = $this->fetch('ajax_cart');
				}
			}



			$this->setData( $restaurant_promotion_manjian_rates*100, 'restaurant_promotion_manjian_rates' );
			$this->setData( $id, 'restaurant_id' );
			$this->setData( $menu_totalprice, 'totalprice' );
			$this->setData( $voucher_totalprice, 'voucher_totalprice' );
			$title = str_replace('|' ,'',$restaurant_coupon['title']);
			$this->setData(  $title. ' | '.'cityb2b' , 'pageTitle' );
			$this->setData($title. ' | '.'cityb2b', 'pageKeywords' );
			$this->setData( $title. ' | '.'cityb2b', 'pageDescription' );

		//wx share
			require_once "wx/wxjssdk.php";
			$jssdk = new WXjsSDK();
			$signPackage = $jssdk->GetSignPackage();
			$this->setData($signPackage,'signPackage');

			$shareUrl = HTTP_ROOT_WX."restaurant/$id?reftag=".$this->loginUser['id'];
			$this->setData($shareUrl,'shareUrl');
			$this->setData(generateQRCode($shareUrl),'shareQRCode');

			$this->setData( get2('action'), 'returnAction' );

			$this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');




	   // init page 的内容

			$data=array();


			$this->setData( $coupon_list, 'coupon_list' );


			$this->setData( $restaurant_category, 'restaurant_category' );

			$us=$this->getUserDevice();
			if($us=='desktop'){
				$category_list = $this->fetch('/mobile/restaurant/category');

			}else{
				$category_list = $this->fetch('/mobile/restaurant/category');

			}



			$data['coupon'] =$restaurant_coupon;
			$data['html'] =$html;
			$data['totalPrice'] =$cartTotalPrice;
			$data['totalQuantity'] =$totalQuantity;



			echo json_encode($data); return;




		}



		public function get_category_coupon_list_action(){


		/**
		 * 加载模组
		 */

		$mdl_user=$this->loadModel('user');

		$id = (int)get2( 'businessUserId' );



		if(!$id)$this->sheader(null,(string)$this->lang->choose_rihgt_business);

		// 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
		$where =array(
			'createUserId' => $id,
			'EvoucherOrrealproduct' =>'restaurant_menu'
		);

		$mdl_coupons =$this->loadModel("coupons");

		$restaurant_coupon= $mdl_coupons->getByWhere($where);

		if(($restaurant_coupon['isApproved']==1 && $restaurant_coupon['status']==4) || $restaurant_coupon['createUserId']==$this->loginUser['id']  || $_SESSION['coupon_private_view_allowed']==$restaurant_coupon['id']) {
			$mdl_user =$this->loadModel("user");
			$business_user =$mdl_user->get($restaurant_coupon['createUserId']);
			$restaurant_coupon['business']=$business_user;

			$this->setData( $restaurant_coupon, 'coupon' );

			//获得自己店的其它产品
			$refresh_code =$business_user['business_store_refresh_code'];



		}





		$where1=array(
			'restaurant_id' => $id,
		);
		$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");

		$restaurant_promotion_manjian=$mdl_restaurant_promotion_manjian->getByWhere($where1);
		//var_dump($id);exit;
		if($restaurant_promotion_manjian) {
			$restaurant_promotion_manjian_rates =$restaurant_promotion_manjian[discount]/100;
		}else{
			$restaurant_promotion_manjian_rates=0;
		}
		$this->setData($restaurant_promotion_manjian,'restaurant_promotion_manjian');

		if($this->loginUser) {
	     	//清除购物车其它产品
			$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');
			$where =array (
				'businessUserId <>' . $id,
				'userId '=> $this->loginUser['id']
			);
			//$mdl_wj_user_temp_carts->deleteByWhere($where);


			/* 获得该用户订单数量,总额 ,同时 生成点击订单按钮的显示数据  */

			$userId=$this->loginUser['id'];

			$sql ="SELECT category_sort_id,category_cn_name,category_en_name,a.*,b.menu_pic as pic ,d.pic as coupon_pic FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id  where a.userId=" .$userId."   and businessUserId =".$id . " order by category_sort_id,b.menu_id";


			$cartItems=$mdl_wj_user_temp_carts->getListBySql($sql);

			$cartTotalPrice = 0;


			$old_category='0';
			$totalQuantity=0;
			foreach ($cartItems as $key => $val) {
				$totalQuantity+=$val['quantity'];
				if ($val['category_sort_id'] !== $old_category) {
						if(!$val['category_sort_id']) { // 没有分类 就是团购套餐

							$cartItems[$key]['category_cn_name']='团购';
						}
						$cartItems[$key]['new_cat']=1;
					}else{
						$cartItems[$key]['new_cat']=0;

					}
					$old_category=$val['category_sort_id'];
					if(!$val['category_sort_id']) {
						$cartItems[$key]['pic']=$val['coupon_pic'];
					}

					if ($val['main_coupon_id']== $restaurant_coupon['id']){

						$menu_totalprice += $val['single_amount']*$val['quantity'];
					}else{

						$voucher_totalprice += $val['single_amount']*$val['quantity'];

					}


					$cartTotalPrice+=$val['single_amount']*$val['quantity'];
				}

				$this->setData($cartTotalPrice,'totalPrice');
				$this->setData($totalQuantity,'totalQuantity');

				$this->setData($cartItems,'items');
				$this->setData($businessUserId,'businessUserId');



				$us=$this->getUserDevice();
				if($us=='desktop'){
					$html = $this->fetch('/restaurant/ajax_cart');
				}else{
					$html = $this->fetch('ajax_cart');
				}

				$this->setData(json_encode($html),'html');

			}

		//加载餐馆菜单
			$mdl_restaurant_category=$this->loadModel('restaurant_category');
			$restaurant_category=$mdl_restaurant_category->getListBySql("select * from cc_restaurant_category where restaurant_id = ".$id . " and (parent_category_id  =0 or parent_category_id is null ) and (length(category_cn_name)>0 or length(category_en_name)>0) order by `category_sort_id`");

			foreach ($restaurant_category as $key => $value) {
				if($restaurant_category[$key]['category_en_name']=='') {
					$restaurant_category[$key]['category_en_name']=$restaurant_category[$key]['category_cn_name'];
				}
			}


        // 如果发现有special菜单，那么生成一个新的类别编号，并置顶
		$mdl_restaurant_menu = $this->loadModel( 'restaurant_menu' );
		$sql_special  = "select count(*) as count from cc_restaurant_menu a where a.restaurant_id =$id and  (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and a.visible=1 and a.onSpecial =1  ";
		//var_dump($sql_special);exit;
		$exist_special =$mdl_restaurant_menu->getListBySql($sql_special);
		if($exist_special[0]['count']>0) {

			///var_dump($exist_special);exit;

			$special_array = array(
			   'restaurant_id'=>$id,
			   'category_id'=>1000,
			   'category_sort_id'=>10,
			   'category_cn_name'=>'本期优惠',
			   'category_en_name'=>'On Sale',
			   'createUserId'=>$id,
			   'ref_restaurant_id'=>0,
			   'ref_DishTypeId'=>0,
			   'hot'=>1

			);
			array_unshift($restaurant_category,$special_array);

			//var_dump($restaurant_category);exit;

		}



	   // 获取该商家的所有coupon 及子coupon 并列表
			$coupon_list =$mdl_coupons->getAllCouponsofUser($id);



	   //如果当前用户购买了那个coupon ,则直接更改其购买的数量
			foreach ($coupon_list as $key => $value) {
				if($this->loginUser) {
					$sql ="select  quantity from cc_wj_user_temp_carts where main_coupon_id=".$coupon_list[$key]['id'] ." and sub_coupon_id=".$coupon_list[$key]["sub_id"]." and  userId=".$this->loginUser['id'];

					$temp_item = $mdl_wj_user_temp_carts->getListBySql($sql);

					if($temp_item) {
						$coupon_list[$key]['qty']=$temp_item[0]['quantity'];
					}else{
						$coupon_list[$key]['qty']=0;
					}

				}else{
					$coupon_list[$key]['qty']=0;
				}
			}

			$this->setData( $coupon_list, 'coupon_list' );


			$this->setData( $restaurant_category, 'restaurant_category' );

			$us=$this->getUserDevice();
			if($us=='desktop'){
				$category_list = $this->fetch('/mobile/restaurant/category');
				$category_list_en = $this->fetch('/mobile/restaurant/category_en');
				$coupon_list = $this->fetch('/mobile/restaurant/coupon_list');
				$title_promotion =$this->fetch('/mobile/restaurant/title_promotion');
				$title_promotion_en =$this->fetch('/mobile/restaurant/title_promotion_en');
			}else{
				$category_list = $this->fetch('/mobile/restaurant/category');
				$category_list_en = $this->fetch('/mobile/restaurant/category_en');
				$coupon_list = $this->fetch('/mobile/restaurant/coupon_list');
				$title_promotion =$this->fetch('/mobile/restaurant/title_promotion');
				$title_promotion_en =$this->fetch('/mobile/restaurant/title_promotion_en');
			}
			$data1=array();



			//首次直接输出
			if($this->getLangStr()=='en'){

				$data1['category'] =$category_list_en;
				$data1['coupon_list'] =$coupon_list;
				$data1['title_promotion'] =$title_promotion_en;

			}else{
				$data1['category'] =$category_list;
				$data1['coupon_list'] =$coupon_list;
				$data1['title_promotion'] =$title_promotion;

			}




			/* 写入数据到静态文件 */

				//$filename='/data/upload/htm/restaurant/category_'.$id.'htm';
				//var_dump($filename);exit;
			//	file_put_contents($filename, $data1['category']);

			$filename =DATA_DIR. 'upload/htm/restaurant/category_'.$id.$refresh_code.'.htm';

			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $category_list);
				fclose($fh);
			}

			$filename =DATA_DIR. 'upload/htm/restaurant/category_'.$id.'_en'.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $category_list_en);
				fclose($fh);
			}

			$filename =DATA_DIR. 'upload/htm/restaurant/title_promotion_'.$id.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $title_promotion);
				fclose($fh);
			}

			$filename =DATA_DIR. 'upload/htm/restaurant/title_promotion_'.$id.'_en'.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $title_promotion_en);
				fclose($fh);
			}

			$filename =DATA_DIR. 'upload/htm/restaurant/coupon_list_'.$id.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $coupon_list);
				fclose($fh);
			}

			echo json_encode($data1); return;
		}



		public  function refresh_menu_action() {

			$business_id= get2('business_id');

			if(!$business_id) {

				$business_id =$this->loginUser['id'];
			}


		 $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

		 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

			$isAuthoriseCustomer =0 ;
			foreach ($authoriseBusinessList as $key => $value) {
					if($business_id ==$value['customer_id']) {
							$isAuthoriseCustomer =1;

					}

				}



			if ($this->loginUser['id'] != $business_id  &&  !$isAuthoriseCustomer) {

				$this->sheader(null,'您无权使用该功能');
			}	//var_dump($this->loginUser['id'] != $business_id);exit;

			// 获得该用户的refreshcode
			$current_user = $this->loadModel('user')->getUserById($this->loginUser['id']);
			$refresh_code_old =$current_user['business_store_refresh_code'];


        //删除该商家的临时文件。 该操作应该在新的文件生成之后。
		$this->restaurantStaticFileDeleteProcess($business_id,$refresh_code_old);

	   //生成新的刷新码
	   // 当用户调用页面时，保证调用新的页面，而不是缓存得旧页面


			$refresh_code =time();

			$data=array(
				'business_store_refresh_code'=>$refresh_code,
				'store_fresh_time'=>$refresh_code

			);
			$this->loadModel('user')->update($data,$business_id);



	   // 重新运行页面，生成新的文件。


			$this->sheader(HTTP_ROOT_WWW.'restaurant/'. $business_id.'?force=1');
		}





	public 	function get_menu_action() {


		/**
		 * 加载模组
		 */
		$mdl_restaurant_menu = $this->loadModel( 'restaurant_menu' );

		$mdl_user=$this->loadModel('user');

		$businessUserIdtt = get2('businessUserId');

		$id = (int)get2( 'id' );
		$no_exist=(int)get2('no_exist');
		if(!$no_exist) return ;

		if(!$id) {
			$id =$businessUserIdtt;
			$businessUserId=$businessUserIdtt;

		}
		if(!$id)$this->sheader(null,'请选择正确餐厅');

		// 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
		$where =array(
			'createUserId' => $id,
			'EvoucherOrrealproduct' =>'restaurant_menu'
		);

		$mdl_coupons =$this->loadModel("coupons");

		$restaurant_coupon= $mdl_coupons->getByWhere($where);

		if(($restaurant_coupon['isApproved']==1 && $restaurant_coupon['status']==4) || $restaurant_coupon['createUserId']==$this->loginUser['id']  || $_SESSION['coupon_private_view_allowed']==$restaurant_coupon['id']) {
			$mdl_user =$this->loadModel("user");
			$business_user =$mdl_user->get($restaurant_coupon['createUserId']);
			$restaurant_coupon['business']=$business_user;

			$this->setData( $restaurant_coupon, 'coupon' );
			$refresh_code =$business_user['business_store_refresh_code'];
			//获得自己店的其它产品

			$this->setData( $restaurant_coupon['id'], 'restaurant_couponID' );

		}

		$where1=array(
			'restaurant_id' => $id,
		);
		$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");

		$restaurant_promotion_manjian=$mdl_restaurant_promotion_manjian->getByWhere($where1);

		if($restaurant_promotion_manjian) {
			$restaurant_promotion_manjian_rates =$restaurant_promotion_manjian[discount]/100;
		}else{
			$restaurant_promotion_manjian_rates=0;
		}
		$this->setData($restaurant_promotion_manjian,'restaurant_promotion_manjian');

		if(!$this->loginUser) {
			$sql="select c.category_cn_name,c.category_en_name,a.*,b.category_id as restaurant_category_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id . "  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." ) and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id order by b.category_sort_id,menu_order_id,menu_id";
			$menu = $mdl_restaurant_menu->getListBySql($sql);
			//var_dump($sql);exit;
			//显示新价格
			foreach ($menu as $key => $value) {
				$menu[$key]['new_price'] =number_format($menu[$key]['price'] *(1-$restaurant_promotion_manjian_rates),2);


			}

			$sql_special="select '本期特价' as category_cn_name, 'On Sale' as category_en_name,a.*,'1000' as  restaurant_category_id,'1' as category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id . "  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and a.onSpecial =1 and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by a.menu_order_id,a.menu_id";
			$menu_sub = $mdl_restaurant_menu->getListBySql($sql_special);
			foreach ($menu_sub as $key => $value) {
				$menu_sub[$key]['price'] =$value['speical_price'];
				$menu_sub[$key]['onSpecial'] =1;
				if($value['original_price']<=0) { //如果原价为空

						$menu_sub[$key]['original_price'] =$value['price'];
						$value['original_price'] =$value['price'];

				}
			
				if($value['limit_buy_qty']>0 ) {
					
					if($value['original_price']>$value['speical_price']) {
						
					
							$menu_sub[$key]['menu_cn_name']='('.$this->lang->limit_buy.' '. $value['limit_buy_qty'].' '.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];
				
						
					}else{
							$menu_sub[$key]['menu_cn_name']='('.$this->lang->limit_buy.' '. $value['limit_buy_qty'].')'.$menu_sub[$key]['menu_cn_name'];
			
						
					}
					
				}else{
					if($value['original_price']>$value['speical_price']) {
					
					$menu_sub[$key]['menu_cn_name']='('.$this->lang->special_price.')'.$menu_sub[$key]['menu_cn_name'];
					}else{
						
					}
				}

			}

			foreach ($menu as $key => $value) {
				$menu[$key]['onSpecial'] =0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
				if($value['original_price']<=0) { //如果原价为空

						$menu[$key]['original_price'] =$value['price'];
						$value['original_price']=$value['price'];

				}
			}
			//array_unshift($menu,$menu_sub);
            $menu=array_merge($menu_sub,$menu);


		}else{
	     	//清除购物车其它产品
			$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');
			$where =array (
				'businessUserId <>' . $id,
				'userId '=> $this->loginUser['id']
			);
			//$mdl_wj_user_temp_carts->deleteByWhere($where);


			//菜单列表
			$sql="select c.category_cn_name, c.category_en_name,a.*,b.category_id as restaurant_category_id,b.category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id . "  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by b.category_sort_id,a.menu_order_id,a.menu_id";
			$menu = $mdl_restaurant_menu->getListBySql($sql);

			$sql_special="select '本期特价' as category_cn_name, 'On Sale' as category_en_name,a.*,'1000' as  restaurant_category_id,'1' as category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id . "  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and a.onSpecial =1 and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by a.menu_order_id,a.menu_id";
			$menu_sub = $mdl_restaurant_menu->getListBySql($sql_special);
			foreach ($menu_sub as $key => $value) {
				$menu_sub[$key]['price'] =$value['speical_price'];
				$menu_sub[$key]['onSpecial'] =1;

				if($value['original_price']<=0) { //如果原价为空

						$menu_sub[$key]['original_price'] =$value['price'];
						$value['original_price']=$value['price'];

				}
					if($value['limit_buy_qty']>0 ) {
					
					if($value['original_price']>$value['speical_price']) {
						
					
							$menu_sub[$key]['menu_cn_name']='('.$this->lang->limit_buy.' '. $value['limit_buy_qty'].' '.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];
				
						
					}else{
							$menu_sub[$key]['menu_cn_name']='('.$this->lang->limit_buy.' '. $value['limit_buy_qty'].')'.$menu_sub[$key]['menu_cn_name'];
			
						
					}
					
				}else{
					if($value['original_price']>$value['speical_price']) {
					
					$menu_sub[$key]['menu_cn_name']='('.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];
					}else{
						$menu_sub[$key]['menu_cn_name']='('.$this->lang->special_price.')'.$menu_sub[$key]['menu_cn_name'];
					}
				}
			}

			foreach ($menu as $key => $value) {
				$menu[$key]['onSpecial'] =0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
			if($value['original_price']<=0) { //如果原价为空

						$menu[$key]['original_price'] =$value['price'];


				}
			}
			//array_unshift($menu,$menu_sub);
            $menu=array_merge($menu_sub,$menu);


			//////    做到这里 ，这块 的 价格 需要替换成 特价  加紧购物车价格有问题 / 未登陆的用户还没有处理 / 原价显示不对 （原价显示逻辑是 如果原价未0 ，则找现价未原价， 之前的原价，如果未空或与现价相等则不显示
		//  var_dump($menu);exit; //  需要检查中文/英文 /登陆/未登录的所有场景
			//显示新价格
			foreach ($menu as $key => $value) {
				//$menu[$key]['onSpecial'] =0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
				if ($this->getLangStr() =='en') {
					if(!$menu[$key]['menu_en_name']) {
						$menu[$key]['menu_en_name']=$menu[$key]['menu_cn_name'];
					}

				}

				$menu[$key]['new_price'] =number_format($menu[$key]['price'] *(1-$restaurant_promotion_manjian_rates),2);

				//加载购物车已购买数量
				//var_dump('here');exit;
				$sql ="select quantity from cc_wj_user_temp_carts where main_coupon_id=".$restaurant_coupon['id']." and menu_id=".$value['id']." and sidedish_menu_id=0 and userId=".$this->loginUser['id'];

				$result = $mdl_wj_user_temp_carts->getListBySql($sql);
				$menu[$key]['quantity']=$result[0]['quantity'];
			}

			//加载配菜
			$mdl_sidedish_menu=$this->loadModel('restaurant_sidedish_menu');
			foreach ($menu as $key => $value) {
				if($menu[$key]['sidedish_category']>0){
					$menu[$key]['sidedish_menu']=$mdl_sidedish_menu->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['sidedish_category']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					foreach ($menu[$key]['sidedish_menu'] as $k => $v) {
						//配菜新价格
						$menu[$key]['sidedish_menu'][$k]['new_price'] =number_format($menu[$key]['sidedish_menu'][$k]['price'] *(1-$restaurant_promotion_manjian_rates),2);
					}

				}
			}

			//加载菜品规格
			$mdl_menu_option=$this->loadModel('restaurant_menu_option');
			foreach ($menu as $key => $value) {
				if($menu[$key]['menu_option']>0){
					$menu[$key]['menu_option_list']=$mdl_menu_option->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['menu_option']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					foreach ($menu[$key]['menu_option_list'] as $k => $v) {
						//配菜新价格
						$menu[$key]['menu_option_list'][$k]['new_price'] =number_format($menu[$key]['menu_option_list'][$k]['price'] *(1-$restaurant_promotion_manjian_rates),2);
					}

				}
			}
		}

// 换 en cn
		$old_cat="";
		foreach ($menu as $key => $value) {
			$new_cat =$menu[$key]['category_cn_name'];
			// 如果en不为空，则保存en cate名称
			if ($menu[$key]['category_en_name']) {
				$new_cat_en =$menu[$key]['category_en_name'];
			}else{
				// 如果英文分类为空，换中文
				$menu[$key]['category_en_name']=$menu[$key]['category_cn_name'];
				$new_cat_en =$new_cat;

			}
			if($old_cat<>$new_cat) {
				$menu[$key]['new_cat']=$new_cat;
				$menu[$key]['new_cat_en']=$new_cat_en;
				$old_cat=$new_cat;
			}else{
				$menu[$key]['new_cat']=0;
				$menu[$key]['new_cat_en']=0;
			}


		}

		if ( count($menu)<=7) {
			$us=$this->getUserDevice();



			if($us=='desktop'){

				$menu_en = $this->fetch('/mobile/restaurant/menu_en');
				$menu = $this->fetch('/mobile/restaurant/menu');
			}else{
				$menu_en = $this->fetch('/mobile/restaurant/menu_en');
				$menu = $this->fetch('/mobile/restaurant/menu');
			}



			$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$id.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $menu);
				fclose($fh);
			}

			$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$id.'_en'.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $menu_en);
				fclose($fh);
			}

			if ($this->getLangStr()=='en'){
				echo $menu_en; return;
			}else{
				echo $menu; return;

			}
		}else{


			$this->setData( $restaurant_promotion_manjian_rates*100, 'restaurant_promotion_manjian_rates' );


			$menu1 =array_slice($menu,0,7);

			$us=$this->getUserDevice();

			$this->setData( $menu1, 'menu' );
			$this->setData( 0,'lazyload');
			$this->setData( 0,'start_index');



			if($us=='desktop'){
				$menu1_en = $this->fetch('/mobile/restaurant/menu_en');
				$menu1 = $this->fetch('/mobile/restaurant/menu');
			}else{
				$menu1_en = $this->fetch('/mobile/restaurant/menu_en');
				$menu1 = $this->fetch('/mobile/restaurant/menu');
			}




			$filename =DATA_DIR. 'upload/htm/restaurant/menu_'.$id.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $menu1);
				fclose($fh);
			}

			$filename =DATA_DIR. 'upload/htm/restaurant/menu_'.$id.'_en'.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $menu1_en);
				fclose($fh);
			}

			//var_dump($menu);exit;
			$menu2 =array_slice($menu,7);
			$this->setData( 1,'lazyload');
			$this->setData( 7,'start_index');
			$us=$this->getUserDevice();
			$this->setData( $menu2, 'menu' );


			if($us=='desktop'){
				$menu2_en = $this->fetch('/mobile/restaurant/menu_en');
				$menu2 = $this->fetch('/mobile/restaurant/menu');
			}else{
				$menu2_en = $this->fetch('/mobile/restaurant/menu_en');
				$menu2 = $this->fetch('/mobile/restaurant/menu');
			}


			$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$id.'_1'.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $menu2);
				fclose($fh);
			}

			$filename = DATA_DIR.'upload/htm/restaurant/menu_'.$id.'_1_en'.$refresh_code.'.htm';
			if(!is_file($filename)){
				$fh = fopen($filename, "w"); //w从开头写入 a追加写入
				fwrite($fh, $menu2_en);
				fclose($fh);
			}

			if ($this->getLangStr()=='en'){
				echo $menu1_en.$menu2_en; return;
			}else{
				echo $menu1.$menu2; return;

			}
		}

	}

	function get_business_info_action() {
		/**
		 * 加载模组
		 */
		$mdl_restaurant_menu = $this->loadModel( 'restaurant_menu' );

		$mdl_user=$this->loadModel('user');
		$start_time=time();
		$this->setData($start_time,'start_time');


		$businessUserIdtt = get2('businessUserId');

		$id = (int)get2( 'id' );


		if(!$id) {
			$id =$businessUserIdtt;
			$businessUserId=$businessUserIdtt;

		}
		if(!$id)$this->sheader(null,'请选择正确商家');

		// 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
		$where =array(
			'createUserId' => $id,
			'EvoucherOrrealproduct' =>'restaurant_menu'
		);

		$mdl_coupons =$this->loadModel("coupons");

		$restaurant_coupon= $mdl_coupons->getByWhere($where);

		if(($restaurant_coupon['isApproved']==1 && $restaurant_coupon['status']==4) || $restaurant_coupon['createUserId']==$this->loginUser['id']  || $_SESSION['coupon_private_view_allowed']==$restaurant_coupon['id']) {
			$mdl_user =$this->loadModel("user");
			$business_user =$mdl_user->get($restaurant_coupon['createUserId']);
			$restaurant_coupon['business']=$business_user;

			$this->setData( $restaurant_coupon, 'coupon' );

			//获得自己店的其它产品

			$selfProduct =$mdl_coupons->getListBySql("select * from cc_coupons where createUserId =".$restaurant_coupon['createUserId'] . " and isApproved =1 and status=4 and EvoucherOrrealproduct <> 'restaurant_menu'");

			$this->setData($selfProduct,"selfProduct");

			$this->setData($mdl_coupons->getRecommendProduct($restaurant_coupon['id']), 'recommends' );

			$this->setData( $restaurant_coupon['id'], 'restaurant_couponID' );

		}


		$where1=array(
			'restaurant_id' => $id,
		);
		$mdl_restaurant_promotion_manjian =$this->loadModel("restaurant_promotion_manjian");

		$restaurant_promotion_manjian=$mdl_restaurant_promotion_manjian->getByWhere($where1);
		//var_dump($id);exit;
		if($restaurant_promotion_manjian) {
			$restaurant_promotion_manjian_rates =$restaurant_promotion_manjian[discount]/100;
		}else{
			$restaurant_promotion_manjian_rates=0;
		}
		$this->setData($restaurant_promotion_manjian,'restaurant_promotion_manjian');

		if(!$this->loginUser) {
			$sql="select c.category_cn_name,a.*,b.category_id as restaurant_category_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where a.restaurant_id=".$id . "  and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id order by b.category_sort_id,menu_id";
			$menu = $mdl_restaurant_menu->getListBySql($sql);

			//显示新价格
			foreach ($menu as $key => $value) {
				$menu[$key]['new_price'] =number_format($menu[$key]['price'] *(1-$restaurant_promotion_manjian_rates),2);


			}
		}else{
	     	//清除购物车其它产品
			$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');
			$where =array (
				'businessUserId <>' . $id,
				'userId '=> $this->loginUser['id']
			);
			//$mdl_wj_user_temp_carts->deleteByWhere($where);


			//菜单列表
			$sql="select c.category_cn_name,a.*,b.category_id as restaurant_category_id,b.category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where a.restaurant_id=".$id . "  and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id order by b.category_sort_id,menu_id";
			$menu = $mdl_restaurant_menu->getListBySql($sql);


			//显示新价格
			foreach ($menu as $key => $value) {
				$menu[$key]['new_price'] =number_format($menu[$key]['price'] *(1-$restaurant_promotion_manjian_rates),2);

				//加载购物车已购买数量
				$sql ="select quantity from cc_wj_user_temp_carts where main_coupon_id=".$restaurant_coupon['id']." and menu_id=".$value['id']." and sidedish_menu_id=0 and userId=".$this->loginUser['id'];

				$result = $mdl_wj_user_temp_carts->getListBySql($sql);
				$menu[$key]['quantity']=$result[0]['quantity'];
			}

			//加载配菜
			$mdl_sidedish_menu=$this->loadModel('restaurant_sidedish_menu');
			foreach ($menu as $key => $value) {
				if($menu[$key]['sidedish_category']>0){
					$menu[$key]['sidedish_menu']=$mdl_sidedish_menu->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['sidedish_category']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					foreach ($menu[$key]['sidedish_menu'] as $k => $v) {
						//配菜新价格
						$menu[$key]['sidedish_menu'][$k]['new_price'] =number_format($menu[$key]['sidedish_menu'][$k]['price'] *(1-$restaurant_promotion_manjian_rates),2);
					}

				}
			}


			//加载菜品规格
			$mdl_menu_option=$this->loadModel('restaurant_menu_option');
			foreach ($menu as $key => $value) {
				if($menu[$key]['menu_option']>0){
					$menu[$key]['menu_option_list']=$mdl_menu_option->getList(null,array('restaurant_id'=>$menu[$key]['restaurant_id'],'restaurant_category_id'=>$menu[$key]['menu_option']," (length(menu_cn_name)>0 or length(menu_en_name)>0) "));

					foreach ($menu[$key]['menu_option_list'] as $k => $v) {
						//配菜新价格
						$menu[$key]['menu_option_list'][$k]['new_price'] =number_format($menu[$key]['menu_option_list'][$k]['price'] *(1-$restaurant_promotion_manjian_rates),2);
					}

				}
			}

			/* 获得该用户订单数量,总额 ,同时 生成点击订单按钮的显示数据  */

			$userId=$this->loginUser['id'];

			$sql ="SELECT category_sort_id,category_cn_name,a.*,b.menu_pic as pic ,d.pic as coupon_pic FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id  where a.userId=" .$userId."   and businessUserId =".$id . " order by category_sort_id,b.menu_id";


			$cartItems=$mdl_wj_user_temp_carts->getListBySql($sql);

			$cartTotalPrice = 0;


			$old_category='0';
			$totalQuantity=0;
			foreach ($cartItems as $key => $val) {
				$totalQuantity+=$val['quantity'];
				if ($val['category_sort_id'] !== $old_category) {
						if(!$val['category_sort_id']) { // 没有分类 就是团购套餐

							$cartItems[$key]['category_cn_name']='团购';
						}
						$cartItems[$key]['new_cat']=1;
					}else{
						$cartItems[$key]['new_cat']=0;

					}
					$old_category=$val['category_sort_id'];
					if(!$val['category_sort_id']) {
						$cartItems[$key]['pic']=$val['coupon_pic'];
					}

					if ($val['main_coupon_id']== $restaurant_coupon['id']){

						$menu_totalprice += $val['single_amount']*$val['quantity'];
					}else{

						$voucher_totalprice += $val['single_amount']*$val['quantity'];

					}


					$cartTotalPrice+=$val['single_amount']*$val['quantity'];
				}

				$this->setData($cartTotalPrice,'totalPrice');
				$this->setData($totalQuantity,'totalQuantity');

				$this->setData($cartItems,'items');
				$this->setData($businessUserId,'businessUserId');
			}

		//加载餐馆菜单
			$mdl_restaurant_category=$this->loadModel('restaurant_category');
			$restaurant_category=$mdl_restaurant_category->getListBySql("select * from cc_restaurant_category where restaurant_id = ".$id . " and (length(category_cn_name)>0 or length(category_en_name)>0) order by `category_sort_id`");

	   // 获取该商家的所有coupon 及子coupon 并列表
			$coupon_list =$mdl_coupons->getAllCouponsofUser($id);

	   //如果当前用户购买了那个coupon ,则直接更改其购买的数量
			foreach ($coupon_list as $key => $value) {
				if($this->loginUser) {
					$sql ="select  quantity from cc_wj_user_temp_carts where main_coupon_id=".$coupon_list[$key]['id'] ." and sub_coupon_id=".$coupon_list[$key]["sub_id"]." and  userId=".$this->loginUser['id'];

					$temp_item = $mdl_wj_user_temp_carts->getListBySql($sql);

					if($temp_item) {
						$coupon_list[$key]['qty']=$temp_item[0]['quantity'];
					}else{
						$coupon_list[$key]['qty']=0;
					}

				}else{
					$coupon_list[$key]['qty']=0;
				}
			}

			$this->setData( $coupon_list, 'coupon_list' );
			$this->setData( $restaurant_category, 'restaurant_category' );

			$this->setData( $restaurant_promotion_manjian_rates*100, 'restaurant_promotion_manjian_rates' );
			$this->setData( $menu, 'menu' );
			$this->setData( $id, 'restaurant_id' );
			$this->setData( $menu_totalprice, 'totalprice' );
			$this->setData( $voucher_totalprice, 'voucher_totalprice' );
			$title = str_replace('|' ,'',$restaurant_coupon['title']);
			$this->setData(  $title. ' | '.'cityb2b' , 'pageTitle' );
			$this->setData($title. ' | '.'cityb2b', 'pageKeywords' );
			$this->setData( $title. ' | '.'cityb2b', 'pageDescription' );

			$this->setData( get2('action'), 'returnAction' );

			$this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');
			
			$delivery_fee_desc  = $this->get_business_delivery_des ($business_user['id']);
			$this->setData($delivery_fee_desc, 'delivery_fee_desc');
			

			$us=$this->getUserDevice();
			if($us=='desktop'){
				$business_info = $this->fetch('/mobile/restaurant/sec_explain');
			}else{
				$business_info = $this->fetch('/mobile/restaurant/sec_explain');
			}
			echo $business_info; return;
		}

//以下程序为： 检查对应id的条形码在产品库中是否存在，如果不存在，可以将该信息更新到产品苦衷，如果存在，那检查这条标准库中的产品图片来源
					// 如果该来源为该用户，那么，该用户的图片更新可以覆盖之前的图片，如果该图片来源与其它渠道，该用户的图片更新无法更新产品库。

function updateStandProductUponMenuId($id) {
	
	
	$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
	$curr_rec = $mdl_restaurant_menu->get($id);
					if(strlen($curr_rec['barcode_number'])<=7) {
						//不做处理
						
					}else{
					  $mdl_standard_product_info= $this->loadModel('standard_product_info');
					  $rec = $mdl_standard_product_info->ifFindBarcode($curr_rec['barcode_number']);
					  if($rec) {
						  //产品库中存在该记录
						 // var_dump ('产品库里存在记录'.$rec['source_business_id'].' ' .$curr_rec['restaurant_id']);exit; 
							if($rec['source_business_id']==$curr_rec['restaurant_id']) {
								//来源相同，则该商家新更新的图片可以覆盖之前的图片。
								$updateData001 = array(
									     'images1'=>$curr_rec['menu_pic'],
										 'imagesmore'=>$curr_rec['menu_pics']
									  
									  );
									  $mdl_standard_product_info->update($updateData001,$rec['id']);
								
							}else{
								//如果来源不同， 则检查当前产品库的图片字段是否有信息，如果有信息，则不能进行更新，如果没有图片信息则可以更新。
								if($rec['images1']) {
									//不做处理
									
								}else{
									//没有图片则可以更新该用户的图片。
								
									$updateData001 = array(
									     'images1'=>$curr_rec['menu_pic'],
										 'imagesmore'=>$curr_rec['menu_pics']
									  
									  );
									  $mdl_standard_product_info->update($updateData001,$rec['id']);
								}
								
							}
						 
						  
					  }else{
						   //产
						  
						     
								  if(!$curr_rec['menu_en_name']) {
									  $title =$curr_rec['menu_cn_name'];
								  }else{
									  $title =$curr_rec['menu_en_name'];
									  
								  }
								  
								  
								   if(!$curr_rec['menu_cn_name']) {
									  $title_cn =$curr_rec['menu_en_name'];
								  }else{
									  $title_cn =$curr_rec['menu_cn_name'];
								  }
								  
								  
								  if(!$curr_rec['menu_desc']) {
									  $description_cn =$curr_rec['menu_en_desc'];
								  }else{
									  $description_cn =$curr_rec['menu_desc'];
									  
								  }
								  
								  
								  if(!$curr_rec['menu_en_desc']) {
									  $description =$curr_rec['menu_desc'];
								  }else{
									  $description =$curr_rec['menu_en_desc'];
									  
								  }
								  
								  
								
								  $insertData001=array(
								  
								     'barcode_number'=>$curr_rec['barcode_number'],
									 'title'=>$title,
									 'title_cn'=>$title_cn,
									 'category'=>$curr_rec['restaurant_category_id'],
									 'description'=>$description,
									 'description_cn'=>$description_cn,
									 'images1'=>$curr_rec['menu_pic'],
									 'imagesmore'=>$curr_rec['menu_pics'],
									 'grapType'=>1,
									 'source_business_id'=>$curr_rec['restaurant_id']
								  
									  
								  );
								    //var_dump ($insertData001);exit; 
								   $mdl_standard_product_info->insert($insertData001);
						  
					  }
					  
						
					}
					
	
	
	
	
	
	
}

		function menu_pic_action()
		{
			$id =get2('id');
			$this->setData($id,'id');
			$data= $this->loadModel('restaurant_menu')->get($id);
			$this->setData($data,'data');
			
	
			if (is_post()) {
				$id =post('id');
				$images = post('images');
				$content                    = post('content');
				foreach ($images as $key => $value) {
					if($value=="default/image_upload.jpg")
						unset($images[$key]);
					else
						$images[$key]=trim($value);
				}

				$data=array(
					'menu_pic'=>trim(reset($images)),
					'menu_pics'=>trim(serialize(array_slice($images, 1)))
				);
              $data['content']                    = $content;
			//  var_dump($data);exit;

				$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
				if ($mdl_restaurant_menu->update($data,$id)) {
					//以下程序为： 如果用户更新了图片，则要检查对应的条形码在产品库中是否存在，如果不存在，可以将该信息更新到产品苦衷，如果存在，那检查这条标准库中的产品图片来源
					// 如果该来源为该用户，那么，该用户的图片更新可以覆盖之前的图片，如果该图片来源与其它渠道，该用户的图片更新无法更新产品库。
					
					$this->updateStandProductUponMenuId($id);
					
					$this->form_response_msg( '保存成功');
				} else {

					$this->form_response_msg('保存失败');
				}
			} else {
				$this->setData('添加菜单图片 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');
				$this->setData('restaurant_menu', 'submenu');
				$this->setData('restaurant', 'menu');
				$this->display('restaurant/menu_pic');
			}
			
			
			
						
			/*
			
                if (is_post()) {

                    $images=post('images');
                    if($images){
                        foreach ($images as $key => $value) {
                            if($value=="default/image_upload.jpg")
                                unset($images[$key]);
                            else
                                $images[$key]=trim($value);
                        }
                        $data['pic']=reset($images);

                        $data['pics'] = serialize(array_slice($images, 1));
                    }else{
                       $this->freshfood_edit_toStep($coupon['id'], 4); //no data no update. slow internet error
                    }

                    if ($mdl_coupons->update($data, $coupon['id'])) {
                        $this->freshfood_edit_toStep($coupon['id'], 4);
                    } else {
                        $this->coupons_edit_failure('保存失败');
                    }

                }
			
			*/
			
			
			
			
			
			
			
		}

	function restaurant_parant_category_edit_action(){


		$freshfood =get2('freshfood');
		$this->setData($freshfood,'freshfood');



		//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

		$mdl = $this->loadModel('authrise_manage_other_business_account');
		$authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
		//var_dump($authoriseBusinessList);exit;

		$this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

		$customer_id =get2('customer_id');
		if(!$customer_id) {
			$customer_id =$this->loginUser['id'];

		}
		$isAuthoriseCustomer =0 ;
		foreach ($authoriseBusinessList as $key => $value) {
			if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id']) {
				$isAuthoriseCustomer =1;
			}

		}






		if ($isAuthoriseCustomer) {

			$mdl_restaurant_category = $this->loadModel('restaurant_category');
			$countOfcat =$mdl_restaurant_category->getCountOfCategory($customer_id);
			$exist = $mdl_restaurant_category->getByWhere(array('createUserId'=>$customer_id));

			if(!$exist){
				$category_id =100;
				$category_sort_id=200;

				for($i=0;$i<50;$i++) {
					$menu_category_info=array(
						'category_cn_name'=>'',
						'category_en_name'=>'',
						'restaurant_id'=>$customer_id,
						'category_id'=>$category_id,
						'category_sort_id'=>$category_sort_id,
						'createUserId'=>$customer_id,
						'parent_category_id'=>0
					);
					$mdl_restaurant_category->insert($menu_category_info);
					$category_id =$category_id+100;
					$category_sort_id =$category_sort_id +10;
				}
			}else{

				$sqlexist =" select count(*) as count  from  cc_restaurant_category where createUserId =$customer_id  &&  parent_category_id =0 && (length(category_cn_name)=0 && length(category_en_name)=0) ";
				$category_list = $mdl_restaurant_category->getListBySql($sqlexist);
				if( $category_list[0]['count'] <=10)
				{ //如果子分类下面可用分类不足10条，则增加10条子分类
					$newAddRecordCount = 10;
				}else{

					$newAddRecordCount = 0;
				}

				//var_dump($newAddRecordCount);exit;
				//增加该大类下的子分类

				$category_id   =100*(1+$countOfcat);
				$category_sort_id=10*(1+$countOfcat);
//var_dump ($category_list);exit;
				for($i=0;$i<$newAddRecordCount;$i++) {
					$menu_category_info=array(
						'category_cn_name'=>'',
						'category_en_name'=>'',
						'restaurant_id'=>$customer_id,
						'category_id'=>$category_id,
						'category_sort_id'=>$category_sort_id,
						'createUserId'=>$customer_id,
						'parent_category_id'=>0
					);
					//var_dump($menu_category_info);exit;
					$mdl_restaurant_category->insert($menu_category_info);
					$category_id =$category_id+100;
					$category_sort_id =$category_sort_id +10;
				}







			}

			$pageSql = "select  * from cc_restaurant_category where restaurant_id=$customer_id  and (parent_category_id=0 or  parent_category_id is null) and isdeleted =0   order by  ishide,category_sort_id,category_cn_name desc ";


			//var_dump($pageSql);exit;
			$pageUrl = $this->parseUrl()->set('page');
			$pageSize =50;
			$maxPage = 100;
			$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
			$data = $mdl_restaurant_category->getListBySql($page['outSql']);

			$this->setData($data, 'data');
			$this->setData($customer_id,'customer_id');

			$sql_Parent_cate_list ="select  * from cc_restaurant_category where restaurant_id=".$customer_id. "  and (length(category_cn_name)>0 or length(category_en_name)>0) and ( parent_category_id =0 or  parent_category_id is null) and isdeleted =0 order by parent_category_id,category_sort_id ";
			$data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
			$this->setData($data_parent_cate_list, 'data_parent_cate_list');
			//var_dump($sql_Parent_cate_list);exit;
			$this->setData($page['pageStr'], 'pager');
			$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

		}else {
			//do nothing  attack

		}






		$this->setData('restaurant_parant_category_edit', 'submenu_top');
		$this->setData('restaurant_parant_category_edit', 'submenu');
		$this->setData('index_publish', 'menu');

		$pagename = "店铺一级品类管理";
		$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];


		$this->setData($pagename, 'pagename');
		$this->setData($pageTitle, 'pageTitle');

		$this->setData($this->loginUser['gst_type'], 'gstType');
		$this->display_pc_mobile('restaurant/restaurant_parant_category_edit', 'restaurant/restaurant_parant_category_edit');

	}
		

		/* 一级分类折扣编辑*/

function discount_edit_parant_category_action(){

	    //输入参数合法性验证
	     $customer_id =get2('customer_id');

		if(!$customer_id) {
			var_dump('please choose the customer !'); exit;

		}
		$this->setData($this->loadModel('user_factory')->getUserCodeandName($customer_id, $this->current_business['id']),'customer_info');

		$this->setData($customer_id,'customer_id');



	    //操作权限： 检查当前用户对当前的客户是否拥有操作权限
        if(!$this->loadModel('user_factory')->isUserAuthorisedToOperate($customer_id,$this->current_business['id']))
		{
			var_dump('you are not allow to operate this customer !'); exit;
		}



		//获取当前供应商大类及改客户关于各个大类的折扣率

		$mdl = $this->loadModel('user_factory_category_discount_rate');
		$parent_cate_discount_rate_data = $mdl->get_discount_data($this->current_business['id'],$customer_id);

       // var_dump($parent_cate_discount_rate_data);exit;
	   //session传递值供前端操作 。




		$this->setData($parent_cate_discount_rate_data, 'data');
		//var_dump($sql_Parent_cate_list);exit;




			$this->setData('discount_edit_parant_category', 'submenu_top');
			$this->setData('customer_price_management', 'submenu');
			$this->setData('customer_management', 'menu');

		    $pagename = "Discount Management";
			$pageTitle=  $pagename." - Business_centre - ". $this->site['pageTitle'];
			$this->setData($pagename, 'pagename');
			$this->setData($pageTitle, 'pageTitle');


			$this->display( 'restaurant/discount_edit_parant_category');

	}




	/* 一级分类折扣编辑*/

	function discount_edit_sub_category_action(){

		//输入参数合法性验证
		$customer_id =get2('customer_id');
		if(!$customer_id) {
			var_dump('please choose the customer !'); exit;
		}

		$this->setData($this->loadModel('user_factory')->getUserCodeandName($customer_id, $this->current_business['id']),'customer_info');
		$parent_category_id =get2('parent_category_id');
		$this->setData($customer_id,'customer_id');
		$this->setData($parent_category_id,'parent_category_id');




		//操作权限： 检查当前用户对当前的客户是否拥有操作权限
		if(!$this->loadModel('user_factory')->isUserAuthorisedToOperate($customer_id,$this->current_business['id']))
		{
			var_dump('you are not allow to operate this customer !'); exit;
		}


		//获取当前供应商大类及改客户关于各个大类的折扣率

		$mdl = $this->loadModel('user_factory_category_discount_rate');
		$parent_cate_discount_rate_data = $mdl->get_discount_data($this->current_business['id'],$customer_id);
		$this->setData($parent_cate_discount_rate_data, 'data1');
       //  var_dump($parent_cate_discount_rate_data);exit;


		//获取当前供应商大类及改客户关于各个大类的折扣率
       if($parent_category_id) {

		   $sub_cate_discount_rate_data = $mdl->get_sub_discount_data($this->current_business['id'],$customer_id,$parent_category_id);

		   $this->setData($sub_cate_discount_rate_data, 'data');
		 //  var_dump($sub_cate_discount_rate_data);exit;

	   }

		//session传递值供前端操作 。

		$this->setData('discount_edit_sub_category', 'submenu_top');
		$this->setData('customer_price_management', 'submenu');
		$this->setData('customer_management', 'menu');

		$pagename = "Discount Management";
		$pageTitle=  $pagename." - Business_centre - ". $this->site['pageTitle'];
		$this->setData($pagename, 'pagename');
		$this->setData($pageTitle, 'pageTitle');


		$this->display( 'restaurant/discount_edit_sub_category');

	}



// 分类调整 ，指定源类和目标类，将产品标记薇源类的产品都换到目标类别


function category_migration_action(){
	
	
		$freshfood =get2('freshfood');
		$this->setData($freshfood,'freshfood');
		
		
		$sourceCat =get2('sourceCat');
		$this->setData($sourceCat,'sourceCat');

		$destCat =get2('destCat');
		$this->setData($destCat,'destCat');
		
	
		//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

		 $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
		//var_dump($authoriseBusinessList);exit;

		 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
		
		 $customer_id =get2('customer_id');
		 if(!$customer_id) {
			  $customer_id =$this->loginUser['id'];

			}
		 $isAuthoriseCustomer =0 ;
		 foreach ($authoriseBusinessList as $key => $value) {
				if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id']) {
						$isAuthoriseCustomer =1;
				}

			}
		

		if ($isAuthoriseCustomer) {
			
			 if($sourceCat && $destCat) {
				 
				 
				 $oldDestCat =$destCat;
				 
				  $oldSourceCat =$sourceCat;
				//获得了源分类和目标分类，可以开始转换
				
				 // 可能有两个表被转换，一个时 menu主表的分类，另外一个是category_menu表 记录产品可能存在的多分分类。
				 
				 // step 1  修改主表的分类
				 
				 $mdl_restaurant_menu = $this->loadModel('restaurant_menu');
				 
				 
				 
				 // 获得该分类的所有数据集
				 
				 $menuListForSourCategory =  $mdl_restaurant_menu->getList(null, array('restaurant_category_id' => $sourceCat));
				// var_dump($menuListForSourCategory);exit;
				 
				 
				 
				 
				 //首先查找该分类是否为子类，如果是子类，则获得大类后,修改成指定的大类。 如果是大类直接修改
				 
				 $newSourceCateRec =$this->loadModel('restaurant_category')->get($sourceCat);
				 if($newSourceCateRec['parent_category_id']) { //如果非空或0 表示是一个子类，需转黄成主类
					 $sourceCat = $newSourceCateRec['parent_category_id'];
					 
				 }
				 
				  $newDescCateRec =$this->loadModel('restaurant_category')->get($destCat);
				 if($newDescCateRec['parent_category_id']) { //如果非空或0 表示是一个子类，需转黄成主类
					 $destCat = $newDescCateRec['parent_category_id'];
					 
				 }
					 
				 
				 $updatedata =array (
				    'restaurant_category_id'=>$destCat
				 );
				 
				 $where  =array (
				    'restaurant_category_id'=>$sourceCat
				 );
				 
				 $count1 = $mdl_restaurant_menu->updateByWhere($updatedata,$where);
				 
				 
				 
				 
				 
				 if ( !$count) {
					 
					// var_dump('操作失败！');exit;
				 }
				 
				 
			  // 对这个表可以直接进行修改。 而且要做一个增加的处理， 因为 将某个类别 修改成某个大类的子类，在该表中可能不存在数据，所以要进行检测，如果未发现则进行添加 ，要首先获得所有
			  //该子类的产品，进行循环添加。
			  
			  
				$mdl_restaurant_menu_category = $this->loadModel('restaurant_menu_category');
				 
				 $updatedata =array (
				    'category_id'=>$oldDestCat
				 );
				 
				 $where  =array (
				    'category_id'=>$oldSourceCat
				 );
				 
				 $count2 = $mdl_restaurant_menu_category->updateByWhere($updatedata,$where);
				 
				 
				 // 将前面获得的源列表 在多级分类中逐一添加
				 $countMulticate =0;
				  foreach ($menuListForSourCategory as $key => $value) {
					  
					  $data =array(
					  'restaurant_menu_id'=>$value['id'],
					  'category_id'=>$oldDestCat//destCat 有可能已经能被准换成其对应的大类， 因为在resataruant_menu中只允许填写大类 ，而在多级分类表，这个要改的表，需要写进小类
					  
					  );
					  $mdl_restaurant_menu_category->insert($data);
                       $countMulticate++; 
                    }
				 
				 
				
				$result ="本次成功转换".$count1."个主表分类，及更新了".$count2."个多级分类表，并增加了".$countMulticate."到多级分类表";
				$this->setData($result,'result');
				
				
			 }
				
			
		}
	
	
	       
			$sql_Parent_cate_list ="select *,  if(`parent_category_id`,concat('---',category_cn_name),category_cn_name) as category_cn_name1 ,if(`parent_category_id`,concat(category_cn_name),category_cn_name) as   category_cn_name2 ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id  from cc_restaurant_category where restaurant_id=$customer_id and (length(category_cn_name)>0 or length(category_en_name)>0) order by ishide,parent_id, category_sort_id,category_cn_name ";
			$mdl_restaurant_category = $this->loadModel('restaurant_category');
			$data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
			//var_dump($data_parent_cate_list);exit;

			$this->setData($data_parent_cate_list, 'data_parent_cate_list');

	
			$this->setData($authoriseBusinessList, 'authoriseBusinessList');
			 $this->setData('category_migration', 'submenu');
			$this->setData('index_publish', 'menu');

			$pagename = "产品分类迁移";
			$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];


			$this->setData($pagename, 'pagename');
			$this->setData($pageTitle, 'pageTitle');
			$this->display_pc_mobile('restaurant/category_migration', 'restaurant/category_migration');

	
	
	
}


	/**
	 * 菜单分类编辑页面
	 */

	function restaurant_edit_action(){


		$freshfood =get2('freshfood');
		$this->setData($freshfood,'freshfood');
		
		
		$cat_id =get2('cat_id');
		$this->setData($cat_id,'cat_id');

			
	
		//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

		 $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);
		//var_dump($authoriseBusinessList);exit;

		 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');
		
		 $customer_id =get2('customer_id');
		 if(!$customer_id) {
			  $customer_id =$this->loginUser['id'];

			}
		 $isAuthoriseCustomer =0 ;
		 foreach ($authoriseBusinessList as $key => $value) {
				if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id']) {
						$isAuthoriseCustomer =1;
				}

			}
		

			if ($isAuthoriseCustomer) {

				$mdl_restaurant_category = $this->loadModel('restaurant_category');
				$countOfcat =$mdl_restaurant_category->getCountOfCategory($customer_id);
				//var_dump($countOfcat);exit;
				
				//如果点击一个主分类，则查找该主分类下面的子分类是否存在
				if ($cat_id){
					//如果选择大分类，则检查该账户下面该大分类是否已经创建了小分类
					$sqlexist =" select count(*) as count  from  cc_restaurant_category where createUserId =$customer_id and parent_category_id = $cat_id and parent_category_id !=0 ";
					$sub_category_list = $mdl_restaurant_category->getListBySql($sqlexist);
					$countOfsubcat = $sub_category_list[0]['count'];
					 
					if(  $countOfsubcat==0) { //如果子分类下面没有数据
					   
						$newAddRecordCount = 20; //设定增加数量为50
						
					}else { //子分类下面有数据 获得未使用的子分类条数如果小于10条，则再增加10条
						$sqlexist =" select count(*) as count  from  cc_restaurant_category where createUserId =$customer_id and parent_category_id = $cat_id and parent_category_id !=0 &&  (length(category_cn_name)=0 && length(category_en_name)=0) ";
						$sub_category_list = $mdl_restaurant_category->getListBySql($sqlexist);
						if( $sub_category_list[0]['count'] <=10)
							{ //如果子分类下面可用分类不足10条，则增加10条子分类
							$newAddRecordCount = 10;
							}else{
								
								$newAddRecordCount = 0;
							}
					}
						//var_dump($newAddRecordCount);exit;
								//增加该大类下的子分类

								$category_id   =100*(1+$countOfcat);  
								$category_sort_id=10*(1+$countOfcat);

								for($i=0;$i<$newAddRecordCount;$i++) {
									$menu_category_info=array(
										'category_cn_name'=>'',
										'category_en_name'=>'',
										'restaurant_id'=>$customer_id,
										'category_id'=>$category_id,
										'category_sort_id'=>$category_sort_id,
										'createUserId'=>$customer_id,
										'parent_category_id'=>$cat_id
									);
									//var_dump($menu_category_info);exit;
									$mdl_restaurant_category->insert($menu_category_info);
									$category_id =$category_id+100;
									$category_sort_id =$category_sort_id +10;
								}
							
					
					$where1 = ' and parent_category_id ='.$cat_id;
					$pageSql = "select  * from cc_restaurant_category where restaurant_id=$customer_id  $where1 and isdeleted =0  order by  parent_category_id,category_cn_name desc,category_sort_id ";
					
					
					//var_dump($pageSql);exit;
					$pageUrl = $this->parseUrl()->set('page');
					$pageSize =50;
					$maxPage = 100;
					$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
					$data = $mdl_restaurant_category->getListBySql($page['outSql']);

					$this->setData($data, 'data');
					
					$sql_Parent_cate_list ="select  * from cc_restaurant_category where restaurant_id=".$customer_id. "  and (length(category_cn_name)>0 or length(category_en_name)>0) and ( parent_category_id =0 or  parent_category_id is null) and isdeleted =0  order by ishide,category_sort_id,category_cn_name ";
					$data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
					$this->setData($data_parent_cate_list, 'data_parent_cate_list');
					//var_dump($sql_Parent_cate_list);exit;
					$this->setData($page['pageStr'], 'pager');
					$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

						
				}else {
					//如果不选择大分类，则只显示当前已经做好的所有二级分类，分类可以按照主分类序号，子分类序号进行排列显示。不进行增加处理
					
					$sqlexist =" select * from  cc_restaurant_category where createUserId =$customer_id  and parent_category_id !=0  and isdeleted= 0  and (length(category_cn_name)>0 or length(category_en_name)>0)";
					$sub_category_list = $mdl_restaurant_category->getListBySql($sqlexist);
					
						
				

				
				$pageSql = "select  * from cc_restaurant_category where restaurant_id=$customer_id  and parent_category_id !=0  and (length(category_cn_name)>0 or length(category_en_name)>0) order by  ishide,category_sort_id,category_cn_name  ";
				
				
				//var_dump($pageSql);exit;
				$pageUrl = $this->parseUrl()->set('page');
				$pageSize =50;
				$maxPage = 100;
				$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
				$data = $mdl_restaurant_category->getListBySql($page['outSql']);

				$this->setData($data, 'data');
				
				$sql_Parent_cate_list ="select  * from cc_restaurant_category where restaurant_id=".$customer_id. "  and (length(category_cn_name)>0 or length(category_en_name)>0) and ( parent_category_id =0 or  parent_category_id is null) order by ishide,category_sort_id,category_cn_name ";
				$data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
				$this->setData($data_parent_cate_list, 'data_parent_cate_list');
				//var_dump($sql_Parent_cate_list);exit;
				$this->setData($page['pageStr'], 'pager');
				$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');
				}

			}else {
				//do nothing  attack

			}


         


			$this->setData('restaurant_edit', 'submenu_top');
			$this->setData('restaurant_edit', 'submenu');
			$this->setData('index_publish', 'menu');

		    $pagename = "店铺二级品类管理";
			$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];


			$this->setData($pagename, 'pagename');
			$this->setData($pageTitle, 'pageTitle');

			$this->setData($this->loginUser['gst_type'], 'gstType');
			$this->display_pc_mobile('restaurant/edit', 'restaurant/edit');

	}





	/**
	 * 菜单分类编辑页面
	 */

	function restaurant_refresh_preview_management_action(){



		//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

		 $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);





		if($authoriseBusinessList) { //如果该商家可以托管账户
			// 检查接收的托管的商家是否合法

		     $mdl_user =$this->loadModel('user');
		     foreach ($authoriseBusinessList as $key => $value) {
				$curruser =$mdl_user->get($value['customer_id']);
				 $authoriseBusinessList[$key]['store_update_time']=$curruser['store_update_time'];
				 $authoriseBusinessList[$key]['store_fresh_time']=$curruser['store_fresh_time'];
				 if( $curruser['store_fresh_time'] && $curruser['store_update_time'] && $curruser['store_fresh_time']>=$curruser['store_update_time'] ) {
					 //该店铺有过更改，有过刷新 ，且刷新时间大于更改时间表示店铺已经刷新
					  $authoriseBusinessList[$key]['status'] =1;
					   $authoriseBusinessList[$key]['status_name'] ='已刷新';
				 }else {
					  $authoriseBusinessList[$key]['status'] ='0';
					   $authoriseBusinessList[$key]['status_name'] ='未刷新';
				 }


			 }
			 $this->setData($authoriseBusinessList, 'authoriseBusinessList');
			 $this->setData('preview_refresh', 'submenu');
			$this->setData('index_publish', 'menu');

			$pagename = "预览及上线管理";
			$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];


			$this->setData($pagename, 'pagename');
			$this->setData($pageTitle, 'pageTitle');
			$this->display_pc_mobile('restaurant/restaurant_refresh_preview_management', 'restaurant/restaurant_refresh_preview_management');




		}else{ //直接按照之前的方式走

				$this->setData('preview_refresh', 'submenu');
				$this->setData('index_publish', 'menu');

				$pagename = "预览及上线管理";
				$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];


				$this->setData($pagename, 'pagename');
				$this->setData($pageTitle, 'pageTitle');
				//$this->display_pc_mobile('/restaurant/'.$this->loginUser['id']);
			    $this->sheader(HTTP_ROOT_WWW.'restaurant/'.$this->loginUser['id']);

		}








	}




	/**
	 * 菜单编辑页面
	 */
	public function restaurant_menu_add_action(){
		// 获得该用户餐厅的菜单分类信息

		$freshfood =get2('freshfood');
		$addCount =get2('addCount');
//var_dump($addCount);exit;
		if ($freshfood) {
			$this->setData($freshfood,'freshfood');
		}else{
			$freshfood = post('freshfood');
			$this->setData($freshfood,'freshfood');
			//var_dump($freshfood);exit;

		}

		$customer_id =get2('customer_id');

		if(!$customer_id) {
		  $customer_id =$this->loginUser['id'];

		}
		$this->setData($customer_id,'customer_id');

	     $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

		 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

		if($authoriseBusinessList) { //如果该商家可以托管账户
			// 检查接收的托管的商家是否合法



			$isAuthoriseCustomer =0 ;
			foreach ($authoriseBusinessList as $key => $value) {
				if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id']) {
						$isAuthoriseCustomer =1;
				}

			}

			if($isAuthoriseCustomer) { //如果是授权的customer
			
			
				$mdl_restaurant_category = $this->loadModel('restaurant_category');
				$pageSql = "select  * from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0) and ( parent_category_id =0 or  parent_category_id is null) and isdeleted =0  order by isHide, category_sort_id ";
				$data = $mdl_restaurant_category->getListBySql($pageSql);

		
				if(!$data) {
					//$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
				}
				$this->setData($data,'restaurant_category');
				
				
				
				$sql_Parent_cate_list ="select *,  if(`parent_category_id`,concat('---',category_cn_name),category_cn_name) as category_cn_name1 ,if(`parent_category_id`,concat(category_cn_name),category_cn_name) as   category_cn_name2 ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id  from cc_restaurant_category where restaurant_id=$customer_id and (length(category_cn_name)>0 or length(category_en_name)>0) and isdeleted =0  order by isHide,parent_id,category_sort_id ";
				
				$data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
				//var_dump($sql_Parent_cate_list);exit;

				$this->setData($data_parent_cate_list, 'data_parent_cate_list');


				

			


				$sub_category =trim(get2('sub_category'));
			    $this->setData($sub_category,'sub_category');
				$category = trim(get2('category'));
				
                if(!$category) {$category='all';}
			
				$this->setData($sk,'sk');
				$this->setData($category,'category1');
				
			


				$sql = "select   m.category_id,m.restaurant_menu_id ,o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id left join cc_restaurant_menu_category m on o.id = m.restaurant_menu_id";

				$whereStr.=" o.restaurant_id = $customer_id ";
			//	var_dump($customer_id. ' ' . $category. ' ' . $sub_category);exit;
			$whereStr.=" and (length(o.menu_cn_name) =0 and length(o.menu_en_name) =0) ";
				if($category =='all' or empty($category)) {
						
				}else{
					
					if($sub_category) {
						$whereStr.= " and ( m.category_id= $sub_category) ";
					}else{
						$whereStr.= " and (o.restaurant_category_id='$category'  or m.category_id= $category ) ";
					}
					
				//var_dump($customer_id. ' ' . $category. ' ' . $sub_category);exit;
				$this->addNewEmptyMenuUponCategory($customer_id,$category,$sub_category,$addCount);
					
				}
				
				
				// 这块加一个通用程序，判断，如果当前指定的商家，在没有所搜具体产品时，根据其选择的大类，中类，如果当前中文名和英文名称都为空的数据小于10的时候，自动为该大类或大类中类，添加10条空记录，并标记相关的类别，
				//如果是中类，只记录中类，大类会自动找到（这块检查一下之前的程序）。
				
				
				
			
				
	
				if (!empty($sk)) {
					$whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.Menu_desc  like  '%" . $sk . "%')";
				}

				if($allOrspecial =='special') {
						$whereStr.=" and onSpecial =1";
						$this->setData($allOrspecial,'allOrspecial');
					}else{
					$this->setData($allOrspecial,'all');
				}

				// 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
				// 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

				$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
				$pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
				//var_dump($pageSql);exit;
				$pageUrl = $this->parseUrl()->set('page');
				$pageSize =50;
				$maxPage = 100;
				$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
				$data = $mdl_restaurant_menu->getListBySql($page['outSql']);


				// 获得该用户的gst type

                $mdl_user =$this->loadModel("user");
                $customerInfo = $mdl_user->get($customer_id);

				//var_dump($customerInfo);exit;
				
		
				
				

			}else{  //如果可以管理更多店铺

				


			}


    	}//结束主处理


			//获取该商家是否有多个供应商，是否为集合店

				$this->loadModel('freshfood_disp_suppliers_schedule');
				$suppliersList = DispCenter::getSupplierListWithName($customer_id);
				//var_dump($suppliersList);exit;
				if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$customer_id ) {  //如果该配货中心下只有一个商家


				}

				$this->setData($suppliersList, 'suppliersList');
				$this->setData($page['pageStr'], 'pager');
				$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

				/**
				 * 获得配菜分类列表
				 */
				$where=array();
				$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
				$where['restaurant_id']=$customer_id;
				$restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
				$this->setData($restaurant_sidedish_category_list,'sidedish_category_list');
				/**
				 * 获得配菜分类列表
				 */
				$where=array();
				$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
				$where['restaurant_id']=$customer_id;
				$restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
				$this->setData($restaurant_menu_option_list,'menu_option_list');

		foreach ($data as $key => $menu) {
			$categoryIds = $this->loadModel('restaurant_menu_category')->findCategoryIdsByMenuId($menu['id']);
			$data[$key]['categoryIds'] = $categoryIds;
		}
		$this->setData($data, 'data');

        $this->setData('restaurant_menu', 'submenu_top');

        $this->setData('restaurant_menu_add', 'submenu');
        $this->setData('index_publish', 'menu');

        $pagename = "店铺单品管理";
        $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];

        $this->setData($pagename, 'pagename');

        $this->setData($pageTitle, 'pageTitle');

		$this->setData($this->loginUser['gst_type'], 'gstType');
        $this->display_pc_mobile('restaurant/menu_add', 'restaurant/menu_add');
    }



	/**
	 * 菜单编辑页面
	 */
	function restaurant_menu_edit_action(){
		// 获得该用户餐厅的菜单分类信息

		$freshfood =get2('freshfood');

		if ($freshfood) {
			$this->setData($freshfood,'freshfood');
		}else{
			$freshfood = post('freshfood');
			$this->setData($freshfood,'freshfood');
			//var_dump($freshfood);exit;

		}

		$customer_id =get2('customer_id');

		if(!$customer_id) {
		  $customer_id =$this->loginUser['id'];

		}
		$this->setData($customer_id,'customer_id');

	     $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

		 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

		if($authoriseBusinessList) { //如果该商家可以托管账户
			// 检查接收的托管的商家是否合法



			$isAuthoriseCustomer =0 ;
			foreach ($authoriseBusinessList as $key => $value) {
				if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id']) {
						$isAuthoriseCustomer =1;
				}

			}

			if($isAuthoriseCustomer) { //如果是授权的customer
			
			
				$mdl_restaurant_category = $this->loadModel('restaurant_category');
				$pageSql = "select  * from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0) and ( parent_category_id =0 or  parent_category_id is null) and isdeleted =0  order by isHide,category_sort_id ";
				$data = $mdl_restaurant_category->getListBySql($pageSql);

		
				if(!$data) {
					//$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
				}
				$this->setData($data,'restaurant_category');
				
				
				
				$sql_Parent_cate_list ="select *,  if(`parent_category_id`,concat('---',category_cn_name),category_cn_name) as category_cn_name1 ,if(`parent_category_id`,concat(category_cn_name),category_cn_name) as   category_cn_name2 ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id  from cc_restaurant_category where restaurant_id=$customer_id and (length(category_cn_name)>0 or length(category_en_name)>0) and isdeleted =0  order by isHide, parent_id,category_sort_id ";
				
				$data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
				//var_dump($sql_Parent_cate_list);exit;



				//$ParentCategoryList = $mdl_restaurant_category->getParentCateList($customer_id);
				$data_parent_cate_list = $mdl_restaurant_category->getCateList($customer_id);

				//var_dump($subCategoryList);exit;

				$this->setData($data_parent_cate_list, 'data_parent_cate_list');
				$sk = trim(get2('sk'));

				$allOrspecial = trim(get2('allOrspecial'));

				$onoffguigecatinfo = trim(get2('onoffguigecatinfo'));
				$this->setData($onoffguigecatinfo,'onoffguigecatinfo');

				$onoffcninfo = trim(get2('onoffcninfo'));
				$this->setData($onoffcninfo,'onoffcninfo');
			//	var_dump($onoffcninfo);exit;

				$sub_category =trim(get2('sub_category'));
			    $this->setData($sub_category,'sub_category');
				$category = trim(get2('category'));
				
                if(!$category) {$category='all';}
			//		var_dump($sub_category);exit;
				$this->setData($sk,'sk');
				$this->setData($category,'category1');


				$sql = "select   m.category_id,m.restaurant_menu_id ,o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id left join cc_restaurant_menu_category m on o.id = m.restaurant_menu_id";

				$whereStr.=" o.restaurant_id = $customer_id and o.isDeleted =0  ";
				
				if($category =='all' or empty($category)) {
						$whereStr.=" and (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) ";
				}else{
					
					if($sub_category) {
						$whereStr.= " and ( m.category_id= $sub_category) ";
					}else{
						$whereStr.= " and (o.restaurant_category_id='$category'  or m.category_id= $category ) ";
					}
					
				}
				
				
	
				if (!empty($sk)) {
					$whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.Menu_desc  like  '%" . $sk .  "%'";
					$whereStr.=" or o.menu_id  like  '%" . $sk . "%'";
					$whereStr.=" or o.barcode_number  like  '%" . $sk .  "%'";
					$whereStr.=" or o.id  like  '%" . $sk . "%')";
				}

				if($allOrspecial =='special') {
						$whereStr.=" and onSpecial =1";
						$this->setData($allOrspecial,'allOrspecial');
					}else{
					$this->setData('all','allOrspecial');
				}

				// 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
				// 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

				$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
				$pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
				//var_dump($pageSql);exit;
				$pageUrl = $this->parseUrl()->set('page');
				$pageSize =30;
				$maxPage =200;
				$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
				$data = $mdl_restaurant_menu->getListBySql($page['outSql']);

				
					$key = 'id';
				 
				$data=$this->assoc_unique($data, $key);
//var_dump($data);exit;

				// 获得该用户的gst type

                $mdl_user =$this->loadModel("user");
                $customerInfo = $mdl_user->get($customer_id);

				//var_dump($customerInfo);exit;



				

			}else{  //如果可以管理更多店铺

				


			}


    	}//结束主处理


			//获取该商家是否有多个供应商，是否为集合店

				$this->loadModel('freshfood_disp_suppliers_schedule');
				$suppliersList = DispCenter::getSupplierListWithName($customer_id);
				//var_dump($suppliersList);exit;
				if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$customer_id ) {  //如果该配货中心下只有一个商家


				}

				$this->setData($suppliersList, 'suppliersList');
				$this->setData($page['pageStr'], 'pager');
				$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

		

		
				 // 获得配菜分类列表
				 
				$where=array();
				$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
				$where['restaurant_id']=$customer_id;
				$restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
				$this->setData($restaurant_sidedish_category_list,'sidedish_category_list');
				
				//  获得配菜分类列表
				
				$where=array();
				$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
				$where['restaurant_id']=$customer_id;
				$restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
				$this->setData($restaurant_menu_option_list,'menu_option_list');
				
			

		foreach ($data as $key => $menu) {
			$categoryIds = $this->loadModel('restaurant_menu_category')->findCategoryIdsByMenuId($menu['id']);
			$data[$key]['categoryIds'] = $categoryIds;
		}
		$this->setData($data, 'data');

        $this->setData('restaurant_menu', 'submenu_top');

        $this->setData('restaurant_menu_edit', 'submenu');
        $this->setData('index_publish', 'menu');

        $pagename = "店铺单品管理";
        $pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];

        $this->setData($pagename, 'pagename');

        $this->setData($pageTitle, 'pageTitle');

		$this->setData($this->loginUser['gst_type'], 'gstType');
        $this->display_pc_mobile('restaurant/menu_edit', 'restaurant/menu_edit');
    }




	function customer_product_discount_edit_action(){
		// 获得该用户餐厅的菜单分类信息

		$user_id =get2('user_id');

		$this->setData($user_id,'user_id');
		$this->setData($user_id,'customer_id');
		if(!$user_id) {

			var_dump('please choose the customer !'); exit;
		}



    			 $customer_id =$this->current_business['id'];




				$mdl_restaurant_category = $this->loadModel('restaurant_category');
				$pageSql = "select  * from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0) and ( parent_category_id =0 or  parent_category_id is null) and isdeleted =0  order by isHide,category_sort_id ";
				$data = $mdl_restaurant_category->getListBySql($pageSql);


				if(!$data) {
					//$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
				}
				$this->setData($data,'restaurant_category');



				$sql_Parent_cate_list ="select *,  if(`parent_category_id`,concat('---',category_cn_name),category_cn_name) as category_cn_name1 ,if(`parent_category_id`,concat(category_cn_name),category_cn_name) as   category_cn_name2 ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id  from cc_restaurant_category where restaurant_id=$customer_id and (length(category_cn_name)>0 or length(category_en_name)>0) and isdeleted =0  order by isHide, parent_id,category_sort_id ";

				$data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
				//var_dump($sql_Parent_cate_list);exit;

				$this->setData($data_parent_cate_list, 'data_parent_cate_list');


				$sk = trim(get2('sk'));

				$allOrspecial = trim(get2('allOrspecial'));

				$onoffguigecatinfo = trim(get2('onoffguigecatinfo'));
				$this->setData($onoffguigecatinfo,'onoffguigecatinfo');

				$onoffcninfo = trim(get2('onoffcninfo'));
				$this->setData($onoffcninfo,'onoffcninfo');
				//	var_dump($onoffcninfo);exit;

				$sub_category =trim(get2('sub_category'));
				$this->setData($sub_category,'sub_category');
				$category = trim(get2('category'));

				if(!$category) {$category='all';}
				//		var_dump($sub_category);exit;
				$this->setData($sk,'sk');
				$this->setData($category,'category1');


				$sql = "select   cust.menu_discount_rate as discount_rate ,cust.price as customer_price ,m.category_id,m.restaurant_menu_id ,o.* ,b.category_cn_name,b.category_en_name  
						from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id 
							left join cc_restaurant_menu_category m on o.id = m.restaurant_menu_id
							left join cc_user_factory_menu_price cust on o.id=cust.restaurant_menu_id and cust.user_id =$user_id
							
							";

				$whereStr.=" o.restaurant_id = $customer_id and o.isDeleted =0 and ( length(o.menu_cn_name ) > 0 or length( o.menu_en_name ) >0 )  ";

				if($category =='all' or empty($category)) {
				//	$whereStr.=" and (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) ";
				}else{

					if($sub_category) {
						$whereStr.= " and ( m.category_id= $sub_category) ";
					}else{
						$whereStr.= " and (o.restaurant_category_id='$category'  or m.category_id= $category ) ";
					}

				}

				if (!empty($sk)) {
					$whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.Menu_desc  like  '%" . $sk .  "%'";
					$whereStr.=" or o.menu_id  like  '%" . $sk . "%'";
					$whereStr.=" or o.barcode_number  like  '%" . $sk .  "%'";
					$whereStr.=" or o.id  like  '%" . $sk . "%')";
				}



				// 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
				// 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

				$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
				$pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
				//var_dump($pageSql);exit;
				$pageUrl = $this->parseUrl()->set('page');
				$pageSize =30;
				$maxPage =200;
				$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
				$data = $mdl_restaurant_menu->getListBySql($page['outSql']);


				$key = 'id';

				$data=$this->assoc_unique($data, $key);


				// 获得该用户的gst type

				$mdl_user =$this->loadModel("user");
				$customerInfo = $mdl_user->get($customer_id);



		//  获得配菜分类列表

		$where=array();
		$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
		$where['restaurant_id']=$customer_id;
		$restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
		$this->setData($restaurant_menu_option_list,'menu_option_list');

		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->setPath('restaurant/customer_product_discount_edit'), 'editUrl');



		$this->setData($data, 'data');

		$this->setData('customer_product_discount_edit', 'submenu_top');

		$this->setData('customer_price_management', 'submenu');
		$this->setData('customer_management', 'menu');

		$pagename = "单品折扣管理";
		$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];

		$this->setData($pagename, 'pagename');

		$this->setData($pageTitle, 'pageTitle');


		$this->display('restaurant/customer_product_discount_edit');
	}




	function menu_recycle_action(){
		// 获得该用户餐厅的菜单分类信息

		$freshfood =get2('freshfood');

		if ($freshfood) {
			$this->setData($freshfood,'freshfood');
		}else{
			$freshfood = post('freshfood');
			$this->setData($freshfood,'freshfood');
			//var_dump($freshfood);exit;

		}

		$customer_id =get2('customer_id');

		if(!$customer_id) {
			$customer_id =$this->loginUser['id'];

		}
		$this->setData($customer_id,'customer_id');

		$mdl = $this->loadModel('authrise_manage_other_business_account');
		$authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

		$this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

		if($authoriseBusinessList) { //如果该商家可以托管账户
			// 检查接收的托管的商家是否合法



			$isAuthoriseCustomer =0 ;
			foreach ($authoriseBusinessList as $key => $value) {
				if($customer_id ==$value['customer_id'] || $customer_id ==$this->loginUser['id']) {
					$isAuthoriseCustomer =1;
				}

			}

			if($isAuthoriseCustomer) { //如果是授权的customer


				$mdl_restaurant_category = $this->loadModel('restaurant_category');
				$pageSql = "select  * from cc_restaurant_category where createUserId=$customer_id  and (length(category_cn_name)>0 or length(category_en_name)>0) and ( parent_category_id =0 or  parent_category_id is null) and isdeleted =0  order by isHide,category_sort_id ";
				$data = $mdl_restaurant_category->getListBySql($pageSql);


				if(!$data) {
					//$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
				}
				$this->setData($data,'restaurant_category');



				$sql_Parent_cate_list ="select *,  if(`parent_category_id`,concat('---',category_cn_name),category_cn_name) as category_cn_name1 ,if(`parent_category_id`,concat(category_cn_name),category_cn_name) as   category_cn_name2 ,if(`parent_category_id`,concat(`parent_category_id`,id),concat(id,0)) as parent_id  from cc_restaurant_category where restaurant_id=$customer_id and (length(category_cn_name)>0 or length(category_en_name)>0) and isdeleted =0  order by isHide, parent_id,category_sort_id ";

				$data_parent_cate_list  = $mdl_restaurant_category->getListBySql($sql_Parent_cate_list);
				//var_dump($sql_Parent_cate_list);exit;

				$this->setData($data_parent_cate_list, 'data_parent_cate_list');


				$sk = trim(get2('sk'));

				$allOrspecial = trim(get2('allOrspecial'));


				$sub_category =trim(get2('sub_category'));
				$this->setData($sub_category,'sub_category');
				$category = trim(get2('category'));

				if(!$category) {$category='all';}
				//		var_dump($sub_category);exit;
				$this->setData($sk,'sk');
				$this->setData($category,'category1');


				$sql = "select   m.category_id,m.restaurant_menu_id ,o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu o left join cc_restaurant_category b on b.id=o.restaurant_category_id left join cc_restaurant_menu_category m on o.id = m.restaurant_menu_id";

				$whereStr.=" o.restaurant_id = $customer_id ";

				if($category =='all' or empty($category)) {
					$whereStr.=" and o.isDeleted =1 and (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) ";
				}else{

					if($sub_category) {
						$whereStr.= " and ( m.category_id= $sub_category) ";
					}else{
						$whereStr.= " and (o.restaurant_category_id='$category'  or m.category_id= $category ) ";
					}

				}



				if (!empty($sk)) {
					$whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
					$whereStr.=" or o.Menu_desc  like  '%" . $sk .  "%'";
					$whereStr.=" or o.menu_id  like  '%" . $sk . "%'";
					$whereStr.=" or o.barcode_number  like  '%" . $sk .  "%'";
					$whereStr.=" or o.id  like  '%" . $sk . "%')";
				}

				if($allOrspecial =='special') {
					$whereStr.=" and onSpecial =1";
					$this->setData($allOrspecial,'allOrspecial');
				}else{
					$this->setData($allOrspecial,'all');
				}

				// 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
				// 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

				$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
				$pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
				//var_dump($pageSql);exit;
				$pageUrl = $this->parseUrl()->set('page');
				$pageSize =50;
				$maxPage =500;
				$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
				$data = $mdl_restaurant_menu->getListBySql($page['outSql']);


				$key = 'id';

				$data=$this->assoc_unique($data, $key);
//var_dump($data);exit;

				// 获得该用户的gst type

				$mdl_user =$this->loadModel("user");
				$customerInfo = $mdl_user->get($customer_id);

				//var_dump($customerInfo);exit;





			}else{  //如果可以管理更多店铺




			}


		}//结束主处理


		//获取该商家是否有多个供应商，是否为集合店

		$this->loadModel('freshfood_disp_suppliers_schedule');
		$suppliersList = DispCenter::getSupplierListWithName($customer_id);
		//var_dump($suppliersList);exit;
		if( count($suppliersList) ==1 && $suppliersList[0]['suppliers_id']!=$customer_id ) {  //如果该配货中心下只有一个商家


		}

		$this->setData($suppliersList, 'suppliersList');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');




		// 获得配菜分类列表

		$where=array();
		$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
		$where['restaurant_id']=$customer_id;
		$restaurant_sidedish_category_list=$this->loadModel('restaurant_sidedish_category')->getList(null,$where);
		$this->setData($restaurant_sidedish_category_list,'sidedish_category_list');

		//  获得配菜分类列表

		$where=array();
		$where[]="(length(category_cn_name) >0 or length(category_en_name) >0)";
		$where['restaurant_id']=$customer_id;
		$restaurant_menu_option_list=$this->loadModel('restaurant_menu_option_category')->getList(null,$where);
		$this->setData($restaurant_menu_option_list,'menu_option_list');



		foreach ($data as $key => $menu) {
			$categoryIds = $this->loadModel('restaurant_menu_category')->findCategoryIdsByMenuId($menu['id']);
			$data[$key]['categoryIds'] = $categoryIds;
		}
		$this->setData($data, 'data');



		$this->setData('restaurant_menu_edit', 'submenu');
		$this->setData('index_publish', 'menu');

		$pagename = "Items Recycle Management";
		$pageTitle=  $pagename." - Business Centre - ". $this->site['pageTitle'];

		$this->setData($pagename, 'pagename');

		$this->setData($pageTitle, 'pageTitle');

		$this->setData($this->loginUser['gst_type'], 'gstType');
		$this->display_pc_mobile('restaurant/menu_recycle', 'restaurant/menu_recycle');
	}
		function assoc_unique($arr, $key) {
		 
		$tmp_arr = array();
		 
		foreach ($arr as $k => $v) {
		 
		if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
		 
		unset($arr[$k]);
		 
		} else {
		 
		$tmp_arr[] = $v[$key];
		 
		}
		
		}
		 return $arr;
		}
	function menu_publish_ajax_recycle_bin_action()
	{
		$id = (int)get2('id');



		$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

		$menu = $mdl_restaurant_menu->get($id);

		if ($id < 0 || !$menu ) $this->form_response_msg('menu id invalid');

		//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

		$mdl = $this->loadModel('authrise_manage_other_business_account');
		$authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

		$this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');


		$isAuthoriseCustomer =0 ;
		foreach ($authoriseBusinessList as $key => $value) {
			if($menu['createUserId'] ==$value['customer_id']) {
				$isAuthoriseCustomer =1;
			}

		}




		if ($menu['createUserId'] != $this->loginUser['id'] && !$isAuthoriseCustomer  ) $this->form_response_msg('you are not allow to update!');

		$data = array();
		$data['isDeleted'] = ($menu['isDeleted'] == '0') ? '1' : '0';

		if($menu['isDeleted'] == '0') {
			$data['visible']=0;
		}

		if ($mdl_restaurant_menu->update($data, $menu['id'])) {
			echo json_encode(array('isDeleted' => $data['isDeleted']));
		} else {
			$this->form_response_msg('Please try again later');
		}


	}





    function menu_publish_ajax_action()
    {
    	$id = (int)get2('id');



    	$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

    	$menu = $mdl_restaurant_menu->get($id);

		if ($id < 0 || !$menu ) $this->form_response_msg('menu id invalid');

	//检查该商家是否可以管理其它店铺，如果授权即可以该商家权限进入系统。

		 $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

		 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');


		$isAuthoriseCustomer =0 ;
		foreach ($authoriseBusinessList as $key => $value) {
				if($menu['createUserId'] ==$value['customer_id']) {
						$isAuthoriseCustomer =1;
				}

			}




    	if ($menu['createUserId'] != $this->loginUser['id'] && !$isAuthoriseCustomer  ) $this->form_response_msg('you are not allow to update!');

    	$data = array();
    	$data['visible'] = ($menu['visible'] == '0') ? '1' : '0';

    	if ($mdl_restaurant_menu->update($data, $menu['id'])) {
    		echo json_encode(array('visible' => $data['visible']));
    	} else {
    		$this->form_response_msg('Please try again later');
    	}


    }



	 function update_sell_price_action()
    {
    	$userrange = get2('userrange');
		$pricerates = get2('pricerates');
		$productrange = get2('productrange');




    	$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

    	if($this->loginUser['id']  != 217005 && $this->loginUser['id']  != 218639 ) {

			$this->form_response_msg('Please try again later');

		}




		if($userrange  =='all') {
			$whereUserrange ='(restaurant_id =217005 or restaurant_id=218639)';

		}elseif ($userrange  =='217005') {
			$whereUserrange =' restaurant_id =217005 ';

		}elseif($userrange  =='218639'){
			$whereUserrange =' restaurant_id=218639 ';

		}



		if($productrange ==1) {
			$whereproductrange ='';

		}elseif ($productrange ==2) {
			$whereproductrange =' and Onspecial =0 ';

		}


        $sql = "update cc_restaurant_menu set price = round(freshx_price * $pricerates +0.1,1) where $whereUserrange $whereproductrange and   freshx_price>0 and visible =1  ";
       // var_dump ($sql);exit;




    	if ($mdl_restaurant_menu->getListBySql($sql) ){
    		echo json_encode(array('message' => 1));
    	} else {
			echo json_encode(array('message' => 0));

    	}
      //  echo json_encode($sql);

    }




 function process_link_picture_action()
    {
    	   $id = (int)get2('id');
           $fileName = get2('filePath');
		   $changeMainpic = get2('changeMainpic');
          
		  
		
			//$type     = $this->getImagetype( $type );
			
			if (!strstr($fileName, '.'))
			{
			$this->form_response(500,'不是图片文件');
			}
		  
		  	  
           $mdl_restaurant_menu = $this->loadModel('restaurant_menu');

    	   $menu = $mdl_restaurant_menu->get($id);

		   if ($id < 0 || $menu['restaurant_id'] != $this->loginUser['id'] ) $this->form_response(500,'menu id invalid');

           if(strlen($fileName)<=0) exit;

           $count_of_pics =$this->count_of_menu_pics($menu);
		   if($count_of_pics >=10) $this->form_response(500,'the count of pictures over 10! please remove and add it again');
		  // 开始按照流程图做即可。
		  //已经获得了是否更换主图的变量。
		  //按照流程图走就可以。 关键是要把 几个地方 通用化。 
		  //根据给出的barcode , 的处理流程，根据给出的图片的 处理流程。
		  
		  

		  
		   $uploadPath = date("Y-m");
		   $this->file->createdir('data/upload/'.$uploadPath);
           $pic_arr= $this->gen_image_file_from_barcode_web($fileName,$uploadPath);
		  
		   $sucess_save_pic =0;

           if($changeMainpic){
			  //更换主图
              //将之前的主图片放到附图的第一个位置
			  if(strlen(trim($menu['menu_pics']))<=8) {
				  $menu_pic_arr=array();
			  }else{
				   $menu_pic_arr = unserialize($menu['menu_pics']);
			  }
			 
			   // $this->form_response(500, $menu_pic_arr);
			  array_push($menu_pic_arr,$menu['menu_pic']);
			  
			  $menu_pic_json = serialize($menu_pic_arr);
			 
			  $UpdateData =array (
			    'menu_pic'=>$pic_arr[0],
			    'menu_pics'=>$menu_pic_json
			  
			  );
			  
			  
			  if($mdl_restaurant_menu->update($UpdateData, $menu['id'])){
				  
				  
				  
				   //更新产品库图片信息
				   $this->updateStandProductUponMenuId($menu['id']);
				   
				   //生成前端替换显示的图片信息
				   $picPath =$this->image_file_insert_cut_info($pic_arr[0],'_66x66_fill');
				   $msg =$picPath;
				   $this->form_response(200,$msg,'');
				  
			  }
			  
			  // 更换主图
			//  $this->form_response_msg($id.' '.$menu_pic_json.' ' .$pic_arr[0]);
			   
				}else{
			   //添加至附图
			   
			    if(strlen(trim($menu['menu_pics']))<=8) {
				  $menu_pic_arr=array();
			  }else{
				   $menu_pic_arr = unserialize($menu['menu_pics']);
			  }
			   
			  
			  array_push($menu_pic_arr,$pic_arr[0]);
			  
			  $menu_pic_json = serialize($menu_pic_arr);
			   $UpdateData =array (
			     'menu_pics'=>$menu_pic_json
			  
			  );
			   $mdl_restaurant_menu->update($UpdateData, $menu['id']);
			     
				   //更新产品库图片信息
				   $this->updateStandProductUponMenuId($menu['id']);
		   }



  








		//   $this->form_response_msg($id.' '.$changeMainpic.' ' .$pic_arr[0]);
    	

    	

    }




	 function menu_publish_ajax1_action()
    {
    		$id = (int)get2('id');



    	$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

    	$menu = $mdl_restaurant_menu->get($id);

		if ($id < 0 || !$menu ) $this->form_response_msg('menu id invalid');

		 $mdl = $this->loadModel('authrise_manage_other_business_account');
         $authoriseBusinessList = Authorise_Center::getCustmerListsWithBusinessName($this->loginUser['id']);

		 $this->setData($authoriseBusinessList, 'authrise_manage_other_business_account');

		$isAuthoriseCustomer =0 ;
		foreach ($authoriseBusinessList as $key => $value) {
				if($menu['createUserId'] ==$value['customer_id']) {
						$isAuthoriseCustomer =1;
				}

			}




    	if ($menu['createUserId'] != $this->loginUser['id'] && !$isAuthoriseCustomer  ) $this->form_response_msg('you are not allow to update!');

    	$data = array();
    	$data['onSpecial'] = ($menu['onSpecial'] == '0') ? '1' : '0';
		$data['limit_buy_qty'] = 1;

    	if ($mdl_restaurant_menu->update($data, $menu['id'])) {
    		echo json_encode(array('onSpecial' => $data['onSpecial']));
    	} else {
    		$this->form_response_msg('Please try again later');
    	}


    }


	/**
	 * ajax update menu item
	 */

	function update_menu_item_action(){

		if(is_post()){
			$mdl_restaurant_menu =$this->loadModel("restaurant_menu");

			$id = post('id');

			$idCreateUser = $mdl_restaurant_menu->get($id);

			$mdl  = $this->loadModel('authrise_manage_other_business_account');
			$isAuthoriseCustomer =Authorise_Center::getIsCustomerIdIsAuthorised($this->loginUser['id'],$idCreateUser['restaurant_id']);

	   if($idCreateUser['restaurant_id'] != $this->loginUser['id']) {
			if(!$isAuthoriseCustomer) $this->form_response(600,$id.' '.$idCreateUser['restaurant_id'],'未发现产品');
	   }


			$data=array();

			$price = post('price');
			if($price)$data['price']=$price;


			$lowest_price = post('lowest_price');
			if($lowest_price)$data['lowest_price']=$lowest_price;

			
			$original_price = post('original_price');
			if($original_price)$data['original_price']=$original_price;

			$speical_price = post('speical_price');
			if($speical_price)$data['speical_price']=$speical_price;
			
			
			$barcode_number = post('barcode_number');
			
			 // 如果该编号已经改变 ，则进行相关处理，如果没有改变则不做处理
          // $this->form_response(500, $barcode_number,'');  
			
			if($barcode_number)
				
				{
				  $data['barcode_number']=$barcode_number;
           		   if( strlen($barcode_number)<=7) {
					   $this->form_response(500, 'barcode_number too short！','');   
				   }
				  					  
                      //如果当前产品没有图片，则开始处理，如果有图片不做操作
					  
					   if(!$idCreateUser['menu_pic']) {
						   
						    $mdl_standard_product_info= $this->loadModel('standard_product_info');
						
							//$this->form_response(500, $barcode_number,'');   
						  // 查找在产品库中是否存在，如果存在，则将图片路径新鲜，复制到该产品之下。 可能包含 主图和子图信息。
							
							$rec = $mdl_standard_product_info->ifFindBarcode($barcode_number);
							 if($rec) {
								 //将 图片信息填到该记录这里
								$picData = $this->generate_images_data($rec);
								if($picData) $mdl_restaurant_menu->update($picData,$idCreateUser['id']);
								
								
								$picPath =$this->image_file_insert_cut_info($rec['images1'],'_66x66_fill');
								//$this->form_response(500, $picPath,'');   
								$msg =$picPath;
							  }	
						   
						   
					   }else{
						   //如果该产品有图片，则检查在标准sku库中是否存在该产品，如果不存在，则增加到标准库中，并标记来源，如果已存在检查是否有图片，如果没有图片则更新图片，有图片不做处理。
						     $mdl_standard_product_info= $this->loadModel('standard_product_info');
							 $rec = $mdl_standard_product_info->ifFindBarcode($barcode_number);
							  if($rec) {
								  //如果存在产品记录
								  if($rec['images1']) {
									  //不做处理
									  
								  }else{
									  //没有图片，生成图片信息数据，及来源数据，更新到该产品标准表的相应信息上。
									  $updateData001 = array(
									     'images1'=>$idCreateUser['menu_pic'],
										 'imagesmore'=>$idCreateUser['menu_pics'],
										 'source_business_id'=>$idCreateUser['restaurant_id']
									  
									  );
									  $mdl_standard_product_info->update($updateData001,$rec['id']);
									  
								  }
								
							  }	else{
								  //不存在产品记录
								  
								  if(!$idCreateUser['menu_en_name']) {
									  $title =$idCreateUser['menu_cn_name'];
								  }else{
									  $title =$idCreateUser['menu_en_name'];
									  
								  }
								  
								  
								   if(!$idCreateUser['menu_cn_name']) {
									  $title_cn =$idCreateUser['menu_en_name'];
								  }else{
									  $title_cn =$idCreateUser['menu_cn_name'];
								  }
								  
								  
								  if(!$idCreateUser['menu_desc']) {
									  $description_cn =$idCreateUser['menu_en_desc'];
								  }else{
									  $description_cn =$idCreateUser['menu_desc'];
									  
								  }
								  
								  
								  if(!$idCreateUser['menu_en_desc']) {
									  $description =$idCreateUser['menu_desc'];
								  }else{
									  $description =$idCreateUser['menu_en_desc'];
									  
								  }
								  
								  
								  
								  $insertData001=array(
								  
								     'barcode_number'=>$barcode_number,
									 'title'=>$title,
									 'title_cn'=>$title_cn,
									 'category'=>$idCreateUser['restaurant_category_id'],
									 'description'=>$description,
									 'description_cn'=>$description_cn,
									 'images1'=>$idCreateUser['menu_pic'],
									 'imagesmore'=>$idCreateUser['menu_pics'],
									 'grapType'=>1,
									 'source_business_id'=>$idCreateUser['restaurant_id']
								  
									  
								  );
								  
								   $mdl_standard_product_info->insert($insertData001);
								  
							  }
						   
						   
						   
					   }
					   
					   
						
						
						
					
				}
		 
		   

	      	
			$freshx_price = post('freshx_price');
			if($freshx_price)$data['freshx_price']=$freshx_price;



			$menu_id = post('menu_id');
			if($menu_id)$data['menu_id']=$menu_id;
			
			
			//检查如果发现相同的menu_id 提示用户选择菜单分类
			if($menu_id) {
				$where1 =array (
					'restaurant_id'=>$idCreateUser['restaurant_id'],
					'menu_id'=>$menu_id
				);
				if($mdl_restaurant_menu->getCount($where1)>0) {
					
					$this->form_response(500, '编号重复！','');
				}
				
			}

			$menu_cn_name = post('menu_cn_name');
			if(isset($menu_cn_name))$data['menu_cn_name']=$menu_cn_name;

			$menu_en_name = post('menu_en_name');
			if(isset($menu_en_name))$data['menu_en_name']=$menu_en_name;

			$qty = post('qty');
			if(isset($qty))$data['qty']=$qty;

			$limit_buy_qty = post('limit_buy_qty');
			if(isset($limit_buy_qty))$data['limit_buy_qty']=$limit_buy_qty;

			$unit = post('unit');
			if(isset($unit))$data['unit']=$unit;

			$unit_en = post('unit_en');
			if(isset($unit_en))$data['unit_en']=$unit_en;

			$include_gst = post('include_gst');
			if(isset($include_gst))$data['include_gst']=$include_gst;

			$menu_desc = post('menu_desc');
			if(isset($menu_desc))$data['menu_desc']=$menu_desc;

			$menu_en_desc = post('menu_en_desc');
			if(isset($menu_en_desc))$data['menu_en_desc']=$menu_en_desc;

			$restaurant_category_id = post('restaurant_category_id');
			if(isset($restaurant_category_id))$data['restaurant_category_id']=$restaurant_category_id;

			$sidedish_category = post('sidedish_category');
			if(isset($sidedish_category))$data['sidedish_category']=$sidedish_category;

			$menu_option = post('menu_option');
			if(isset($menu_option))$data['menu_option']=$menu_option;

			$restaurant_id = post('restaurant_id');
			if(isset($restaurant_id))$data['restaurant_id']=$restaurant_id;

			$menu_order_id = post('menu_order_id');
			if(isset($menu_order_id))$data['menu_order_id']=$menu_order_id;

			try {
				$mdl_restaurant_menu->update($data,$id);

				$this->loadModel('user')->update(array('store_update_time' =>time()),$idCreateUser['restaurant_id']);


				$this->form_response(200,$msg,'');
			} catch (Exception $e) {
				$this->form_response(500, $e->getMessage(),'');
			}

		}else{
			//wrong protocol
		}
	}


	/**
	 *  Ajax update Category item
	 */

	public function update_category_item_action()
	{
		if(is_post()){

			$mdl_restaurant_category =$this->loadModel("restaurant_category");

			$id = post('id');


			$idCreateUser = $mdl_restaurant_category->get($id);
			$mdl  = $this->loadModel('authrise_manage_other_business_account');
			$isAuthoriseCustomer =Authorise_Center::getIsCustomerIdIsAuthorised($this->loginUser['id'],$idCreateUser['restaurant_id']);

	       if($idCreateUser['restaurant_id'] != $this->loginUser['id']) {
			   	if(!$isAuthoriseCustomer ) $this->form_response(600,'未发现产品','未发现产品');
		   }



			$data=array();

			$category_sort_id = post('category_sort_id');
			if($category_sort_id)$data['category_sort_id']=$category_sort_id;

			$category_en_name = post('category_en_name');
			if(isset($category_en_name))$data['category_en_name']=$category_en_name;

			$category_cn_name = post('category_cn_name');
			if(isset($category_cn_name))$data['category_cn_name']=$category_cn_name;

			$parent_category_id = post('parent_category_id');
			if(isset($parent_category_id))$data['parent_category_id']=$parent_category_id;
			
			$isHide = post('isHide');
			if(isset($isHide))$data['isHide']=$isHide;
			
			
			if($parent_category_id == 0) {
				$parent_category_id = null;
			}

			$hot = post('hot');
			if(isset($hot))$data['hot']=$hot;
			try {
				$mdl_restaurant_category->update($data,$id);
				$this->loadModel('user')->update(array('store_update_time' =>time()),$idCreateUser['restaurant_id']);

				$this->form_response(200,'','');
			} catch (Exception $e) {
				$this->form_response(500, $e->getMessage(),'');
			}

		}else{
			//wrong protocol
		}
	}

	/**
	 * 配菜分类编辑页面
	 */

	function restaurant_sidedish_edit_action(){
		$mdl_restaurant_sidedish_category = $this->loadModel('restaurant_sidedish_category');
		$exist = $mdl_restaurant_sidedish_category->getByWhere(array('createUserId'=>$this->loginUser['id']));


		if(!$exist){
			$category_id =100;
			$category_sort_id=10;

			for($i=0;$i<50;$i++) {
				$menu_category_info=array(
					'category_cn_name'=>'',
					'category_en_name'=>'',
					'restaurant_id'=>$this->loginUser['id'],
					'category_id'=>$category_id,
					'category_sort_id'=>$category_sort_id,
					'createUserId'=>$this->loginUser['id']
				);
				$mdl_restaurant_sidedish_category->insert($menu_category_info);
				$category_id =$category_id+100;
				$category_sort_id =$category_sort_id +10;
			}
		}

		$pageSql = "select  * from cc_restaurant_sidedish_category where createUserId=".$this->loginUser['id']. " order by category_sort_id ";
		$pageUrl = $this->parseUrl()->set('page');
		$pageSize =50;
		$maxPage = 10;
		$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data = $mdl_restaurant_sidedish_category->getListBySql($page['outSql']);

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

		$this->setData('restaurant_sidedish', 'submenu_top');
		$this->setData('restaurant_set', 'submenu');
		$this->setData('index_publish', 'menu');

		$pagename = "餐厅配菜类别管理";
		$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];


		$this->setData($pagename, 'pagename');
		$this->setData($pageTitle, 'pageTitle');

		$this->display_pc_mobile('restaurant/sidedish_edit', 'restaurant/sidedish_edit');
	}

	/**
	 * 配菜菜单编辑页面
	 */
	function restaurant_sidedish_menu_edit_action(){

		// 获得该用户餐厅的菜单分类信息

		$mdl_restaurant_sidedish_category = $this->loadModel('restaurant_sidedish_category');
		$pageSql = "select  * from cc_restaurant_sidedish_category where createUserId=".$this->loginUser['id']. " and (length(category_cn_name)>0 or length(category_en_name)>0) order by category_sort_id ";
		$data = $mdl_restaurant_sidedish_category->getListBySql($pageSql);

		if(!$data) {
			$this->sheader(null,'您需要首先定义餐厅的菜单分类,然后才可以定义菜品....');
		}
		$this->setData($data,'restaurant_sidedish_category');



		$sk = trim(get2('sk'));
		$category = trim(get2('category'));

		$this->setData($sk,'sk');
		$this->setData($category,'category');


		$sql = "select  o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_sidedish_menu o left join cc_restaurant_sidedish_category b on b.id=o.restaurant_category_id";

		if($category =='all' or empty($category)) {
			$whereStr.=" (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and o.restaurant_id= ".$this->loginUser['id'];
		}else{
			$whereStr.=" o.restaurant_id= ".$this->loginUser['id'];
		}


		if (!empty($category)) {
			if ($category != 'all') {
				$whereStr.= " and o.restaurant_category_id='$category' ";
			}
		}
		if (!empty($sk)) {
			$whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
			$whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
			$whereStr.=" or o.Menu_desc  like  '%" . $sk . "%')";
		}


	    // 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
		// 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

		$mdl_restaurant_sidedish_menu = $this->loadModel('restaurant_sidedish_menu');
		$pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
		$pageUrl = $this->parseUrl()->set('page');
		$pageSize =20;
		$maxPage = 10;
		$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data = $mdl_restaurant_sidedish_menu->getListBySql($page['outSql']);
		//var_dump($category);
		if(!$data){

			if($category !='all' && !empty($category)) {
				// 增加50个菜单分类
				$menu_id =100;


				for($i=0;$i<50;$i++) {
					$menu_info=array(
						'createUserId'=>$this->loginUser['id'],
						'restaurant_id'=>$this->loginUser['id'],
						'restaurant_category_id'=>$category,
						'menu_id'=>$category.$menu_id,
						'menu_cn_name'=>'',
						'price'=>'',
						'menu_pic'=>'',
						'Menu_desc'=>'',
						'menu_en_name'=>''
					);
					$mdl_restaurant_sidedish_menu->insert($menu_info);
					$menu_id =$menu_id+1;
				}

				$pageSql = "select  * from cc_restaurant_sidedish_menu where createUserId=".$this->loginUser['id']. " and restaurant_category_id =".$category." order by menu_id";
				$pageUrl = $this->parseUrl()->set('page');
				$pageSize =20;
				$maxPage = 10;
				$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
				$data = $mdl_restaurant_sidedish_menu->getListBySql($page['outSql']);
			}
		}

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

		$this->setData('restaurant_sidedish_menu', 'submenu_top');
		$this->setData('restaurant_set', 'submenu');
		$this->setData('index_publish', 'menu');

		$pagename = "店铺细项管理";
		$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];

		$this->setData($pagename, 'pagename');

		$this->setData($pageTitle, 'pageTitle');

		$this->display_pc_mobile('restaurant/sidedish_menu_edit', 'restaurant/sidedish_menu_edit');
	}


	/**
	 * ajax update menu item
	 */

	function update_sidedish_menu_item_action(){

		if(is_post()){
			$mdl_restaurant_sidedish_menu =$this->loadModel("restaurant_sidedish_menu");

			$id = post('id');

			$sql ="select id from cc_restaurant_sidedish_menu  where id=".$id. " and createUserId=".$this->loginUser['id'];

			if( !$mdl_restaurant_sidedish_menu->getListBySql($sql))$this->form_response(600,'未发现产品','未发现产品');


			$data=array();

			$price = post('price');
			if($price)$data['price']=$price;

			$menu_id = post('menu_id');
			if($menu_id)$data['menu_id']=$menu_id;

			$menu_cn_name = post('menu_cn_name');
			if(isset($menu_cn_name))$data['menu_cn_name']=$menu_cn_name;

			$menu_en_name = post('menu_en_name');
			if(isset($menu_en_name))$data['menu_en_name']=$menu_en_name;

			$menu_desc = post('menu_desc');
			if(isset($menu_desc))$data['menu_desc']=$menu_desc;


			try {
				$mdl_restaurant_sidedish_menu->update($data,$id);
				$this->form_response(200,'','');
			} catch (Exception $e) {
				$this->form_response(500, $e->getMessage(),'');
			}

		}else{
			//wrong protocol
		}
	}


	/**
	 *  Ajax update Category item
	 */

	public function update_sidedish_category_item_action()
	{
		if(is_post()){

			$mdl_restaurant_sidedish_category =$this->loadModel("restaurant_sidedish_category");

			$id = post('id');

			$sql ="select id from cc_restaurant_sidedish_category  where id=".$id. " and createUserId=".$this->loginUser['id'];


			if( !$mdl_restaurant_sidedish_category->getListBySql($sql)) $this->form_response(600,'未发现产品','未发现产品');


			$data=array();

			$category_sort_id = post('category_sort_id');
			if($category_sort_id)$data['category_sort_id']=$category_sort_id;

			$category_en_name = post('category_en_name');
			if(isset($category_en_name))$data['category_en_name']=$category_en_name;

			$category_cn_name = post('category_cn_name');
			if(isset($category_cn_name))$data['category_cn_name']=$category_cn_name;


			try {
				$mdl_restaurant_sidedish_category->update($data,$id);

				$this->form_response(200,'','');
			} catch (Exception $e) {
				$this->form_response(500, $e->getMessage(),'');
			}

		}else{
			//wrong protocol
		}
	}

	/**
	 * 餐品规格分类编辑页面
	 */

	function restaurant_menu_option_category_edit_action(){
		$mdl_restaurant_menu_option_category = $this->loadModel('restaurant_menu_option_category');
		$exist = $mdl_restaurant_menu_option_category->getByWhere(array('createUserId'=>$this->loginUser['id']));


		if(!$exist){
			$category_id =100;
			$category_sort_id=10;

			for($i=0;$i<50;$i++) {
				$menu_category_info=array(
					'category_cn_name'=>'',
					'category_en_name'=>'',
					'restaurant_id'=>$this->loginUser['id'],
					'category_id'=>$category_id,
					'category_sort_id'=>$category_sort_id,
					'createUserId'=>$this->loginUser['id']
				);
				$mdl_restaurant_menu_option_category->insert($menu_category_info);
				$category_id =$category_id+100;
				$category_sort_id =$category_sort_id +10;
			}
		}

		$pageSql = "select  * from cc_restaurant_menu_option_category where createUserId=".$this->loginUser['id']. " order by category_sort_id ";
		$pageUrl = $this->parseUrl()->set('page');
		$pageSize =50;
		$maxPage = 10;
		$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data = $mdl_restaurant_menu_option_category->getListBySql($page['outSql']);

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

		$this->setData('restaurant_menu_option_category', 'submenu_top');

		$this->setData('restaurant_set', 'submenu');
		$this->setData('index_publish', 'menu');


		$pagename = "菜品规格分类编辑";
		$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];


		$this->setData($pagename, 'pagename');
		$this->setData($pageTitle, 'pageTitle');

		$this->display_pc_mobile('restaurant/menu_option_category_edit', 'restaurant/menu_option_category_edit');
	}

	/**
	 * 餐品规格编辑页面
	 */
	function restaurant_menu_option_edit_action(){

		// 获得该用户餐厅的菜单分类信息

		$mdl_restaurant_menu_option_category = $this->loadModel('restaurant_menu_option_category');
		$pageSql = "select  * from cc_restaurant_menu_option_category where createUserId=".$this->loginUser['id']. " and (length(category_cn_name)>0 or length(category_en_name)>0) order by category_sort_id ";
		$data = $mdl_restaurant_menu_option_category->getListBySql($pageSql);

		if(!$data) {
			$this->sheader(null,'您需要首先定义菜品规格分类,然后才可以定义菜品规格....');
		}
		$this->setData($data,'restaurant_menu_option_category');



		$sk = trim(get2('sk'));
		$category = trim(get2('category'));

		$this->setData($sk,'sk');
		$this->setData($category,'category');


		$sql = "select  o.* ,b.category_cn_name,b.category_en_name  from cc_restaurant_menu_option o left join cc_restaurant_menu_option_category b on b.id=o.restaurant_category_id";

		if($category =='all' or empty($category)) {
			$whereStr.=" (length(o.menu_cn_name) >0 or length(o.menu_en_name) >0) and o.restaurant_id= ".$this->loginUser['id'];
		}else{
			$whereStr.=" o.restaurant_id= ".$this->loginUser['id'];
		}


		if (!empty($category)) {
			if ($category != 'all') {
				$whereStr.= " and o.restaurant_category_id='$category' ";
			}
		}
		if (!empty($sk)) {
			$whereStr.=" and (o.menu_cn_name  like  '%" . $sk . "%'";
			$whereStr.=" or o.menu_en_name  like  '%" . $sk . "%'";
			$whereStr.=" or o.Menu_desc  like  '%" . $sk . "%')";
		}


	    // 提示用户选择菜单分类,如果没有选择菜单分类,则显示当前全部的菜单.
		// 如果选择某一种分类,如果当前没有数据则进行增加50个,如果有数据则直接显示即可.

		$mdl_restaurant_menu_option = $this->loadModel('restaurant_menu_option');
		$pageSql=$sql . " where " . $whereStr . " order by restaurant_category_id,LENGTH(menu_id),menu_id";
		$pageUrl = $this->parseUrl()->set('page');
		$pageSize =20;
		$maxPage = 10;
		$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data = $mdl_restaurant_menu_option->getListBySql($page['outSql']);

		if(!$data){

			if($category !='all' && !empty($category)) {
				// 增加50个菜单分类
				$menu_id =100;


				for($i=0;$i<50;$i++) {
					$menu_info=array(
						'createUserId'=>$this->loginUser['id'],
						'restaurant_id'=>$this->loginUser['id'],
						'restaurant_category_id'=>$category,
						'menu_id'=>$category.$menu_id,
						'menu_cn_name'=>'',
						'price'=>'',
						'menu_pic'=>'',
						'Menu_desc'=>'',
						'menu_en_name'=>''
					);
					$mdl_restaurant_menu_option->insert($menu_info);
					$menu_id =$menu_id+1;
				}

				$pageSql = "select  * from cc_restaurant_menu_option where createUserId=".$this->loginUser['id']. " and restaurant_category_id =".$category." order by menu_id";
				$pageUrl = $this->parseUrl()->set('page');
				$pageSize =20;
				$maxPage = 10;
				$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
				$data = $mdl_restaurant_menu_option->getListBySql($page['outSql']);
			}
		}

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->setPath('restaurant/restaurant_edit'), 'editUrl');

		$this->setData('restaurant_menu_option', 'submenu_top');
		$this->setData('restaurant_set', 'submenu');
		$this->setData('index_publish', 'menu');

		$pagename = "菜品规格管理";
		$pageTitle=  $pagename." - 商家中心 - ". $this->site['pageTitle'];

		$this->setData($pagename, 'pagename');

		$this->setData($pageTitle, 'pageTitle');

		$this->display_pc_mobile('restaurant/menu_option_edit', 'restaurant/menu_option_edit');
	}


	/**
	 * ajax update menu item
	 */

	function update_menu_option_item_action(){

		if(is_post()){
			$mdl_restaurant_menu_option =$this->loadModel("restaurant_menu_option");

			$id = post('id');

			$sql ="select id from cc_restaurant_menu_option  where id=".$id. " and createUserId=".$this->loginUser['id'];

			if( !$mdl_restaurant_menu_option->getListBySql($sql))$this->form_response(600,'未发现产品','未发现产品');


			$data=array();

			$price = post('price');
			if($price)$data['price']=$price;

			$menu_id = post('menu_id');
			if($menu_id)$data['menu_id']=$menu_id;

			$menu_cn_name = post('menu_cn_name');
			if(isset($menu_cn_name))$data['menu_cn_name']=$menu_cn_name;

			$menu_en_name = post('menu_en_name');
			if(isset($menu_en_name))$data['menu_en_name']=$menu_en_name;

			$menu_desc = post('menu_desc');
			if(isset($menu_desc))$data['menu_desc']=$menu_desc;


			try {
				$mdl_restaurant_menu_option->update($data,$id);
				$this->form_response(200,'','');
			} catch (Exception $e) {
				$this->form_response(500, $e->getMessage(),'');
			}

		}else{
			//wrong protocol
		}
	}


	/**
	 *  Ajax update Category item
	 */
	public function update_menu_option_category_item_action()
	{
		if(is_post()){

			$mdl_restaurant_menu_option_category =$this->loadModel("restaurant_menu_option_category");

			$id = post('id');

			$sql ="select id from cc_restaurant_menu_option_category  where id=".$id. " and createUserId=".$this->loginUser['id'];


			if( !$mdl_restaurant_menu_option_category->getListBySql($sql)) $this->form_response(600,'未发现产品','未发现产品');


			$data=array();

			$category_sort_id = post('category_sort_id');
			if($category_sort_id)$data['category_sort_id']=$category_sort_id;

			$category_en_name = post('category_en_name');
			if(isset($category_en_name))$data['category_en_name']=$category_en_name;

			$category_cn_name = post('category_cn_name');
			if(isset($category_cn_name))$data['category_cn_name']=$category_cn_name;


			try {
				$mdl_restaurant_menu_option_category->update($data,$id);

				$this->form_response(200,'','');
			} catch (Exception $e) {
				$this->form_response(500, $e->getMessage(),'');
			}

		}else{
			//wrong protocol
		}
	}

	public function get_available_business_of_delivery_date_action() {
		$this->loadModel('freshfood_disp_suppliers_schedule');

		$centre_business_id = DispCenter::getDispCenterIdOfSupplier(post('businessid'));
		$business = DispCenter::getAvailableBusinessForDeliverDate(post('time'), post('option'), $centre_business_id, $this->lang['lang'][0]);

		$mdl_coupons = $this->loadModel("coupons");

		$html ='';
		foreach ($business as $business_id => $business_name) {
			$where =array(
				'createUserId' => $business_id,
				'EvoucherOrrealproduct' =>'restaurant_menu'
			);
			$coupon = $mdl_coupons->getByWhere($where);
			$pic = $coupon['pic'];

			$html.= "<span>";
			$html.= "<a href ='#' onclick ='swith_display_panel(".$business_id.")'>";
			$html.= "<img src='/data/upload/".$pic."'>";
			$html.=	"<div style='color:white'>".$business_name."<strong>&nbsp;".(string)$this->lang->go_shop.">></strong></div>";
			$html.= "</a>";
			$html.="</span>";
		}
		echo $html;

	}
	
	
	/**
	 *  Ajax delete Category
	 */
	public function delete_category_action()
	{
		
		if(is_post()){
			
			
			$id = post('id');
			$mdl_restaurant_category =$this->loadModel("restaurant_category");
			
			$currentRec = $mdl_restaurant_category->get($id);
			
			if ($currentRec['restaurant_id'] != $this->loginUser['id']) {
				
				$this->form_response(500, 'no access','');
			}

		
		   //检查如果该分类下面有记录没有被迁移 ，则不能删除
		   
		  //如果为大类，则检查主表
		  
		  if(!$currentRec['parent_category_id']) {
			  $where =array (
				  'restaurant_category_id' =>$id
			   
			   );
			   
			   $menuCount1 = $this->loadModel('restaurant_menu')->getCount($where);
			   
			   if ($menuCount1>0) {
				   
					$this->form_response(500, '该分类主菜单下还有数据，无法删除！请将数据迁移到其它分类，然后删除该分类','');
			   }
		   
		  }
		   
		   
		   $where =array (
		      'category_id' =>$id
		   
		   );
		   
		    $menuCount2 = $this->loadModel('restaurant_menu_category')->getCount($where);
		   
		   if ($menuCount2>0) {
			   
			   	$this->form_response(500, '该分类多级菜单下还有数据，无法删除！请将数据迁移到其它分类，然后删除该分类','');
		   }
		   
		
			try {
				
				
				$mdl_restaurant_category->update(['isdeleted' => 1,'isHide' => 1],$id);
				$this->form_response(200,'删除成功','');
			} catch (Exception $e) {
				$this->form_response(500, '删除不成功','');
			}
		}
	}

	

	/**
	 *  Ajax update Category
	 */
	public function update_category_gst_action()
	{
		if(is_post()){
			$mdl_restaurant_category =$this->loadModel("restaurant_category");

			$id = post('id');
			$sql ="select id from cc_restaurant_category  where id=".$id. " and createUserId=".$this->loginUser['id'];

			if( !$mdl_restaurant_category->getListBySql($sql)) $this->form_response(500,'未发现品类','未发现品类');

			$include_gst = post('include_gst');

			try {
				$mdl_restaurant_menu =$this->loadModel('restaurant_menu');
				$mdl_restaurant_menu->updateByWhere([
					'include_gst' => $include_gst
				], [
					'restaurant_category_id' => $id
				]);
				$this->form_response(200,'','');
			} catch (Exception $e) {
				$this->form_response(500, $e->getMessage(),'');
			}
		}
	}

	public function get_bought_list_action() {
		$id = (int)get2( 'id' );

		$deliveryTime = $this->cookie->getCookie('DispCenterUserSelectedDeliveryDate');
		$menu_bought_list = $this->loadModel("restaurant_menu")->getUserBoughtMenu($this->loginUser['id'],$id, $deliveryTime, $this->lang['lang'][0]);
		$this->setData($menu_bought_list, 'menu_bought_list');
		if($menu_bought_list) {
			$where =array(
				'createUserId' => $id,
				'EvoucherOrrealproduct' =>'restaurant_menu'
			);

			$mdl_coupons =$this->loadModel("coupons");
			$restaurant_coupon= $mdl_coupons->getByWhere($where);

			$this->setData( $restaurant_coupon['id'], 'restaurant_couponID' );
			echo $this->fetch('mobile/restaurant/menu_bought_list');
		} else {
			echo null;
		}
	}
	
		public function get_menu_by_keyword_action($id)
	{
		$id = get2('id');
		$keyword = get2('keyword');
		$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

		$where1 = [
			'restaurant_id' => $id,
		];
		
	

		
			$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

			//菜单列表
			$sql = "select c.category_cn_name, c.category_en_name,a.*,b.category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where a.restaurant_id=".$id."  and (c.category_cn_name like '%$keyword%' or a.menu_cn_name like '%$keyword%' ) and  (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by b.category_sort_id,a.menu_order_id,a.menu_id";
			$menu = $mdl_restaurant_menu->getListBySql($sql);
           // var_dump($sql);exit;
			$sql_special = "select '本期特价' as category_cn_name, 'On Sale' as category_en_name,a.*,'1000' as  restaurant_category_id,'1' as category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id." ) and  (c.category_cn_name like '%$keyword%' or a.menu_cn_name like '%$keyword%'  ) and a.onSpecial =1 and   c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by a.menu_order_id,a.menu_id";
			$menu_sub = $mdl_restaurant_menu->getListBySql($sql_special);
			foreach ($menu_sub as $key => $value) {
				$menu_sub[$key]['restaurant_category_id'] = 'hot';
				$menu_sub[$key]['price'] = $value['speical_price'];
				$menu_sub[$key]['onSpecial'] = 1;

				if ($value['original_price'] <= 0) { //如果原价为空

					$menu_sub[$key]['original_price'] = $value['price'];
				}
				if ($value['limit_buy_qty'] > 0) {
					$menu_sub[$key]['menu_cn_name'] = '('.$this->lang->limit_buy.' '.$value['limit_buy_qty'].' '.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];;
				} else {
					$menu_sub[$key]['menu_cn_name'] = '('.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];;
				}
			}

			$menu = array_merge($menu_sub, $menu);

			foreach ($menu as $key => $value) {
				$menu[$key]['onSpecial'] = 0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
				if ($value['original_price'] <= 0) { //如果原价为空

					$menu[$key]['original_price'] = $value['price'];
				}

				if ($this->getLangStr() == 'en') {
					if (! $menu[$key]['menu_en_name']) {
						$menu[$key]['menu_en_name'] = $menu[$key]['menu_cn_name'];
					}
				}

				$menu[$key]['new_price'] = number_format($menu[$key]['price'] * (1 - $restaurant_promotion_manjian_rates), 2);

				//加载购物车已购买数量
				$sql = "select quantity from cc_wj_user_temp_carts where main_coupon_id=".$id." and menu_id=".$value['id']." and sidedish_menu_id=0 and userId=".$this->loginUser['id'];

				$result = $mdl_wj_user_temp_carts->getListBySql($sql);
                if($result) {
				//显示新价格
				$menu[$key]['quantity'] = $result[0]['quantity'];
				}else{
					
				}
			}
	

		// 换 en cn
		$old_cat = "";
		$menuIds = [];
	

		$menus = [];
		foreach ($menu as $key => $value) {
			if (! $menu[$key]['category_en_name']) {
				$menu[$key]['category_en_name'] = $menu[$key]['category_cn_name'];
			}

			if (! $menu[$key]['unit_en']) {
				$menu[$key]['unit_en'] = $menu[$key]['unit'];
			}

			if ($this->lang['lang'][0] == 'English') {
				$new_cat = $menu[$key]['category_en_name'];
				$menu[$key]['category_name'] = $menu[$key]['category_en_name'];
				$menu[$key]['unit'] = $menu[$key]['unit_en'];
				$menu[$key]['menu_name'] = $menu[$key]['menu_en_name'];
				$menu[$key]['menu_desc'] = $menu[$key]['menu_en_desc'];
			} else {
				$new_cat = $menu[$key]['category_cn_name'];
				$menu[$key]['category_name'] = $menu[$key]['category_cn_name'];
				$menu[$key]['menu_name'] = $menu[$key]['menu_cn_name'];
			}

			if ($old_cat <> $new_cat) {
				$menu[$key]['new_cat'] = $new_cat;
				$old_cat = $new_cat;
			} else {
				$menu[$key]['new_cat'] = 0;
			}

			if(!$categoryId || $menu[$key]['restaurant_category_id'] == $categoryId || in_array($menu[$key]['id'], $menuIds)) {
				array_push($menus, $menu[$key]);
			}
		}

		echo json_encode($menus);
		return $menu;
	}
	
	
	

	public function get_menu_by_category_action($id)
	{
		$id = get2('id');
		$categoryId = get2('category_id');
		$mdl_restaurant_menu = $this->loadModel('restaurant_menu');

		$where1 = [
			'restaurant_id' => $id,
		];
		$mdl_restaurant_promotion_manjian = $this->loadModel("restaurant_promotion_manjian");
		$restaurant_promotion_manjian = $mdl_restaurant_promotion_manjian->getByWhere($where1);
		if ($restaurant_promotion_manjian) {
			$restaurant_promotion_manjian_rates = $restaurant_promotion_manjian['discount'] / 100;
		} else {
			$restaurant_promotion_manjian_rates = 0;
		}

		if (! $this->loginUser) {
			$sql = "select c.category_cn_name,c.category_en_name,a.* from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id."  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." ) and length(a.menu_pic)>0  and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id order by b.category_sort_id,menu_order_id,menu_id";
			$menu = $mdl_restaurant_menu->getListBySql($sql);

			$sql_special = "select '本期特价' as category_cn_name, 'On Sale' as category_en_name,a.*,'1000' as  restaurant_category_id,'1' as category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id."  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and length(a.menu_pic)>0  and a.onSpecial =1 and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by a.menu_order_id,a.menu_id";
			$menu_sub = $mdl_restaurant_menu->getListBySql($sql_special);
			foreach ($menu_sub as $key => $value) {
				$menu_sub[$key]['restaurant_category_id'] = 'hot';
				$menu_sub[$key]['price'] = $value['speical_price'];
				$menu_sub[$key]['onSpecial'] = 1;
				if ($value['original_price'] <= 0) { //如果原价为空

					$menu_sub[$key]['original_price'] = $value['price'];
				}
				if ($value['limit_buy_qty'] > 0) {
					$menu_sub[$key]['menu_cn_name'] = '('.$this->lang->limit_buy.' '.$value['limit_buy_qty'].' '.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];
				} else {
					$menu_sub[$key]['menu_cn_name'] = '('.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];
				}
			}

			foreach ($menu as $key => $value) {
				$menu[$key]['onSpecial'] = 0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
				if ($value['original_price'] <= 0) { //如果原价为空

					$menu[$key]['original_price'] = $value['price'];
				}
				$menu[$key]['new_price'] = number_format($menu[$key]['price'] * (1 - $restaurant_promotion_manjian_rates), 2);
			}

			$menu = array_merge($menu_sub, $menu);
		}
		else {
			//清除购物车其它产品
			$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');

			//菜单列表
			$sql = "select c.category_cn_name, c.category_en_name,a.*,b.category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id."  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.") and length(a.menu_pic)>0  and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by b.category_sort_id,a.menu_order_id,a.menu_id";
			$menu = $mdl_restaurant_menu->getListBySql($sql);

			$sql_special = "select '本期特价' as category_cn_name, 'On Sale' as category_en_name,a.*,'1000' as  restaurant_category_id,'1' as category_sort_id from cc_restaurant_category c,cc_restaurant_menu a, cc_restaurant_category b where (a.restaurant_id=".$id."  or a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id." )) and length(a.menu_pic)>0   and a.onSpecial =1 and  a.restaurant_category_id in (select id from cc_restaurant_category where restaurant_id =".$id.")   and (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and  c.id=a.restaurant_category_id  and b.id = a.restaurant_category_id and a.visible=1 order by a.menu_order_id,a.menu_id";
			$menu_sub = $mdl_restaurant_menu->getListBySql($sql_special);
			foreach ($menu_sub as $key => $value) {
				$menu_sub[$key]['restaurant_category_id'] = 'hot';
				$menu_sub[$key]['price'] = $value['speical_price'];
				$menu_sub[$key]['onSpecial'] = 1;

				if ($value['original_price'] <= 0) { //如果原价为空

					$menu_sub[$key]['original_price'] = $value['price'];
				}
				if ($value['limit_buy_qty'] > 0) {
					$menu_sub[$key]['menu_cn_name'] = '('.$this->lang->limit_buy.' '.$value['limit_buy_qty'].' '.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];;
				} else {
					$menu_sub[$key]['menu_cn_name'] = '('.$this->lang->original_price.$menu_sub[$key]['original_price'].')'.$menu_sub[$key]['menu_cn_name'];;
				}
			}

		

			foreach ($menu as $key => $value) {
				$menu[$key]['onSpecial'] = 0; //之前的on sepcial 都已提出了，下面再常规类别中显示的都可以不视为special
				if ($value['original_price'] <= 0) { //如果原价为空

					$menu[$key]['original_price'] = $value['price'];
				}

				if ($this->getLangStr() == 'en') {
					if (! $menu[$key]['menu_en_name']) {
						$menu[$key]['menu_en_name'] = $menu[$key]['menu_cn_name'];
					}
				}

				$menu[$key]['new_price'] = number_format($menu[$key]['price'] * (1 - $restaurant_promotion_manjian_rates), 2);

				//加载购物车已购买数量
				$sql = "select quantity from cc_wj_user_temp_carts where main_coupon_id=".$id." and menu_id=".$value['id']." and sidedish_menu_id=0 and userId=".$this->loginUser['id'];

				$result = $mdl_wj_user_temp_carts->getListBySql($sql);

				//显示新价格
				$menu[$key]['quantity'] = $result[0]['quantity'];
			}
			
				$menu = array_merge($menu_sub, $menu);
		}

		// 换 en cn
		$old_cat = "";
		$menuIds = [];
		if($categoryId) {
			$menuCategories = $this->loadModel('restaurant_menu_category')->findMenuIdsByCategoryId($categoryId);
			foreach ($menuCategories as $menuCategory) {
				array_push($menuIds, $menuCategory['restaurant_menu_id']);
			}
		}

		$menus = [];
		foreach ($menu as $key => $value) {
			if (! $menu[$key]['category_en_name']) {
				$menu[$key]['category_en_name'] = $menu[$key]['category_cn_name'];
			}

			if (! $menu[$key]['unit_en']) {
				$menu[$key]['unit_en'] = $menu[$key]['unit'];
			}

			if ($this->lang['lang'][0] == 'English') {
				$new_cat = $menu[$key]['category_en_name'];
				$menu[$key]['category_name'] = $menu[$key]['category_en_name'];
				$menu[$key]['unit'] = $menu[$key]['unit_en'];
				$menu[$key]['menu_name'] = $menu[$key]['menu_en_name'];
				$menu[$key]['menu_desc'] = $menu[$key]['menu_en_desc'];
			} else {
				$new_cat = $menu[$key]['category_cn_name'];
				$menu[$key]['category_name'] = $menu[$key]['category_cn_name'];
				$menu[$key]['menu_name'] = $menu[$key]['menu_cn_name'];
			}

			if ($old_cat <> $new_cat) {
				$menu[$key]['new_cat'] = $new_cat;
				$old_cat = $new_cat;
			} else {
				$menu[$key]['new_cat'] = 0;
			}
			
				//	var_dump('ff'.substr($menu[$key]['menu_pics'],0,2));exit;
				
					
				$menu_pic =$menu[$key]['menu_pics'];
				 $aa= unserialize($menu_pic);
				 $bb= implode(";", $aa);
				 if($bb) {
					  $menu[$key]['menu_pics'] =$bb;
				 }
				
		
			
		


			if(!$categoryId || $menu[$key]['restaurant_category_id'] == $categoryId || in_array($menu[$key]['id'], $menuIds)) {
				array_push($menus, $menu[$key]);
			}
			
			//$menu[$key]['menu_pic'] ='dfdfsdf';
		}


        
		echo json_encode($menus);
		return $menu;
	}

	public function fresh_action()
	{
		$id = (int) get2('id');
		$userId = $this->loginUser['id'];
		$cart = (int) get2('cart');
        $this->setData(1,'dy'); //采用动态页面

		if (! $id) {
			$this->sheader(null, '请选择正确商户');
		}

		if ($this->loginUser) {
			//插入一段获取某用户购买历史的程序
			$deliveryTime = $this->cookie->getCookie('DispCenterUserSelectedDeliveryDate');
			$menu_bought_list = $this->loadModel("restaurant_menu")->getUserBoughtMenu($userId, $id, $deliveryTime, $this->lang['lang'][0]);
			$this->setData($menu_bought_list, 'menu_bought_list');
			//var_dump($menu_bought_list);exit;
		}

		$where = [
			'createUserId' => $id,
			'EvoucherOrrealproduct' => 'restaurant_menu',
		];

		$mdl_coupons = $this->loadModel("coupons");

		$restaurant_coupon = $mdl_coupons->getByWhere($where);
		$this->setData($restaurant_coupon['id'], 'restaurant_couponID');

		// 获取的餐馆ID--是商家的ID ,判断当前商家是否设置餐厅入口
		if (($restaurant_coupon['isApproved'] == 1 && $restaurant_coupon['status'] == 4) || $restaurant_coupon['createUserId'] == $this->loginUser['id'] || $_SESSION['coupon_private_view_allowed'] == $restaurant_coupon['id']) {
			$mdl_user = $this->loadModel("user");
			$business_user = $mdl_user->get($restaurant_coupon['createUserId']);
			$restaurant_coupon['business'] = $business_user;
			$this->setData($business_user,'business_user');
			//var_dump($business_user);exit;
			//$sql="select count(*) as count from cc_wj_customer_coupon where createUserId=".$restaurant_coupon['createUserId'];
			$count = $this->loadModel('wj_customer_coupon')->getCount(['business_id'=>$restaurant_coupon['createUserId']]);
			$this->setData($count, 'sales_count');
			$this->setData($restaurant_coupon, 'coupon');
			
			
			$delivery_fee_desc  = $this->get_business_delivery_des ($business_user['id']);
			 $this->setData($delivery_fee_desc, 'delivery_fee_desc');
			 
		} else {
			$this->sheader(HTTP_ROOT_WWW.'coupon1/coupon_private_view_gate?id='.$restaurant_coupon['id']);
			$this->sheader(null, '当前商家还未开启线上餐厅,请稍后..');
		}

		$this->setData($id, 'restaurant_id');
		$this->setData($cart, 'cart');
      
	  $where1 = [
			'restaurant_id' => $id,
		];
		$mdl_restaurant_promotion_manjian = $this->loadModel("restaurant_promotion_manjian");
		$restaurant_promotion_manjian = $mdl_restaurant_promotion_manjian->getByWhere($where1);
		$this->setData($restaurant_promotion_manjian, 'restaurant_promotion_manjian');
	  
        $prom_rec =$this->loadModel('restaurant_promotion_manjian')->get( $restaurant_coupon['id']);
  	 // var_dump($restaurant_coupon);exit;
		$title = str_replace('|', '', $restaurant_coupon['title']);
		$this->setData($title, 'pageTitle');
		$this->setData($restaurant_coupon['searchKeywords'], 'pageKeywords');
		$this->setData($restaurant_promotion_manjian['promotion_desc'], 'pageDescription');

		$this->loadModel('freshfood_disp_suppliers_schedule');
		$businessDispSchedule = DispCenter::getBusinessDispSchedule($id);
		$businessDispScheduleFilledWithContinueDates = DispCenter::getFollowingNDaysIncludeAvailableDeliver($businessDispSchedule);
		$this->setData($businessDispScheduleFilledWithContinueDates, 'businessDispSchedule');
		$this->setData(in_array($id, DispCenter::getSupplierList()), 'isDispCenterBusiness');

		$this->setData(join(DispCenter::getPostcode(DispCenter::getDispCenterIdOfSupplier($id)), ','), 'postcodeSupported'); //使用统配商家邮编信息

		
		$this->setData(1, 'lazyload');

		$restaurant_category = self::get_category_list($id);
		$this->setData($restaurant_category, 'restaurant_category');





		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        $shareUrl = HTTP_ROOT_WX."restaurant/$id?dy=1&reftag=".$this->loginUser['id'];
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');

        $this->setData( get2('action'), 'returnAction' );

        $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');
      
		   
		
	 
		
	  
       
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');


    
        //获得当前店铺得 订货时间
		 $mdl = $this->loadModel('freshfood_disp_suppliers_schedule');
				$where = [];
				$where['business_id'] = $id;
				$where['centre_business_id'] = DispCenter::getDispCenterIdOfSupplier($id);
				$list = $mdl->getList(null, $where);
				
				
			
			foreach ($list as $key =>$value) {
				$order_start_of_date_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['order_start_of_date']);
				$order_cut_of_date_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['order_cut_of_date']);
				$delivery_date_of_week_cn = $this->get_cn_weekdayName_from_en_weekdayName($value['delivery_date_of_week']);
				
				$list[$key]['order_start_of_date_cn'] = $order_start_of_date_cn ;
				$list[$key]['order_cut_of_date'] =$order_cut_of_date_cn  ;
				$list[$key]['delivery_date_of_week_cn'] = $delivery_date_of_week_cn ;
				$list[$key]['ordertime_cn'] = '从'.$order_start_of_date_cn.$value['order_start_of_time'] .' 到 '.$order_cut_of_date_cn.$value['order_cut_of_time'];
				$list[$key]['ordertime'] = 'From '.$value['order_start_of_date'].' '.$value['order_start_of_time'] .' To '.$value['order_cut_of_date'].' '.$value['order_cut_of_time'];
		       
			}
				
				
				
				
            $this->setData($list,'current_business_tuangou_time');
		



		$this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id), 'storeOverWriteLink');

		$this->display_pc_mobile('mobile/fresh/index', 'mobile/fresh/index');

		return;
	}

	public function get_category_list($id)
	{
		//加载餐馆菜单
		$mdl_restaurant_category = $this->loadModel('restaurant_category');
		$restaurant_category = $mdl_restaurant_category->getListBySql("select * from cc_restaurant_category where restaurant_id = ".$id." and isHide=0 and (length(category_cn_name)>0 or length(category_en_name)>0) order by `category_sort_id`");

		foreach ($restaurant_category as $key => $value) {
			if ($restaurant_category[$key]['category_en_name'] == '') {
				$restaurant_category[$key]['category_en_name'] = $restaurant_category[$key]['category_cn_name'];
			}
			if ($this->lang['lang'][0] == 'English') {
				$restaurant_category[$key]['category_name'] = $restaurant_category[$key]['category_en_name'];
			} else {
				$restaurant_category[$key]['category_name'] = $restaurant_category[$key]['category_cn_name'];
			}
		}

		// 如果发现有special菜单，那么生成一个新的类别编号，并置顶
		$mdl_restaurant_menu = $this->loadModel('restaurant_menu');
		$sql_special = "select count(*) as count from cc_restaurant_menu a where a.restaurant_id =$id and  (length(a.menu_cn_name)>0 or length(a.menu_en_name)>0) and a.visible=1 and a.onSpecial =1  ";
		$exist_special = $mdl_restaurant_menu->getListBySql($sql_special);
		if ($exist_special[0]['count'] > 0) {

			$special_array = [
				'id' => 'hot',
				'restaurant_id' => $id,
				'category_id' => 1000,
				'category_sort_id' => 10,
				'createUserId' => $id,
				'ref_restaurant_id' => 0,
				'ref_DishTypeId' => 0,
				'hot' => 1,
			];

			if ($this->lang['lang'][0] == 'English') {
				$special_array['category_name'] = 'On Sale';
			} else {
				$special_array['category_name'] = '本期优惠';
			}
			array_unshift($restaurant_category, $special_array);
		}
      
		return $restaurant_category;
	}

	public function update_restaurant_menu_category_action() {
		$restaurantMenuId = post('restaurant_menu_id');
		$category_id = post('category_id');
		$isAdd = post('is_add');
		
		//$restaurantMenuId=388315;
		//$category_id = 38691;
		//$isAdd =false;
		
		
		try {
			if($isAdd) {
				//	$this->form_response(500,'1'.$restaurantMenuId. ' '.$category_id .' '.$isAdd);exit;
				$this->loadModel('restaurant_menu_category')->insertIfExist($restaurantMenuId, $category_id);
			} else {
			//	$this->form_response(500,'2'.$restaurantMenuId. ' '.$category_id .' '.$isAdd);exit;
				$this->loadModel('restaurant_menu_category')->deleteIfExist($restaurantMenuId, $category_id);
			}
			$this->form_response(200,'','');
		} catch (Exception $e) {
			$this->form_response(500, $e->getMessage(),'');
		}
	}

	public function update_restaurant_menu_category1_action() {
		$restaurantMenuId = post('restaurant_menu_id');
		
		try {
			
			
			 // 检查如果在 restaurant_menu_category里面如果不存在分类，则，这个无法删除，进行提示，如果在 前面提到的表中有，则取第一个，并把他转换成大类标号
			  $mdl = $this->loadModel('restaurant_menu');
			  $menuRec = $mdl->get($restaurantMenuId);
			  
			  if($menuRec){
				   $mdl_menu_category = $this->loadModel('restaurant_menu_category');
				   $newCategoryId =$mdl_menu_category->getFirstCateInRestaurantMenuCategory ($menuRec['id']);
				   if ($newCategoryId) {
					   
						$data =array(
						 'restaurant_category_id' =>$newCategoryId
						);
						$mdl->update($data,$restaurantMenuId);
						
					
						$this->form_response(200,'update success!','');
					   
				   }else{
					   
					   $this->form_response(200,'产品必须有至少一个分类，请指定其它分类后再删除该分类!','');
				   }
					
			  }else{
				  $this->form_response(500, 'no access','');
				  
			  }
			  
			 
		} catch (Exception $e) {
			$this->form_response(500, $e->getMessage(),'');
		}
	}

	public function get_temp_cart_action() {
		$mdl_wj_user_temp_carts = $this->loadModel('wj_user_temp_carts');
			$mdl_restaurant_menu =$this->loadModel('restaurant_menu');
		$businessUserId = get2('restaurant_id');
		$userId=$this->loginUser['id'];
		$sql ="SELECT category_sort_id,category_cn_name,a.*,b.menu_pic as pic ,d.pic as coupon_pic FROM `cc_wj_user_temp_carts` a left join `cc_restaurant_menu` b on b.id=a.`menu_id` left join `cc_restaurant_category` c on c.id=b.restaurant_category_id left join cc_coupons d on d.id=a.main_coupon_id  where a.userId=" .$userId."   and businessUserId =".$businessUserId . " order by category_sort_id,b.menu_id";

		if(!$userId||!$businessUserId)return false;

		$cartItems=$mdl_wj_user_temp_carts->getListBySql($sql);

		$cartTotalPrice = 0;
		$cartTotalQuantity = 0;

		$old_category='0';
		foreach ($cartItems as $key => $val) {
				$menu_rec =$mdl_restaurant_menu->get($val['menu_id']);
			
			if(!$menu_rec){
					$mdl_wj_user_temp_carts->delete($val['id']); // 如果菜单中没有这个，那么从临时购物车中删除。
					continue;
				}else{
					
					
				// 如果找到菜单中的项目，检查是否已经下线，如果下线，则删除

					if (!$menu_rec['visible']) {
						//var_dump($menu_rec['visible'].$menu_rec['id']);
						$mdl_wj_user_temp_carts->delete($val['id']);

					}

                   ///如果是特价产品
				   if($val['onSpecial']) {

					   if($menu_rec['speical_price'] != $val['single_amount']) {


						$new_item_price_data= array(
							'single_amount' =>$menu_rec['speical_price']
						);
						$mdl_wj_user_temp_carts->update($new_item_price_data,$val['id']);

					}




				   }else{

					  	// 如果在菜单中找到该产品，检查价格是否和菜单中描述一致，不一致，则更新
					if($menu_rec['price'] != $val['single_amount']) {




						$new_item_price_data= array(
							'single_amount' =>$menu_rec['price']
						);
						$mdl_wj_user_temp_carts->update($new_item_price_data,$val['id']);

					}



				   }



					// 如果在菜单中找到该产品，检查库存是否溢出，如果溢出，则把库存调整到当前最大值

					if($menu_rec['qty'] < $val['quantity']) {
						$new_item_quantity_data= array(
							'quantity' =>$menu_rec['qty']
						);
						$mdl_wj_user_temp_carts->update($new_item_quantity_data,$val['id']);


					}		
					
		
			if ($val['category_sort_id'] !== $old_category) {
				if(!$val['category_sort_id']) { // 没有分类 就是团购套餐

					$cartItems[$key]['category_cn_name']='团购';
				}
				$cartItems[$key]['new_cat']=1;
			}else{
				$cartItems[$key]['new_cat']=0;

			}
			$old_category=$val['category_sort_id'];
			if(!$val['category_sort_id']) {
				$cartItems[$key]['pic']=$val['coupon_pic'];
			}


			$cartTotalQuantity += $val['quantity'];
			$cartTotalPrice+=$val['single_amount']*$val['quantity'];
		}
		}

		$data = [
			'items' => $cartItems,
			'totalPrice' => $cartTotalPrice,
			'totalQuantity' => $cartTotalQuantity
		];

		echo json_encode($data);
		return $data;
	}
}
?>
