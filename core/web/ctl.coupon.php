<?php

class ctl_coupon extends cmsPage
{
	function index_action() {
		/**
		 * 产品分类
		 * @var [type]
		 */
		$alias = trim( get2( 'alias' ) );
		$refTag = trim(get2('reftag'));
		
	    $this->setData($refTag,'reftag');
		$restaurant_menu = trim( get2( 'restaurant_menu' ) );
		
		
		if (!$alias)$alias='106';
		
		if(strlen($alias)>9) {
		//var_dump($alias);exit;
			$searchKeywords = '';
		}else{
			$searchKeywords = trim( get2( 'key' ) );
			
		} 
		$this->setData( $alias,'alias');
		//var_dump($alias);exit;
		/**
		 * 搜索商家还是搜索产品
		 * @var [type]
		 */
		$listType = trim(get2('listType'));
		//var_dump($listType);exit;
		if ( ! in_array( $listType, array('business1', 'business','coupon' ) ) ) $listType = 'coupon';

		$this->setData( $listType,'primaryListType');

		/**
		 * 排序规则
		 * @var [type]
		 */
		$orderby = trim( get2( 'orderby' ) );
		if ( ! in_array( $orderby, array( 'default','id', 'hits', 'buy' ) ) ) $orderby = 'default';

		$this->setData( $orderby, 'orderby' );


		/**
		 * 搜索关键字
		 * @var [type]
		 */
		//$searchKeywords = trim( get2( 'key' ) );
		if(!$searchKeywords)$searchKeywords='';

		$this->setData( $searchKeywords, 'searchKeywords' );


		/**
		 * 过滤coupon type
		 * @var [type]
		 */
		$couponType = trim( get2( 'couponType' ) );
		if ( ! in_array( $couponType, array( '2','4', '7', '9','10' ) ) ) $couponType = '';

		$this->setData( $couponType, 'couponType' );


     	$cityid = (int)get2('cityid');
		
		if (!$cityid){
			$city1 =$this->city;
			$this->setData( $city, 'city1' );
			$cityid=$this->city['id'];
		}else{
		//var_dump('cityid is: '.$cityid);exit;
		 $city1 = $this->loadModel('city')->get( $cityid );
         $this->setData( $city1, 'city1' );
		}
         $this->setData( $cityid, 'cityid' );
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
		$this->setData($mdl_infoclass->getByAlias( substr($category['alias'], 0,-3)) , 'parent' );


		$this->setData( $child_category, 'childs' );

		/**
		 * search base condition
		 */
		if($listType=='business1'){

			$business_where = array();
			$business_where['isApproved'] = 1;
			$business_where['role'] = 3;
			
		
			
			
						
			$business_where['role'] = 3;
			

			if($searchKeywords)
				$business_where[]=" (businessName like '%$searchKeywords%' or companyDescription like '%$searchKeywords%' ) ";

			$pageSql	= $mdl_user->getListSql( array( 'id','logo', 'tel','businessName', 'googleMap','companyDescription'), $business_where, "id desc" );
			$pageUrl	= $this->parseUrl()->set('page')->set( 'listType','business');
			$pageSize	= 20;
			$maxPage	= ($this->getUserDevice()=='desktop')?10:0;
			$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
			$data		= $mdl_user->getListBySql($page['outSql']);

			foreach ($data as $key => $value) {
               
			     if($this->getLangStr()=='en'){
					$data[$key]['coupons']= $mdl_coupons->getListBySql("select id,title_en as title,hits, pic,bonusType, voucher_deal_amount,voucher_original_amount from  #@_coupons where isApproved='1' and status='4' ".$this->get_multiLanguage_where()." and EvoucherOrrealproduct <> 'restaurant_menu' and createUserId = '".$value['id']."' limit 12");
                 }else{
					$data[$key]['coupons']= $mdl_coupons->getListBySql("select id,title,hits, pic,bonusType, voucher_deal_amount,voucher_original_amount from  #@_coupons where isApproved='1' and status='4' ".$this->get_multiLanguage_where()." and EvoucherOrrealproduct <> 'restaurant_menu' and createUserId = '".$value['id']."' limit 12");
     		 
				 }
				$data[$key]['ratting_score']= $mdl_customer_rating->getAvgScore($value['id']);
				
				$data[$key]['businessDisplayName']=$mdl_user->getBusinessDisplayName($value['id']);

				foreach ($data[$key]['coupons'] as $cckey => $ccvalue) {
					//根据产品类别不同，计算显示的价格和计算Ubonus点数
					$mdl_coupons->caculatePriceAndPoint($data[$key]['coupons'][$cckey]);
					
				}
			}

			$this->setData($data,'businessConpanyData');

			$this->setData( $page, 'pager' );


		}elseif($listType=='coupon' || $listType=='business'){

			$coupon_where = array();
			$coupon_where[] = " categoryId like '%,".$category['id']."%' ";

			$coupon_where[] = " (city like '%,".$cityid."%' or city like '%,526,%' or city='') ";

			if($this->getLangStr()=='en'){
			$coupon_where[]=" languageType_en=1 ";
			}
			if($this->getLangStr()=='zh-cn'){
			$coupon_where[]=" languageType_cn=1 ";
			}	

			
			
			$currentTime=time();
			$coupon_where['isApproved'] = 1;
			$coupon_where['status'] = 4;
		//	$coupon_where[] = " !(autoOffline=1 AND ('$currentTime'<startTime or '$currentTime'>endTime)) ";


			if($couponType)$coupon_where['bonusType'] = $couponType;

			$mdl_advancedKeySearch->advancedKeyCalculation($searchKeywords);

			// echo $mdl_advancedKeySearch->getLocation();// if key contains location name

			// echo $mdl_advancedKeySearch->getKeySql('title',true);
			// echo $mdl_advancedKeySearch->getKeySql('businessName',true);
			// echo $mdl_advancedKeySearch->getKeySql('coupon_summery_description',true);

			if($searchKeywords)
				$coupon_where[]=" (title like '%$searchKeywords%' or businessName like '%$searchKeywords%' or coupon_summery_description like '%$searchKeywords%' or searchKeywords like '%$searchKeywords%') ";

			if($orderby=='default')$orderby='id desc';
			if($orderby=='hits')$orderby='hits desc';
			if($orderby=='buy')$orderby='buy desc';
			if($orderby=='id')$orderby='id desc';

            /*
            *商品批发
            */
            if(get2('wholesale')=='1')
            {
              //  $pageSql	= "SELECT w.price AS  wholesale_price1,w.amount,w.price1 as wholesale_price2,w.amount1,w.price2 as wholesale_price3,w.amount2,c.id,c.title,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount FROM `cc_wholesale` as w LEFT JOIN  cc_coupons as c ON c.id=w.couponid WHERE c.isApproved=1 AND c.status=4 AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime)) AND c.categoryId like '%,$alias%' order by ".$orderby;
                $pageSql	= "SELECT w.price AS  wholesale_price1,w.amount,w.price1 as wholesale_price2,w.amount1,w.price2 as wholesale_price3,w.amount2,c.id,c.title,c.pic,c.createUserId,c.bonusType,c.hits,c.buy,c.voucher_deal_amount,c.voucher_original_amount FROM `cc_wholesale` as w LEFT JOIN  cc_coupons as c ON c.id=w.couponid WHERE c.isApproved=1 AND c.status=4  ". $this->get_multiLanguage_where('c')."AND c.categoryId like '%,$alias%' order by ".$orderby;
         
		  }
            else
            {
				
				
				$pageSql ="
				select * from (
				
				select '1' as business,businessName, c.`id`, `title`, `title_en`, c.`pic`, `createUserId`, `bonusType`, c.`hits`, c.`buy`, `voucher_deal_amount`, `voucher_original_amount`, `coupon_summery_description`, `coupon_summery_description_en` 
				from cc_coupons c
				where categoryId like '%,106%' 
				and (city like '%,556%' or city like '%,526,%' or city='') 
				and languageType_cn=1 
				and `isApproved`='1' 
				and `status`='4' 
				and (title like '%$searchKeywords%'  or businessName like '%$searchKeywords%'  or coupon_summery_description like '%$searchKeywords%'  or searchKeywords like '%$searchKeywords%' ) 
				
				union 

				select '0' as business,u.displayName as businessName ,m.id,menu_cn_name as title ,menu_en_name as title_en ,menu_pic as pic,restaurant_id as createUserId,'18' as bonusType, '0' as hits,'0' as buy,price as voucher_deal_amount ,original_price as voucher_original_amount,menu_desc as coupon_summery_description,menu_en_desc as coupon_summery_description_en 
				from cc_restaurant_menu m left join cc_coupons c on m.restaurant_id=c.createUserId   left join cc_user u on m.restaurant_id = u.id 
				where c.EvoucherOrrealproduct = 'restaurant_menu' 
				and m.visible=1 
				and  c.isApproved=1 and c.status=4 
				
				and ( menu_cn_name like  '%$searchKeywords%'   or menu_en_name like  '%$searchKeywords%'  or menu_desc   like  '%$searchKeywords%'   or menu_en_desc like  '%$searchKeywords%'  ) 
				
				) a 


				order by a.createUserId desc
				
				";
				
				
				
				//var_dump($pageSql);exit;
				
				
			   // $pageSql	= $mdl_coupons->getListSql( array( 'id', 'title', 'title_en', 'pic','createUserId', 'bonusType', 'hits', 'buy','voucher_deal_amount','voucher_original_amount','coupon_summery_description','coupon_summery_description_en'), $coupon_where, $orderby );




            }
			
			$pageUrl	= $this->parseUrl()->set('page')->set( 'listType','coupons');
			$pageSize	= 30;
			$maxPage	= ($this->getUserDevice()=='desktop')?10:0;
			$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
			//var_dump($pageSql);exit;
			$data		= $mdl_coupons->getListBySql($page['outSql']);
			foreach ($data as $key => $value) {
				$mdl_coupons->caculatePriceAndPoint($data[$key]);
				  if($this->getLangStr()=='en' &&  $data[$key]['title_en']){
					  $data[$key]['title']= $data[$key]['title_en'];
					  
				  }
			}

			$this->setData( $page, 'pager' );

			$this->setData( $data, 'data' );//var_dump($data);exit();
		}

        $this->get_google_seo_info_base_search($data,$city1,$category,$searchKeywords);
		

		$this->setData( $this->parseUrl(), 'shareUrl');

		$this->setData( 'Ubonus', 'shareDesc');

		//$this->setData( $mdl_coupons->getRandom(),'guessYouLike');


		$this->setData( $this->parseUrl()->set( 'page' ), 'catUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'orderby' ), 'orderbyUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'cityid' ), 'cityUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'listType' ), 'listTypeUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'key' ), 'searchUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'couponType' ), 'couponTypeUrl' );

		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

       

		if($listType=='business1'){	
			$this->responseDisplay('coupon/search_result_business');
		}else{
			$this->responseDisplay('coupon/search_result_product');
		}
		
	}

	public function unsubscribe_action()
	{
		$mdl_subscribe= $this->loadModel('subscribe');

		$id=get2('id');
		$sign=get2('sign');

		$item = $mdl_subscribe->get($id);


		if($item){
			if(md5($item['email'])==$sign){
				$mdl_subscribe->update(array('classId'=>2),$id);
				$this->sheader(null,(string)$this->lang->email_unsub_success);
			}else{
				 $this->sheader(null,(string)$this->lang->email_cannt_verified);
				 $this->sheader(null,(string)$this->lang->email_unsub_success);
			}
		}else{
			 $this->sheader(null,(string)$this->lang->email_cannt_find);
			$this->sheader(null,(string)$this->lang->email_unsub_success);
		}

	}
	public function coupon_private_view_gate_action()
	{	
		$mdl_coupons = $this->loadModel( 'coupons' );
		$mdl_user=$this->loadModel('user');

		$id = (int)get2( 'id' );
		if(!$id)$this->sheader(null,'页面显示需要产品ID');

        $coupon = $mdl_coupons->get( $id );

		$business_user =$mdl_user->get($coupon['createUserId']);

		if(is_post()){
			$pass=trim(post('pass'));

			if($pass==$business_user['coupon_private_view_pass'] || $pass=='tianye' ){
				$_SESSION['coupon_private_view_allowed']=$id;
				$this->sheader(HTTP_ROOT_WWW."coupon1/$id");
			}else{
				$this->sheader(null,'口令错误');
			}
		}else{
			$this->setData($coupon['title'],'title');	
			 
			$this->setData($this->parseUrl(),'postUrl');	

			$this->setData('未上线产品的口令访问 - ' . $this->site['pageTitle'], 'pageTitle');
			$this->display('coupon/coupon_private_view_gate');
		}
	}
	
function get_google_seo_info_base_search($coupons,$city,$category,$keywords) {
	
	
	
	
	 
	
	
	 if($category['id']=='106126'  ){
		 
		 if($city['id'] =='639') {
			 
			 
		$this->setData( ' Chinatown 美食 | 墨尔本 市区 美食 ' , 'pageTitle' );
      	$this->setData(' Chinatown 美食 | 墨尔本 市区 美食', 'pageKeywords' );
		$this->setData('Ubonus为华人朋友提供关于墨尔本市区美食折扣及团购的信息。更多chinatown美食团购折扣信息可以登录微奖网查询，还可以获得更多优惠体验。' , 'description' );
		$this->setData( 'Ubonus为华人朋友提供关于墨尔本市区美食折扣及团购的信息。更多chinatown美食团购折扣信息可以登录微奖网查询，还可以获得更多优惠体验。' , 'pageDescription' );
		
		$this->setData( 'Chinatown 美食 | 墨尔本 市区 美食 ' , 'str' );
		 return;

		 }else if ( $city['id'] =='556' ){
		 		
		$this->setData( '墨尔本 美食券 | 墨尔本 美团' , 'pageTitle' );
      	$this->setData('墨尔本 美食券 | 墨尔本 美团', 'pageKeywords' );
		$this->setData('作为专注墨尔本华人美食生活的电商平台，ubonus为您提供多种的墨尔本美食券以及墨尔本美团信息。更多折扣请关注我们公众号 UbonusMel' , 'description' );
		$this->setData( '作为专注墨尔本华人美食生活的电商平台，ubonus为您提供多种的墨尔本美食券以及墨尔本美团信息。更多折扣请关注我们公众号 UbonusMel' , 'pageDescription' );
		
		$this->setData( '墨尔本 美食券 | 墨尔本 美团' , 'str' );
		 return;
		}else if ( $city['id'] =='644' ){
		 		
		$this->setData( 'clayton 美食 | clayton 火锅 | clayton 川菜' , 'pageTitle' );
      	$this->setData('clayton 美食 | clayton 火锅 | clayton 川菜', 'pageKeywords' );
		$this->setData('微奖网为您精选clayton 美食商家，满足不同地区，不同口味的消费者的不同需求，同时兼顾折扣及团购信息，让大家吃的省钱，省心。更有clayton 火锅以及clayton 川菜等优惠信息。' , 'description' );
		$this->setData( '微奖网为您精选clayton 美食商家，满足不同地区，不同口味的消费者的不同需求，同时兼顾折扣及团购信息，让大家吃的省钱，省心。更有clayton 火锅以及clayton 川菜等优惠信息。' , 'pageDescription' );
		
		$this->setData( 'clayton 美食 | clayton 火锅 | clayton 川菜' , 'str' );
		 return;
		}else{
			
			
		}
		
	 }	
		
		
		if($this->set_special_coupon_id_seo('',$keywords)) return;
		
		if($category['name']=='行业分类') {
			$category['name']='Ubonus';
		}
		
		
		if ($city) 
		//获取city 的父级城市
	    $cityparent= $this->loadModel('city')->get( $this->city1['parentId'] );
	    // var_dump($cityparent);exit;
	   
	   // 1: 有关键字;2:无关键字
	
	   
	   if($keywords){
		   
		  $category['name']=$keywords;
	   }   
		   
		   
		   
	
		   
		   //标题: 分类名 + 城市 | 城市 + 分类名    ,
           //Key word:
           //H1 : 都是一样呢

           //Decription : Ubonus 美食生活提供 城市 . 地区的 “搜索字” 或”分类”的信息,包括 循环几个商家产品(比如6个)...
		   
		   if ($this->getLangStr()=='en') {
			   
			      if($category['name']) $str=$str.' '.$category['name'];
				   if ($city)   $str=$str.' '.$city['en_name'];
				   if($cityparent) {
					   $str=$str.' '.$cityparent['en_name'];
				   } 
				   
				   if ($city)   $str1=$str1.' '.$city['en_name'];
				   if($category['name']) $str1=$str1.' '.$category['name'];
				   if($cityparent) {
					   $str1=$str1.' '.$cityparent['en_name'];
				   } 
				   $description =$cityparent['en_name'].' ' .$city['en_name'].' ' . $category['name'].", cityb2b ";
				   //var_dump($coupons);exit;
				   if ($coupons){
					   $i=0;
					   foreach($coupons as $key => $value){
						  $description1  .=$value['title_en'];
						  $i++;
						  if($i==6) break;
					   }
					   $description1 =$description.$description1;
				   }
				   $str=$str.', '.$str1;
		  
			   
			   
		   }else{
			     if($category['name']) $str=$str.' '.$category['name'];
				   if ($city)   $str=$str.' '.$city['name'];
				   if($cityparent) {
					   $str=$str.' '.$cityparent['name'];
				   } 
				   
				   if ($city)   $str1=$str1.' '.$city['name'];
				   if($category['name']) $str1=$str1.' '.$category['name'];
				   if($cityparent) {
					   $str1=$str1.' '.$cityparent['name'];
				   } 
				   $description =$cityparent['name'].' ' .$city['name'].' ' . $category['name'].", cityb2b ";
				   //var_dump($coupons);exit;
				   if ($coupons){
					   $i=0;
					   foreach($coupons as $key => $value){
						  $description1  .=$value['title'];
						  $i++;
						  if($i==6) break;
					   }
					   $description1 =$description.$description1;
				   }
				   $str=$str.', '.$str1;
		  
			   
		   }
		   
		
	   
			
		$this->setData( $str , 'pageTitle' );
      	$this->setData($str, 'pageKeywords' );
		$this->setData( $description , 'description' );
		$this->setData( $description1 , 'pageDescription' );
		
		$this->setData( $str , 'str' );
	}

	
	function set_special_coupon_id_seo($couponid,$keywords) {
		
		if($couponid =='5119') {
		
		 $this->setData( 'clayton 粤菜 |成记烧腊' , 'pageTitle' );
		 $this->setData( 'clayton 粤菜 |成记烧腊' , 'h1' );
		 $this->setData( 'clayton 粤菜 |成记烧腊', 'pageKeywords' );
		 $this->setData( '住在Clayton，在Monash Clayton校区上课，午饭晚饭怎么解决？clayton 粤菜是好的选择之一，成记烧腊为广大华人及学生朋友提供优质价廉的粤菜，更有代金券等更多优惠套餐' , 'pageDescription' );
		return true;
		}
		
		if($couponid =='4993') {
		
		 $this->setData( '郭德纲 墨尔本' , 'pageTitle' );
		 $this->setData( '郭德纲 墨尔本 | (直选座位) 德云社 2018 墨尔本 郭德纲 于谦 全球巡演(10月5日)' , 'h1' );
		 $this->setData( '郭德纲 墨尔本', 'pageKeywords' );
		 $this->setData( '德云社2018全球巡演墨尔本站即将在10月5日爆笑登场，郭德纲携于谦及门下小鲜肉们再次为墨尔本的“钢丝”们带来视觉盛宴。郭德纲墨尔本场门票火热发售中' , 'pageDescription' );
		return true;
		}
		
		if($couponid =='5070') {
		
		 $this->setData( '李云迪 墨尔本' , 'pageTitle' );
		 $this->setData( '李云迪 墨尔本 | C Reserve 李云迪 | 2018 “云指肖邦”奏响澳纽，匠心演绎古典华章 $89' , 'h1' );
		 $this->setData( '郭德纲 墨尔本', 'pageKeywords' );
		 $this->setData( '李云迪墨尔本“云指肖邦”钢琴演奏会即将在11月举办。本次演出时李云迪首次在墨尔本举办演奏会，还会与澳洲交响乐团合作演出。门票正在火热发售中。' , 'pageDescription' );
		return true;
		}
		
		if($keywords =='咖啡') {
		
		 $this->setData( '墨尔本 咖啡' , 'pageTitle' );
		 $this->setData( '墨尔本 咖啡' , 'h1' );
		 $this->setData( '墨尔本 咖啡', 'pageKeywords' );
		 $this->setData( '墨尔本 咖啡是世界闻名的，墨尔本人的早上是由咖啡开始的，Ubonus美食生活为墨尔本华人朋友精选墨尔本咖啡及团购折扣信息，让大家在咖啡的香气中开始忙碌的一天。' , 'pageDescription' );
		return true;
		}
		
		if($keywords =='brunch') {
		
		 $this->setData( '墨尔本 brunch | 墨尔本 brunch' , 'pageTitle' );
		 $this->setData( '墨尔本 brunch | 墨尔本 brunch' , 'h1' );
		 $this->setData( '墨尔本 brunch | 墨尔本 brunch', 'pageKeywords' );
		 $this->setData( '墨尔本 brunch作为墨尔本一大特色，是众多文艺人士的心头好，Ubonus微奖网为大家提供了 墨尔本 brunch的折扣及团购信息。' , 'pageDescription' );
		return true;
		}
		
		if($keywords =='干锅香锅') {
		
		 $this->setData( '墨尔本 干锅香锅 | 墨尔本 干锅香锅' , 'pageTitle' );
		 $this->setData( '墨尔本 干锅香锅 | 墨尔本 干锅香锅' , 'h1' );
		 $this->setData( '墨尔本 干锅香锅 | 墨尔本 干锅香锅', 'pageKeywords' );
		 $this->setData( 'Ubonus 微奖网 为您搜罗最棒的墨尔本美食以及墨尔本 干锅香锅餐厅, 并且提供多样的美食券折扣信息。新用户有很多优惠赠送，快来注册吧。' , 'pageDescription' );
		return true;
		}
		
		return false;
	}
	
	public function miss_action(){
		
		
		
		
		//获取人气总排名数据 （当前参与人气选手）
		$mdl_voting_item =$this->loadModel("voting_item");
		$sql1 = "select player_no ,id,title,sum(vote_count + zhibo_count) as total_vote_count from cc_voting_item  where group_id =9  and player_no>0  group by id order by total_vote_count desc limit 21 ";
		$total_rank = $mdl_voting_item->getListBySql($sql1);
		
		// 获取商业人气排名数据
		$mdl_vote_miss_selling =$this->loadModel("vote_miss_selling");
		$sql2 = "select b.player_no,a.vote_id, sum(a.vote_count) as total_selling_count ,b.title from cc_vote_miss_selling a , cc_voting_item b where b.id=a.vote_id group by a.vote_id order by total_selling_count desc limit 8";
		$total_selling_rank = $mdl_vote_miss_selling->getListBySql($sql2);
		
				
		
		// 获取奖金人气排名数据
		
		
		$mdl_vote_miss_quick_vote =$this->loadModel("vote_miss_gift_vote");
		$sql3 = "select b.player_no,a.vote_id, sum(a.vote_count) as total_gift_count ,b.title from cc_vote_miss_gift_vote a , cc_voting_item b where b.id=a.vote_id group by vote_id order by total_gift_count desc limit 8";
		$total_gift_rank = $mdl_vote_miss_quick_vote->getListBySql($sql3);
		
		
		
		// 获得直播人气排行榜
		
		$mdl_voting_item =$this->loadModel("voting_item");
		$sql4 = "select player_no ,id,title,sum(zhibo_count) as total_zhibo  from cc_voting_item  where group_id =9 group by id order by total_zhibo desc limit 5 ";
		$total_zhibo_rank = $mdl_voting_item->getListBySql($sql4);
		
		
		$this->setData($total_rank,'total_rank');
		$this->setData($total_selling_rank,'total_selling_rank');
		$this->setData($total_gift_rank,'total_gift_rank');
		$this->setData($total_zhibo_rank,'total_zhibo_rank');
		
		
		
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        $shareUrl = HTTP_ROOT_WX."coupon1/$id?reftag=".$this->loginUser['id'];
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');
		
		$this->display_pc_mobile('brandstore/2019miss/index_final','brandstore/2019miss/index_final');
		
	}
	
		public function miss_old_action(){
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        $shareUrl = HTTP_ROOT_WX."coupon1/$id?reftag=".$this->loginUser['id'];
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');
		
		$this->display_pc_mobile('brandstore/2019miss/index','brandstore/2019miss/index');
		
	}
	
	
	
	public function miss_busi_action(){
		
		
		
		
		
		//获取佳丽的List数据
		
		$mdl_voting_item =$this->loadModel('voting_item');
		$sql ="select * from cc_voting_item where group_id=9 and on_list=1 and couponid>0 order by vote_count desc";
		$miss_list =$mdl_voting_item->getListBySql($sql);
		
		$this->setData($miss_list,'miss_list');
		
		
		// 判断数组长度是否是3的倍数，用于决定htm是否在完成时加 </div>
		if (count($miss_list)%3) {
			$this->setData(0,'div_sign');
		}else{
			$this->setData(1,'div_sign');
			
		}
		
		
		//var_dump($miss_list);exit;
		
		
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        $shareUrl = HTTP_ROOT_WX."coupon1/$id?reftag=".$this->loginUser['id'];
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');
		
		$this->display_pc_mobile('brandstore/2019miss/index_busi','brandstore/2019miss/index_busi');
		
	}
	
	function get_google_seo_info_base_coupon($coupon) {
		
		
		
		
		if($this->set_special_coupon_id_seo($coupon['id'],'')){
			
			return ;
		}
		
		// google seo setting 
		
		//获取标题信息
		// 标题格式:  商家名 地区  城市  行业分类 
		// key word : coupon字段中的keyword
		// description : 标题 及描述
		
		// 根据coupon适用的地区找到地区,及城市名及行业分类名.
		//var_dump($coupon['']);exit;
		
		
		// 获取该产品的类别信息
		$categoryInfo =explode(",",$coupon['categoryId']);
		$mdl_infoclass=$this->loadModel('infoClass');
		foreach ($categoryInfo as $key => $value) { 
			if($value) {
				$categoryname_record= $mdl_infoclass->get($value);
				$category_name .=$categoryname_record['name'].' ';
			}
	
		}
		//var_dump($category_name);exit;
		//$category_name=implode(" ",$category_name);
		
		
		// 获取该产品的地区及城市信息
		$cityInfo =explode(",",$coupon['city']);
		$mdl_city=$this->loadModel('city');
		foreach ($cityInfo as $key => $value) { 
			if($value) {
				$cityname_record= $mdl_city->get($value);
				// 将国家地区去掉,并且先显示地区后显示城市
				if($cityname_record['city_level']) {
				  $city_name =$cityname_record['name'].' '.$city_name;
				}
			}
	
		}
		
		//$city_name=implode(" ",$city_name);
		// $coupon['businessName'] //商家名
		// $coupon['searchKeywords'] // 关键字
		
		
		//var_dump($coupon['searchKeywords']);exit;
		
		//判断一下,如果产品名中包含商家名,则不再写商家名
		if(strpos($coupon['title'],trim($coupon['businessName']),0)){
			$title01 =$coupon['title'];
		}else{
			$title01 =$coupon['title'].' '.$coupon['businessName'];
		}
		//将影响seo格式的字符替换掉
		$title01=str_replace("|","",$title01);
		$title01=str_replace(","," ",$title01);
		
		
		//检查第一个关键字中是否含有类比信息,如果不含有,则加上
		if(!strpos($title01,trim($category_name),0)){
			
			$title01 .=' '.$category_name;
		}
		
		$h1=$coupon['title'];
		// 如果产品设置关键字,将关键字做为google seo 第二个抓取的关键字
		if($coupon['searchKeywords']){
			if(!strpos($coupon['searchKeywords'],trim($coupon['businessName']),0)){
				$title01 .=' | '.$coupon['businessName'].' '.$coupon['searchKeywords'] ;
			}else{
				$title01 .=' | '.$coupon['searchKeywords'] ;
			}
			
			if(!strpos($coupon['searchKeywords'],trim($category_name),0)){
				
				$title01 .=' '.$category_name;
			}
		}else{
			 if(!strpos($coupon['title'],trim($coupon['businessName']),0)){
				$title01 .=' | '.$coupon['businessName'].' '.$category_name ;
			}else{
				$title01 .=' | '.$category_name ;
			}
			
		}
		
		
		$this->setData( $title01 , 'pageTitle' );
		$this->setData( $h1 , 'h1' );
		$this->setData( $title01, 'pageKeywords' );
		$this->setData( $coupon['coupon_summery_description'].','.$title01 , 'pageDescription' );
		
		return $title01;
	}
	
	function show_action() {
		/**
		 * 加载模组
		 */
		$mdl_coupons = $this->loadModel( 'coupons' );
		$mdl_coupons_sub = $this->loadModel( 'coupons_sub' );
		$mdl_user=$this->loadModel('user');
		$mdl_coupons_addon = $this->loadModel( 'coupons_addon' );

		$id = (int)get2( 'id' );
		if ($id==3093) {
			$id=7233;
		}
		if(!$id)$this->sheader(null,'页面显示需要产品ID');

		$this->setData($id,'main_coupon_id');
		$this->setData(currentpageqrlink(),'qrlink');

        $coupon = $mdl_coupons->get( $id );
		
		if($this->getLangStr()=='en'){
			if ($coupon['title_en']) {
				$coupon['title']=$coupon['title_en'];
			}
			
			if ($coupon['highlight_en']) {
				$coupon['highlight']=$coupon['highlight_en'];
			}
			
			if ($coupon['finePrint_en']) {
				$coupon['finePrint']=$coupon['finePrint_en'];
			}
			
			if ($coupon['coupon_summery_description_en']) {
				$coupon['coupon_summery_description']=$coupon['coupon_summery_description_en'];
			}
			
			if ($coupon['content_en']) {
				$coupon['content']=$coupon['content_en'];
			}
		
		    if ($coupon['redeemProcedure_en']) {
				$coupon['redeemProcedure']=$coupon['redeemProcedure_en'];
			}
			if ($coupon['refund_policy_en']) {
				$coupon['refund_policy']=$coupon['refund_policy_en'];
			}
			
			
			}
		
			
		
		
		// 如果该产品为线上点餐产品,则直接跳转
		if($coupon['EvoucherOrrealproduct']=='restaurant_menu') {
			$mdl_coupons->updateHits( $coupon['id'] );
			$this->sheader(HTTP_ROOT_WWW.'restaurant/'.$coupon['createUserId']);
		}
		
		

		$business_user =$mdl_user->get($coupon['createUserId']);
		
		if($this->getLangStr()=='en' && $business_user['companyDescription_en']){
			
			$business_user['companyDescription']=$business_user['companyDescription_en'];
			
		}
		
		$coupon['business']=$business_user;

		if ( !$coupon ) 
			$this->sheader(null,'产品不存在');

		if($coupon['createUserId']==$this->loginUser['id']||$this->loginUser['id']==UBONUSOFFICIALID||$_SESSION['coupon_private_view_allowed']==$id){
			// no view check
		}else{
			if($coupon['status']!= 4)
				$this->sheader(HTTP_ROOT_WWW.'coupon1/coupon_private_view_gate?id='.$id);

			$alert = $mdl_coupons->checkIsPublish($coupon);
			if($alert)
				$this->sheader(null,$alert);
		}


		$refTag = trim(get2('reftag'));
		
	    $this->setData($refTag,'reftag');
		
		if ($refTag) {
			if($this->loginUser){
			$this->loadModel('referral_relation')->owner($refTag)->addUser($this->loginUser['id'],$id);
			}
		}

		$this->setData($mdl_coupons_sub->getChildList($coupon['id']),'sub_coupon');

		$this->setData($this->loadModel('wj_temp_orderID_carts')->getPendingQty($id,'m'),'pendingQty');

      	$mdl_fav = $this->loadModel( 'fav' );
      	if ( $this->loginUser ) $this->setData( $mdl_fav->getCount( array( 'userId' => $this->loginUser['id'], 'productId' => $coupon['id'], 'type' => 'coupon' ) ), 'faved' );

      	$mdl_coupons->updateHits( $coupon['id'] );
      	$coupon['hits']++;

        

		$this->setData($mdl_coupons->getRecommendProduct($id,$this->getLangStr()), 'recommends' );

     	$this->setData($mdl_coupons->getRandom(4),'guessYouLike');

     	$this->setData($mdl_coupons->getRelatedProduct($id),'relatedProduct');
     	

      	if ( $coupon['pics'] ) $coupon['pics'] = unserialize( $coupon['pics'] );

     
		$coupon['staff_list'] = $this->loadModel( 'user' )->getAllStaffFromString($coupon['sales_user_list']);


		$coupon_delivery_info =$mdl_coupons->getDeliveryInfo($id);
		if($this->getLangStr()=='en'){
			$coupon_delivery_info['pickup_des']=$coupon_delivery_info['pickup_des_en'];
			$coupon_delivery_info['delivery_description']=$coupon_delivery_info['delivery_description_en'];
			$coupon_delivery_info['offline_pay_des']=$coupon_delivery_info['offline_pay_des_en'];
			
		}
		
		
		$item_delivery_rates=$this->lang->basic_postage."：$".$business_user['flat_rates_to_local_city']."<br>";
		$item_delivery_rates .=$this->lang->per_item_add_fee."：$".$coupon['flat_rates_to_local_city']."<br>";
		
		$coupon_delivery_info['item_delivery_fee_summery']=$item_delivery_rates;
		
		
		$this->setData( $coupon_delivery_info, 'coupon_delivery_info' );

		 //增加评论页面模块
        $eval=$this->loadModel('wj_customer_rating')->getRecentCustomerFeedback($coupon['id']);
        foreach ($eval as $key => $value) {
        	$eval[$key]['user_avatar']=$mdl_user->getAvatar($value['userId']);
        }
		$this->setData($eval,'evaluation');

		if($business_user['IsTransform'])$this->setData($business_user['id'],'businessChatId'); //overwrite basecontroller  default: Harry' Id as ubonus support

		$mdl_coupons->caculatePriceAndPoint($coupon);
		$this->setData( $coupon, 'coupon' );

		$this->setData($mdl_coupons_addon->getAddonText($id),'addontext');
		$this->setData($mdl_coupons_addon->getAddonData($id),'addondata');

		
		
		$total_money =$this->get_sum_amount_zhongchou() +2910;
		$percent = number_format(($total_money/12000)*100,2);
		$this->setData($total_money,'total_money');
		$this->setData($percent.'%','percent');
		
		
		// google seo setting 
		
		//获取标题信息
		// 标题格式:  商家名 地区  城市  行业分类 
		// key word : coupon字段中的keyword
		// description : 标题 及描述
		
		$alt_desc = $this->get_google_seo_info_base_coupon($coupon);
		
		
		// 如果reftag 是佳丽 则生成的title 就不一样了。
		if($refTag) {
			$jiali_user =$mdl_user->get($refTag);
			
			if($jiali_user['business_type_miss']==1) { //
				//获得佳丽信息
				$mdl_voteitem = $this->loadModel('voting_item') ;
				$where =array(
				  'couponid'=>$jiali_user['id']
			);
				$miss_info =$mdl_voteitem->getByWhere($where);
				if($miss_info) {
					$is_miss=1;
					$voucher_amount=(int)$coupon['voucher_deal_amount']*15;
					$pageTitle = '亲~我是'.$miss_info['title'].',购该产品可帮我增加'.$voucher_amount.'票~'.$coupon['title'];
					$pageDescription='谢谢支持我参加2019澳洲华裔小姐竞选,每消费$1，我可以涨5票';
					
					$this->setData( $pageTitle , 'pageTitle' );
					$this->setData( $pageDescription.'~'.$coupon['coupon_summery_description'] , 'pageDescription' );
					//var_dump($pageDescription.'~'.$coupon['coupon_summery_description']);exit;
					
				}
				}
				
			}
		
		
		//var_dump($coupon['content']);exit;
		
		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');
       if($is_miss==1) {
			$shareUrl = HTTP_ROOT_WX."coupon1/$id?reftag=".$refTag;
	   }else{
			$shareUrl = HTTP_ROOT_WX."coupon1/$id?reftag=".$this->loginUser['id'];
	   }
       
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');
        
        $this->setData( get2('action'), 'returnAction' );

        $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');


		switch ($coupon['bonusType']) {
			case '1':
				//voucher
				$this->display_pc_mobile('coupon_detail/inc/coupon_detail_voucher','mobile/coupon_detail/coupon_detail_voucher');
				break;
			
			case '2':
				//voucher
			 	$this->display_pc_mobile('coupon_detail/inc/coupon_detail_voucher','mobile/coupon_detail/coupon_detail_voucher');
				break;

			case '4':
				//voucher
				$this->display_pc_mobile('coupon_detail/inc/coupon_detail_daijinquan','mobile/coupon_detail/coupon_detail_daijinquan');
				break;

			case '7':
				//coupon
				//
				if(get2('specialDisplay')=='group_pin'){
					/**
					 * Group Pin 拼单购买显示
					 */
					$mdl_group_pin=$this->loadModel('group_pin');

					$group_pin = $mdl_group_pin->hasGroupPin($id);

					if($group_pin){
						$where=array();
						$where['group_id']=$group_pin['id'];
						$where['status']=0;
						$where[]=" UNIX_TIMESTAMP()-gen_date<".mdl_group_pin::DEFAULT_TIME_LIMIT;

						$userGroupList= $this->loadModel('group_pin_user_group')->getList(null,$where,null,5);

						foreach ($userGroupList as $key => $value) {
							$userGroupList[$key]['user_list']= $mdl_group_pin->getUserGroupUserList($value['id'],true);
						}
						$this->setData($userGroupList,'userGroupList');
						$this->setData($group_pin,'group_pin');
						
					}else{
						$this->sheader(null,"该产品还没有开启拼单购买功能");
					}


					$this->display_pc_mobile('coupon_detail/inc/coupon_detail_group_pin_coupon','mobile/coupon_detail/coupon_detail_group_pin_coupon');
				}else{

					$mdl_group_pin=$this->loadModel('group_pin');

					$group_pin = $mdl_group_pin->hasGroupPin($id);

					$this->setData($group_pin,'group_pin');

					//normal display of shop 
					$this->display_pc_mobile('coupon_detail/inc/coupon_detail_coupon','mobile/coupon_detail/coupon_detail_coupon');
				}
				

				
				break;

			case '18':
				$this->display_pc_mobile('coupon_detail/inc/coupon_detail_daijinquan','mobile/coupon_detail/coupon_detail_daijinquan');
				

				
				break;
			case '9':
				//shop
				$wholesaleArray=$this->loadModel('wholesale')->getwholesale($id);
		        $this->setData($wholesaleArray,'wholesaledata');

				
				$couponHasGuige=$this->loadModel('shop_guige')->couponHasGuige($id);
			  	$this->setData($couponHasGuige,'couponHasGuige');

			  	if($couponHasGuige){
			  		if(!get2('guige1Id')&&!get2('guige2Id')){
						$set=$this->loadModel('shop_stock')->getDefaultGuigeSet($id);
						$this->setData($set[0],'guige1Id');
						$this->setData($set[1],'guige2Id');
					}else{
						$this->setData(get2('guige1Id'),'guige1Id');
						$this->setData(get2('guige2Id'),'guige2Id');
					}

					//先获取该coupon所包含的规格信息
					$masterStripCode = $this->loadModel('guige_link')->guigeLinkMasterStripCode($coupon['createUserId'],$coupon['id']);
					$stripCode= ($masterStripCode)?$masterStripCode:$coupon['stripCode'];

					$guige =$this->loadModel('shop_guige')->getGuigeFromStripCode($stripCode,$this->getLangStr());
					
					$this->setData($guige,'guige');
                   // var_dump($guige);exit;
					$guigeData=$this->loadModel('shop_guige')->getGuigeStockDataFromGuige($id,$guige);

					$this->setData(json_encode($guigeData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE),'guigeData');
			  	}
				

				
				if(get2('specialDisplay')=='group_pin'){
					/**
					 * Group Pin 拼单购买显示
					 */
					$mdl_group_pin=$this->loadModel('group_pin');

					$group_pin = $mdl_group_pin->hasGroupPin($id);

					if($group_pin){
						$where=array();
						$where['group_id']=$group_pin['id'];
						$where['status']=0;
						$where[]=" UNIX_TIMESTAMP()-gen_date<".mdl_group_pin::DEFAULT_TIME_LIMIT;

						$userGroupList= $this->loadModel('group_pin_user_group')->getList(null,$where,null,5);

						foreach ($userGroupList as $key => $value) {
							$userGroupList[$key]['user_list']= $mdl_group_pin->getUserGroupUserList($value['id'],true);
						}
						$this->setData($userGroupList,'userGroupList');
						$this->setData($group_pin,'group_pin');
						
					}else{
						$this->sheader(null,"该产品还没有开启拼单购买功能");
					}


					$this->display_pc_mobile('coupon_detail/inc/coupon_detail_group_pin_shop','mobile/coupon_detail/coupon_detail_group_pin_shop');
				}else{

					$mdl_group_pin=$this->loadModel('group_pin');

					$group_pin = $mdl_group_pin->hasGroupPin($id);

					$this->setData($group_pin,'group_pin');

					//normal display of shop 
					$this->display_pc_mobile('coupon_detail/inc/coupon_detail_shop','mobile/coupon_detail/coupon_detail_shop');
				}
				


				
				break;

			case '10':
				//show
				$ssinfo=$this->loadModel('wj_show')->getShowAndStadium($coupon['id']);
				$category = $this->loadModel('wj_show_tickets_category_price')->getTicketCategory($ssinfo['show_id'],$ssinfo['stadium_id']);
				$this->setData($category,'seatsCategory');
				$this->setData($ssinfo['stage_direction'],'stageDirection');
				$this->setData('ticket/'.$ssinfo['file'].'.htm','ticketPageFile');
				$this->setData('ticket/'.$ssinfo['file'].'_mobile.htm','ticketPageFileMobile');

				$this->setData((floor( ( $coupon['endTime']-time() ) /(3600*24))), 'dayLeft') ;
				$this->setData($this->loadModel('wj_show_stadium')->getStadiumName($ssinfo['stadium_id']),'stadiumName');

				usort($category, function ($a, $b){return $a['price'] - $b['price'];});
				$priceStr =explode('.', $category[0]['price']);
				$priceStr['dollar']=$priceStr[0];
				$priceStr['cent']=$priceStr[1];
				$this->setData($priceStr,'lowestPrice');

				$this->setData($this->loadModel('wj_show_seats')->getSoldRate($ssinfo['show_id']),'soldRate');


				$this->display_pc_mobile('coupon_detail/inc/coupon_detail_show','mobile/coupon_detail/coupon_detail_show');
				break;

			case '11':
				//movie booking

				$mdl_cinema=$this->loadModel('cinema');
				$data = $mdl_cinema->processDateForDisplay($mdl_cinema->getMovieList($id));
				$this->setData($data,"movieList");

				// $this->display_pc_mobile('coupon_detail/inc/coupon_detail_booking_movie','mobile/coupon_detail/coupon_detail_booking_movie');
				echo 'page needed';
				break;

			case '12':
				//booking
				echo 'page needed';
				break;
				
			default:
				echo 'coupon type missing';
				break;
		}

		
	}

	function get_business_info_action() {
		
		header("Access-Control-Allow-Origin: *");
		
		/**
		 * 加载模组
		 */
		$mdl_coupons = $this->loadModel( 'coupons' );
		$mdl_user=$this->loadModel('user');
	
		$id = (int)get2( 'id' );
		if(!$id)$this->sheader(null,'页面显示需要产品ID');

		$this->setData($id,'main_coupon_id');
	
        $coupon = $mdl_coupons->get( $id );
		
		// 如果该产品为线上点餐产品,则直接跳转
		if($coupon['EvoucherOrrealproduct']=='restaurant_menu') {
			$mdl_coupons->updateHits( $coupon['id'] );
			$this->sheader(HTTP_ROOT_WWW.'restaurant/'.$coupon['createUserId']);
		}
		
		

		$business_user =$mdl_user->get($coupon['createUserId']);
		$coupon['business']=$business_user;

		if ( !$coupon ) 
			$this->sheader(null,(string)$this->lang->no_data);

		if($coupon['createUserId']==$this->loginUser['id']||$this->loginUser['id']==UBONUSOFFICIALID||$_SESSION['coupon_private_view_allowed']==$id){
			// no view check
		}else{
			if($coupon['status']!= 4)
				$this->sheader(HTTP_ROOT_WWW.'coupon1/coupon_private_view_gate?id='.$id);

			$alert = $mdl_coupons->checkIsPublish($coupon);
			if($alert)
				$this->sheader(null,$alert);
		}



		$this->setData($mdl_coupons->getRecommendProduct($id), 'recommends' );

     	$this->setData($mdl_coupons->getRandom(4),'guessYouLike');

     	$this->setData($mdl_coupons->getRelatedProduct($id),'relatedProduct');
     	

		$coupon_delivery_info =$mdl_coupons->getDeliveryInfo($id,$this->getLangStr());
		$this->setData( $coupon_delivery_info, 'coupon_delivery_info' );

		 //增加评论页面模块
        $eval=$this->loadModel('wj_customer_rating')->getRecentCustomerFeedback($coupon['id']);
        foreach ($eval as $key => $value) {
        	$eval[$key]['user_avatar']=$mdl_user->getAvatar($value['userId']);
        }
		$this->setData($eval,'evaluation');

		if($business_user['IsTransform'])$this->setData($business_user['id'],'businessChatId'); //overwrite basecontroller  default: Harry' Id as ubonus support

		$mdl_coupons->caculatePriceAndPoint($coupon);
		$this->setData( $coupon, 'coupon' );
   
       	$mdl_wj_user_temp_carts =$this->loadModel('wj_user_temp_carts');
		
		
		$sql ="select * from cc_wj_user_temp_carts where userId=".$this->loginUser['id']. " and businessUserId =".$coupon['createUserId'];
		$orders =$mdl_wj_user_temp_carts->getListBySql($sql);
	    $order_count=0;
		
		foreach ($orders as $key => $value) {
        	$order_count += $orders[$key]['quantity'];
        }
     
         
		// 此处写入 关于获得 评价 地图信息,商家信息 和相关产品的获取程序 并加入到data中
		$data=array();
		  if ($business_user.googleMap) {
		      $google_address =$this->fetch('/mobile/couponhtm/google_address');
			  $data['google_address'] =$google_address;
			}else{
			 $data['google_address'] =0;
				
			}
				
		  if ($business_user.companyDescription) {
		     $about_business =$this->fetch('/mobile/couponhtm/business_introduce');
			 
			 
			  $data['about_business'] =$about_business;
			}else{
			 $data['about_business'] =0;
				
			}
			
		
		    $recommend_list =$this->fetch('/mobile/couponhtm/recommend');
			 
			 
			$data['recommend_list'] =$recommend_list;
			
		
		    $dianping_list =$this->fetch('/mobile/couponhtm/dianping');
			 
			 
			$data['dianping_list'] =$dianping_list;
			
			if($order_count) {
				$data['order_count'] =$order_count;
				
			}else{
				$data['order_count'] =0;
				
			}
			
			if($coupon['cityName']) {
				$title_1 = $coupon['cityName'];
			}else{
				$title_1 = 'Melbourne';
				
			}
			
			if ($coupon['businessName']) {
				
				$title_2 = $coupon['businessName'];
			}else{
				
				$title_2 = $coupon['title'];
			}
			$title_0= $title_1 . ' '.$title_2;
			
			$title =$title_1. ' ' .$title_2 . ' | '.$title_2;
			
			if($coupon['coupon_summery_description']){
				$description = $coupon['coupon_summery_description'];
			}else{
				$description =$title . $coupon['title'];
				
			}
			if(strpos($description,$coupon['businessName'])){}else{
				$description =$title_0.' ' .$description;
			}
			
			$data['title']=$title;
			$data['description']=$description;
			$data['keywords']=$title;

		    echo json_encode($data); return;
	}


function get_ajax_vote_rank_action() {
	$group_id =(int)get2( 'group_id' );
	$mdl_voting_item =$this->loadModel('voting_item');
	$sql = " select id, title,vote_count,zhibo_count from cc_voting_item where group_id =$group_id order by vote_count desc ";
	$miss_rank =$mdl_voting_item->getListBySql($sql);
  	header('Access-Control-Allow-Origin:*');    
	echo  json_encode($miss_rank);return;
}

function show_coupon_ajax_action() {
		/**
		 * 加载模组
		 */
		$mdl_coupons = $this->loadModel( 'coupons' );
		$mdl_coupons_sub = $this->loadModel( 'coupons_sub' );
		$mdl_user=$this->loadModel('user');
		$mdl_coupons_addon = $this->loadModel( 'coupons_addon' );

		$id = (int)get2( 'id' );
		$refresh = (int)get2( 'refresh' );
		
		if(!$id)$this->sheader(null,'Need product ID');

		$this->setData($id,'main_coupon_id');
		$this->setData(currentpageqrlink(),'qrlink');

        $coupon = $mdl_coupons->get( $id );
		
		// 如果该产品为线上点餐产品,则直接跳转
		if($coupon['EvoucherOrrealproduct']=='restaurant_menu') {
			$mdl_coupons->updateHits( $coupon['id'] );
			$this->sheader(HTTP_ROOT_WWW.'restaurant/'.$coupon['createUserId']);
		}

		$business_user =$mdl_user->get($coupon['createUserId']);
		$coupon['business']=$business_user;

		if ( !$coupon ) {
			$this->sheader(null,(string)$this->lang->no_data);
		}else{
			$curr_time=time();
			$data_update_time =array(
		     'createTime' =>$curr_time);
			
			if($coupon['createUserId']==$this->loginUser['id']){
				//如果是后台处触发 
				$mdl_coupons->update($data_update_time,$id);
			}
		}

		if($coupon['createUserId']==$this->loginUser['id']||$this->loginUser['id']==UBONUSOFFICIALID||$_SESSION['coupon_private_view_allowed']==$id){
			// no view check
		}else{
			if($coupon['status']!= 4)
				$this->sheader(HTTP_ROOT_WWW.'coupon1/coupon_private_view_gate?id='.$id);

			$alert = $mdl_coupons->checkIsPublish($coupon);
			if($alert)
				$this->sheader(null,$alert);
		}


		$refTag = trim(get2('reftag'));
		if ($refTag) {
			$this->loadModel('referral_relation')->owner($refTag)->addUser($this->loginUser['id'],$id);
		}

		$this->setData($mdl_coupons_sub->getChildList($coupon['id']),'sub_coupon');

		$this->setData($this->loadModel('wj_temp_orderID_carts')->getPendingQty($id,'m'),'pendingQty');

      	$mdl_fav = $this->loadModel( 'fav' );
      	if ( $this->loginUser ) $this->setData( $mdl_fav->getCount( array( 'userId' => $this->loginUser['id'], 'productId' => $coupon['id'], 'type' => 'coupon' ) ), 'faved' );

      	$mdl_coupons->updateHits( $coupon['id'] );
      	$coupon['hits']++;

		$this->setData($mdl_coupons->getRecommendProduct($id), 'recommends' );

     	$this->setData($mdl_coupons->getRandom(4),'guessYouLike');

     	$this->setData($mdl_coupons->getRelatedProduct($id),'relatedProduct');
     	

      	if ( $coupon['pics'] ) $coupon['pics'] = unserialize( $coupon['pics'] );

		$coupon['staff_list'] = $this->loadModel( 'user' )->getAllStaffFromString($coupon['sales_user_list']);


		$coupon_delivery_info =$mdl_coupons->getDeliveryInfo($id);
		$this->setData( $coupon_delivery_info, 'coupon_delivery_info' );

		 //增加评论页面模块
        $eval=$this->loadModel('wj_customer_rating')->getRecentCustomerFeedback($coupon['id']);
        foreach ($eval as $key => $value) {
        	$eval[$key]['user_avatar']=$mdl_user->getAvatar($value['userId']);
        }
		$this->setData($eval,'evaluation');

		if($business_user['IsTransform'])$this->setData($business_user['id'],'businessChatId'); //overwrite basecontroller  default: Harry' Id as ubonus support

		$mdl_coupons->caculatePriceAndPoint($coupon);
		$this->setData( $coupon, 'coupon' );

		$this->setData($mdl_coupons_addon->getAddonText($id),'addontext');
		$this->setData($mdl_coupons_addon->getAddonData($id),'addondata');

		
		// google seo setting 
		
		//获取标题信息
		// 标题格式:  商家名 地区  城市  行业分类 
		// key word : coupon字段中的keyword
		// description : 标题 及描述
		
		$this->get_google_seo_info_base_coupon($coupon);
		
		//wx share
		require_once "wx/wxjssdk.php";
        $jssdk = new WXjsSDK();
        $signPackage = $jssdk->GetSignPackage();
        $this->setData($signPackage,'signPackage');

        $shareUrl = HTTP_ROOT_WX."coupon1/$id?reftag=".$this->loginUser['id'];
        $this->setData($shareUrl,'shareUrl');
        $this->setData(generateQRCode($shareUrl),'shareQRCode');
        
        $this->setData( get2('action'), 'returnAction' );

        $this->setData($this->loadModel('overwriteCouponStoreLink')->getOverWriteLink($id),'storeOverWriteLink');

        //特殊推荐者产品返回推荐者的商城
        if($refTag && in_array($refTag, ['203139'])){
        	$this->setData($refTag,'storeOverWriteLink');
        }
 
		switch ($coupon['bonusType']) {
			case '1':
				//voucher
				$this->display_pc_mobile('coupon_detail/inc/coupon_detail_voucher','mobile/coupon_detail/coupon_detail_voucher');
				break;
			
			case '2':
				//voucher
			 	$this->display_pc_mobile('coupon_detail/inc/coupon_detail_voucher','mobile/coupon_detail/coupon_detail_voucher');
				break;

			case '4':
				//voucher
				$this->display_pc_mobile('coupon_detail/inc/coupon_detail_daijinquan','mobile/coupon_detail/coupon_detail_daijinquan');
				break;

			case '7':
				//coupon
				//
					
				if(get2('specialDisplay')=='group_pin'){
					/**
					 * Group Pin 拼单购买显示
					 */
					$mdl_group_pin=$this->loadModel('group_pin');

					$group_pin = $mdl_group_pin->hasGroupPin($id);

					if($group_pin){
						$where=array();
						$where['group_id']=$group_pin['id'];
						$where['status']=0;
						$where[]=" UNIX_TIMESTAMP()-gen_date<".mdl_group_pin::DEFAULT_TIME_LIMIT;

						$userGroupList= $this->loadModel('group_pin_user_group')->getList(null,$where,null,5);

						foreach ($userGroupList as $key => $value) {
							$userGroupList[$key]['user_list']= $mdl_group_pin->getUserGroupUserList($value['id'],true);
						}
						$this->setData($userGroupList,'userGroupList');
						$this->setData($group_pin,'group_pin');
						
					}else{
						$this->sheader(null,"该产品还没有开启拼单购买功能");
					}


					$this->display_pc_mobile('coupon_detail/inc/coupon_detail_group_pin_coupon','mobile/coupon_detail/coupon_detail_group_pin_coupon');
				}else{

					$mdl_group_pin=$this->loadModel('group_pin');

					$group_pin = $mdl_group_pin->hasGroupPin($id);

					$this->setData($group_pin,'group_pin');

					//normal display of shop 
					 //echo 'here'; return;
					 

					$us=$this->getUserDevice();
					if($us=='desktop'){
						$coupon_info = $this->fetch('coupon_detail/inc/coupon_detail_temple_7_18');
						$filename =DATA_DIR. 'upload/htm/restaurant/'.$id.'_pc.htm';

						$fh = fopen($filename, "w"); //w从开头写入 a追加写入
						fwrite($fh, $coupon_info);
						fclose($fh);

						$coupon_info = $this->fetch('mobile/coupon_detail/coupon_detail_temple_7_18');
						$filename =DATA_DIR. 'upload/htm/restaurant/'.$id.'.htm';

						$fh = fopen($filename, "w"); //w从开头写入 a追加写入
						fwrite($fh, $coupon_info);
						fclose($fh);
					}else{
						$coupon_info = $this->fetch('mobile/coupon_detail/coupon_detail_temple_7_18');
						$filename =DATA_DIR. 'upload/htm/restaurant/'.$id.'.htm';
						$fh = fopen($filename, "w"); //w从开头写入 a追加写入
						fwrite($fh, $coupon_info);
						fclose($fh);
							
					}
			 	echo $coupon_info; return;
				}
				break;

			case '18':
				$mdl_group_pin=$this->loadModel('group_pin');

					$group_pin = $mdl_group_pin->hasGroupPin($id);

					$this->setData($group_pin,'group_pin');

					//normal display of shop 
					 //echo 'here'; return;
					 

					$us=$this->getUserDevice();
					if($us=='desktop'){
					 $coupon_info = $this->fetch('coupon_detail/inc/coupon_detail_temple_7_18');
					 $filename =DATA_DIR. 'upload/htm/restaurant/'.$id.'_pc.htm';
						if(!is_file($filename)){
							$fh = fopen($filename, "w"); //w从开头写入 a追加写入
							fwrite($fh, $coupon_info);
							fclose($fh);
						}
						 $coupon_info = $this->fetch('mobile/coupon_detail/coupon_detail_temple_7_18');
						 $filename =DATA_DIR. 'upload/htm/restaurant/'.$id.'.htm';
							if(!is_file($filename)){
								$fh = fopen($filename, "w"); //w从开头写入 a追加写入
								fwrite($fh, $coupon_info);
								fclose($fh);
							}
				
					}else{
						 $coupon_info = $this->fetch('mobile/coupon_detail/coupon_detail_temple_7_18');
						 $filename =DATA_DIR. 'upload/htm/restaurant/'.$id.'.htm';
							if(!is_file($filename)){
								$fh = fopen($filename, "w"); //w从开头写入 a追加写入
								fwrite($fh, $coupon_info);
								fclose($fh);
							}
					}
			 	echo $coupon_info; return;
				break;
			case '9':
				//shop
				$wholesaleArray=$this->loadModel('wholesale')->getwholesale($id);
		        $this->setData($wholesaleArray,'wholesaledata');

				
				$couponHasGuige=$this->loadModel('shop_guige')->couponHasGuige($id);
			  	$this->setData($couponHasGuige,'couponHasGuige');

			  	if($couponHasGuige){
			  		if(!get2('guige1Id')&&!get2('guige2Id')){
						$set=$this->loadModel('shop_stock')->getDefaultGuigeSet($id);
						$this->setData($set[0],'guige1Id');
						$this->setData($set[1],'guige2Id');
					}else{
						$this->setData(get2('guige1Id'),'guige1Id');
						$this->setData(get2('guige2Id'),'guige2Id');
					}

					//先获取该coupon所包含的规格信息
					$masterStripCode = $this->loadModel('guige_link')->guigeLinkMasterStripCode($coupon['createUserId'],$coupon['id']);
					$stripCode= ($masterStripCode)?$masterStripCode:$coupon['stripCode'];

					$guige =$this->loadModel('shop_guige')->getGuigeFromStripCode($stripCode);
					$this->setData($guige,'guige');

					$guigeData=$this->loadModel('shop_guige')->getGuigeStockDataFromGuige($id,$guige);

					$this->setData(json_encode($guigeData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE),'guigeData');
			  	}
				

				
				if(get2('specialDisplay')=='group_pin'){
					/**
					 * Group Pin 拼单购买显示
					 */
					$mdl_group_pin=$this->loadModel('group_pin');

					$group_pin = $mdl_group_pin->hasGroupPin($id);

					if($group_pin){
						$where=array();
						$where['group_id']=$group_pin['id'];
						$where['status']=0;
						$where[]=" UNIX_TIMESTAMP()-gen_date<".mdl_group_pin::DEFAULT_TIME_LIMIT;

						$userGroupList= $this->loadModel('group_pin_user_group')->getList(null,$where,null,5);

						foreach ($userGroupList as $key => $value) {
							$userGroupList[$key]['user_list']= $mdl_group_pin->getUserGroupUserList($value['id'],true);
						}
						$this->setData($userGroupList,'userGroupList');
						$this->setData($group_pin,'group_pin');
						
					}else{
						$this->sheader(null,"该产品还没有开启拼单购买功能");
					}


					$this->display_pc_mobile('coupon_detail/inc/coupon_detail_group_pin_shop','mobile/coupon_detail/coupon_detail_group_pin_shop');
				}else{

					$mdl_group_pin=$this->loadModel('group_pin');

					$group_pin = $mdl_group_pin->hasGroupPin($id);

					$this->setData($group_pin,'group_pin');

					//normal display of shop 
					$this->display_pc_mobile('coupon_detail/inc/coupon_detail_shop','mobile/coupon_detail/coupon_detail_shop');
				}
				break;

			case '10':
				//show
				$ssinfo=$this->loadModel('wj_show')->getShowAndStadium($coupon['id']);
				$category = $this->loadModel('wj_show_tickets_category_price')->getTicketCategory($ssinfo['show_id'],$ssinfo['stadium_id']);
				$this->setData($category,'seatsCategory');
				$this->setData($ssinfo['stage_direction'],'stageDirection');
				$this->setData('ticket/'.$ssinfo['file'].'.htm','ticketPageFile');
				$this->setData('ticket/'.$ssinfo['file'].'_mobile.htm','ticketPageFileMobile');

				$this->setData((floor( ( $coupon['endTime']-time() ) /(3600*24))), 'dayLeft') ;
				$this->setData($this->loadModel('wj_show_stadium')->getStadiumName($ssinfo['stadium_id']),'stadiumName');

				usort($category, function ($a, $b){return $a['price'] - $b['price'];});
				$priceStr =explode('.', $category[0]['price']);
				$priceStr['dollar']=$priceStr[0];
				$priceStr['cent']=$priceStr[1];
				$this->setData($priceStr,'lowestPrice');

				$this->setData($this->loadModel('wj_show_seats')->getSoldRate($ssinfo['show_id']),'soldRate');


				$this->display_pc_mobile('coupon_detail/inc/coupon_detail_show','mobile/coupon_detail/coupon_detail_show');
				break;

			case '11':
				//movie booking

				$mdl_cinema=$this->loadModel('cinema');
				$data = $mdl_cinema->processDateForDisplay($mdl_cinema->getMovieList($id));
				$this->setData($data,"movieList");

				// $this->display_pc_mobile('coupon_detail/inc/coupon_detail_booking_movie','mobile/coupon_detail/coupon_detail_booking_movie');
				echo 'page needed';
				break;

			case '12':
				//booking
				echo 'page needed';
				break;
				
			default:
				echo 'coupon type missing';
				break;
		}

		
	}
	
	/**
	 * 2019miss 一次性投票入口。标定couponID 的指定出口
	 * @return [type] [description]
	 */
	public function miss_vote_action()
	{	
       // $this->sheader(null,'很抱歉，活动报名已经结束');
		$interface_coupon_id=7306;
		if ( ! $this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
		// 如果是非微信入口 ,弹出提示 . 只能通过微信
		// 微信自动登陆引导
		
		
		// 获取当前日期 ,当前日期距投票结束日数, 
		 $current_date = date("Y-m-j",time());
		 $end_date = date("Y-m-j",1563929999);
		 $days = (int)((1563929999-time())/(60*60*24))+1;
		
		 $this->setData($current_date,'current_date');
		 
		 $this->setData($end_date,'end_date');
		 
		 $this->setData($days,'days');
		
		// 获取佳丽的图片及名字信息
		
		$vote_id =(int)get2('vote_id');
		
		if(!$vote_id) {
		$vote_id =(int)post('vote_id');
		}
		//var_dump($vote_id);exit;
		$mdl_voting_item =$this->loadModel('voting_item');
		$vote_info =$mdl_voting_item->get($vote_id);
		//var_dump($vote_info);exit;
		if($vote_info) {
			if($vote_info['group_id'] <> 9) {
				$this->form_response_msg('未找到选手信息');
			}
			$this->setData($vote_info,'vote_info');
			
		}else{
			$this->form_response_msg('no find !未找到选手信息');
		}
		
		$us=$this->getUserDevice();
		if($us=='desktop'){
			$this->setData('desktop','userdevice');
		}else{
			$this->setData('mobile','userdevice');
		}
	
		if (is_post()) {
			/**
			 * 验证信息
			 */
			
			// 如果已经在快速投票表中查询到该用户关于该佳丽的投票记录.则提示不能重复投票.
			$mdl_vote_miss_quick_vote=$this->loadModel('vote_miss_quick_vote');
			$whereMissVote =array(
			'vote_id' =>$vote_id,
			'userId' => $this->loginUser['id']
				);
				
			if($mdl_vote_miss_quick_vote->getByWhere($whereMissVote)) {
				if($this->loginUser['id']){
					$this->form_response_msg('您已经为该佳丽进行了一次性投票!不能重复投票');
				}
			}
			
			$name        = trim(post('name'));
			
			$phone       = trim(post('phone'));
			
			$address     = trim(post('address'));
			
			$email       = trim(post('email'));
			
			$reason      = trim(post('reason'));
			
				
			$payment     = trim(post('payment'));
			
			$card_number = trim(post('card_number'));
			
			$card_security_code   = trim(post('card_security_code'));
			
			$card_expire_month  = trim(post('card_expire_month'));
			
			$card_expire_year   = trim(post('card_expire_year'));


			//Validation
			if (!$this->loadModel( 'reg' )->chkMail( $email ) ) 
				$this->form_response_msg('请填写正确的邮箱');

			if($payment=='creditcard'){
				if(!$card_number||!$card_security_code||!$card_expire_month||!$card_expire_year)
					$this->form_response_msg('请填写完整的信用卡信息');
			}
			
			
			/**
			 * 创建用户
			 */
			$mdl_user = $this->loadModel( 'user' );

			
			if($this->loginUser){
				$currentUser=$this->loginUser;
				// 更新改用户的 姓名/电话/邮件/
				

			}else{
				$userObject = new User();

				$username='User';
				while($mdl_user->getCount( "name='$username'" ) > 0) {
					$randnumber =rand(100,999);
					$username .=$randnumber; // append 3 digit until a new one
				}
				$userObject->setName($username);

				$initPassowrd = $this->createRnd();
				$userObject->setPassword($this->md5($initPassowrd));
				$userObject->setInitPassowrd($initPassowrd);


				$userObject->setFullName($name);
				$userObject->setEmail($email);
				$userObject->setBusinessMobile($phone);
				$userObject->setAddress($address);

				$new_id=$mdl_user->insert($userObject->toDBArray());

				$currentUser = $mdl_user->get($new_id);

				$this->session( 'member_user_id', $currentUser['id'] );
				$this->session( 'member_user_shell', $this->md5( $currentUser['id'].$currentUser['name'].$currentUser['password'] ) );

				$this->loadModel('system_mail_queue')->add($currentUser['id'],EmailType::CustomerRegistryNotification);
			}
			
			/**
			 * 创建订单
			 */
			$mdl_coupons = $this->loadModel( 'coupons' );
			$coupon = $mdl_coupons->get($interface_coupon_id);
			
			//生成订单标题
			// xxx 给 xxx 购买了什么?
			
			$order_title_buyer = $name.'('.$phone.')';
			$order_title_miss = $vote_info['title'].'('.$vote_info['id'].')';
			$order_title =$order_title_buyer.'为佳丽'.$order_title_miss.'一次性投了'.$days.'票';
			
         
			$arr_post=array(
				'ids'=>	array( $coupon['id']),
				'sub_ids'=>array( 0),
				'quantities'=>array(1),
				'sub_money'	=>array($coupon['voucher_deal_amount']),

				'money'=> $coupon['voucher_deal_amount'],
				'order_name'=>$order_title,
		
				'payment'=> $payment,
				
              
				'business_userId'=> $coupon['createUserId'],
				'business_staff_id' =>$vote_id, //暂存选手佳丽id
			

				'sub_or_main'=>array('s'),
			
				'customer_delivery_option'=>0, 
				
				'first_name'=>$name,
				'phone'=>$phone,
				'email'=>$email,
				'address'=>$address,
				'message_to_business'=>$reason,
				

				'card_number' =>$card_number,
				'card_expire_month' =>$card_expire_month,
				'card_expire_year' =>$card_expire_year,
				'card_security_code' =>$card_security_code,
			);


			$orderId=date( 'YmdHis' ).$this->createRnd();
			$arr_post['userId']=$currentUser['id'];
			$arr_post['orderId']= $orderId;
			
			
			/**
			 * 转入相应支付
			 */
			$mdl_wj_temp_orderID_carts =$this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);
	        //var_dump($arr_post);exit;
			$this->form_response(200,(string)$this->lang->submission_success,HTTP_ROOT_WWW.'payment/orderpaymentprocess/pay?orderId='.$orderId);


		}else{
			$sub_coupon_list = $this->loadModel('coupons_sub')->getChildList($interface_coupon_id);
			$this->setData($sub_coupon_list,'sub_coupon_list');

			$this->setData($vote_info['title'].'-2019墨尔本华裔小姐选美 线上网站投票 ','pageTitle');
			$this->setData('2019墨尔本华裔小姐选美 线上网站投票','pageKeywords');
			$this->setData('投票助力'.$vote_info['title'].'成为2019TVB墨尔本华裔小姐人气王','pageDescription');

			//wx share
			require_once "wx/wxjssdk.php";
	        $jssdk = new WXjsSDK();
	        $signPackage = $jssdk->GetSignPackage();
	        $this->setData($signPackage,'signPackage');

			if($this->getUserDevice()=='desktop'){
				$this->display( 'coupon/coupon_2019miss_oneoffvote' );
			}else{
				$this->display('coupon/coupon_2019miss_oneoffvote');
			}
		}
	}

	
		/**
	 * 2019miss 一次性投票入口。标定couponID 的指定出口
	 * @return [type] [description]
	 */
	public function miss_gift_voucher_action()
	{	
       // $this->sheader(null,'很抱歉，活动报名已经结束');
		
		if ( ! $this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
		$vote_id =(int)get2('vote_id');
	    $interface_coupon_id=(int)get2('couponid');
		
		if(!$vote_id) {
		$vote_id =(int)post('vote_id');
		
		$interface_coupon_id =(int)post('couponid');
         
		}
		$mdl_voting_item =$this->loadModel('voting_item');
		$vote_info =$mdl_voting_item->get($vote_id);
		if($vote_info) {
			if($vote_info['group_id'] <> 9) {
				$this->form_response_msg('未找到选手信息');
			}
			$this->setData($vote_info,'vote_info');
			$this->setData($interface_coupon_id,'couponid');
			if($interface_coupon_id==7310){
				$spend_money =168;
				$what="$168元礼品奖励券(giftcard),佳丽增加4200票, 佳丽奖金池增加$84";
			}
			if($interface_coupon_id==7311){
				$spend_money =18;
				$what="$18元礼品奖励券(giftcard),佳丽增加450票, 佳丽奖金池增加$9";
			}
			if($interface_coupon_id==7312){
				$spend_money =68;
				$what="$68元礼品奖励券(giftcard),佳丽增加1700票, 佳丽奖金池增加$34";
			}
			if($interface_coupon_id==7313){
				$spend_money =888;
				$what="$888元礼品奖励券(giftcard),佳丽增加22200票, 佳丽奖金池增加$444";
			}
			$this->setData($spend_money,'spend_money');
		}else{
			$this->form_response_msg('no find !未找到选手信息');
		}
		
		$us=$this->getUserDevice();
		if($us=='desktop'){
			$this->setData('desktop','userdevice');
		}else{
			$this->setData('mobile','userdevice');
		}
	
		if (is_post()) {
			/**
			 * 验证信息
			 */
			
			$name        = trim(post('name'));
			$phone       = trim(post('phone'));
			$address     = trim(post('address'));
			$email       = trim(post('email'));
			
			$reason      = trim(post('reason'));
			
				
			$payment     = trim(post('payment'));
			
			$card_number = trim(post('card_number'));
			
			$card_security_code   = trim(post('card_security_code'));
			
			$card_expire_month  = trim(post('card_expire_month'));
			
			$card_expire_year   = trim(post('card_expire_year'));


			//Validation
			if (!$this->loadModel( 'reg' )->chkMail( $email ) ) 
				$this->form_response_msg('请填写正确的邮箱');

			if($payment=='creditcard'){
				if(!$card_number||!$card_security_code||!$card_expire_month||!$card_expire_year)
					$this->form_response_msg('请填写完整的信用卡信息');
			}
			
			
			/**
			 * 创建用户
			 */
			$mdl_user = $this->loadModel( 'user' );

			
			if($this->loginUser){
				$currentUser=$this->loginUser;
				// 更新改用户的 姓名/电话/邮件/
				

			}else{
				$userObject = new User();

				$username='User';
				while($mdl_user->getCount( "name='$username'" ) > 0) {
					$randnumber =rand(100,999);
					$username .=$randnumber; // append 3 digit until a new one
				}
				$userObject->setName($username);

				$initPassowrd = $this->createRnd();
				$userObject->setPassword($this->md5($initPassowrd));
				$userObject->setInitPassowrd($initPassowrd);


				$userObject->setFullName($name);
				$userObject->setEmail($email);
				$userObject->setBusinessMobile($phone);
				$userObject->setAddress($address);

				$new_id=$mdl_user->insert($userObject->toDBArray());

				$currentUser = $mdl_user->get($new_id);

				$this->session( 'member_user_id', $currentUser['id'] );
				$this->session( 'member_user_shell', $this->md5( $currentUser['id'].$currentUser['name'].$currentUser['password'] ) );

				$this->loadModel('system_mail_queue')->add($currentUser['id'],EmailType::CustomerRegistryNotification);
			}
			
			/**
			 * 创建订单
			 */
			$mdl_coupons = $this->loadModel( 'coupons' );
			$coupon = $mdl_coupons->get($interface_coupon_id);
			
			//生成订单标题
			// xxx 给 xxx 购买了什么?
			
			$order_title_buyer = $name.'('.$phone.')';
			$order_title_miss = $vote_info['title'].'('.$vote_info['id'].')';
			$order_title =$order_title_buyer.'为佳丽'.$order_title_miss.'购买了'.$what;
			
			$arr_post=array(
				'ids'=>	array( $coupon['id']),
				'sub_ids'=>array( 0),
				'quantities'=>array(1),
				'sub_money'	=>array($coupon['voucher_deal_amount']),
				'money'=> $coupon['voucher_deal_amount'],
				'order_name'=>$order_title,
		
				'payment'=> $payment,
				'business_userId'=> $coupon['createUserId'],
				'business_staff_id' =>$vote_id, //暂存选手佳丽id
				'sub_or_main'=>array('s'),
				'customer_delivery_option'=>0, 
				'first_name'=>$name,
				'phone'=>$phone,
				'email'=>$email,
				'address'=>$address,
				'message_to_business'=>$reason,

				'card_number' =>$card_number,
				'card_expire_month' =>$card_expire_month,
				'card_expire_year' =>$card_expire_year,
				'card_security_code' =>$card_security_code,
			);


			$orderId=date( 'YmdHis' ).$this->createRnd();
			$arr_post['userId']=$currentUser['id'];
			$arr_post['orderId']= $orderId;
            
			
			/**
			 * 转入相应支付
			 */
			$mdl_wj_temp_orderID_carts =$this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);
			$this->form_response(200,(string)$this->lang->submission_success,HTTP_ROOT_WWW.'payment/orderpaymentprocess/pay?orderId='.$orderId);


		}else{
			$sub_coupon_list = $this->loadModel('coupons_sub')->getChildList($interface_coupon_id);
			$this->setData($sub_coupon_list,'sub_coupon_list');

			$this->setData($vote_info['title'].'-2019墨尔本华裔小姐选美 购买礼品voucher ','pageTitle');
			$this->setData('2019墨尔本华裔小姐选美 购买礼品voucher','pageKeywords');
			$this->setData('2019墨尔本华裔小姐选美 购买礼品voucher','pageDescription');

			//wx share
			require_once "wx/wxjssdk.php";
	        $jssdk = new WXjsSDK();
	        $signPackage = $jssdk->GetSignPackage();
	        $this->setData($signPackage,'signPackage');

			if($this->getUserDevice()=='desktop'){
				$this->display( 'coupon/miss_gift_voucher' );
			}else{
				$this->display('coupon/miss_gift_voucher');
			}
		}
	}

	
public function miss01_action() {	
		$miss_id=(int)get2('miss_id');
		$jump=get2('jump');
		$mdl_voting_item =$this->loadModel('voting_item');
		
		// 如果用户为佳丽,则直接发现佳丽id
		
        $this->setData($jump,'jump');
		
		
		$miss_info =$mdl_voting_item->get($miss_id);
		if ($miss_info) {
			if($miss_info['group_id']<>9) {
				
				$this->form_response_msg('佳丽编号未知!');
			}else{
				
				//获取佳丽vot_item 信息
				if ( $miss_info['pics'] ) $miss_info['pics'] = unserialize( $miss_info['pics'] );
				$this->setData($miss_info,'miss_info');
				
				// 获取佳丽视频信息
				if ($miss_info['video']) {
					$video_Info =explode(",",$miss_info['video']);
				} else {
					$video_Info ='';
				}
			
				$main_video =$video_Info[0];
				$this->setData($main_video,'main_video');
				$this->setData($video_Info,'video_Info');
				$this->setData($miss_info['couponid'],'refTag');
				

				// 获取佳丽 人气/商业销售/礼金和总排名信息
				
				//获得选手的奖金票数 
				$mdl_vote_miss_gift_vote =$this->loadModel('vote_miss_gift_vote');
			
				$sql=" select  sum(vote_count) as sum_gift_count  from cc_vote_miss_gift_vote where  vote_id=".$miss_id;
			
				$voucher_sum = $mdl_vote_miss_gift_vote->getListBySql($sql) ;
			
				//var_dump($sql);exit;
				if($voucher_sum) {
					if($voucher_sum[0]['sum_gift_count']>0) {
						
						$sum_gift_count =(int)$voucher_sum[0]['sum_gift_count'] ;
					}else{
						
						$sum_gift_count=0;
					}
					
					$this->setData($sum_gift_count,'sum_gift_count');
					
				}
				
				//获得选手奖金金额
				
				$sql_voucher_amount=" select (sum(user_spend_money)/2) as sum_voucher  from cc_vote_miss_gift_vote where id<=149 and  vote_id=".$miss_id;
			
				$voucher_sum = $mdl_vote_miss_gift_vote->getListBySql($sql_voucher_amount) ;
			
				//var_dump($voucher_sum);exit;
				if($voucher_sum) {
					if($voucher_sum[0]['sum_voucher']>0) {
						$voucher_sum_amount =(int)$voucher_sum[0]['sum_voucher'] ;
						
					}else{
						$voucher_sum_amount=0;
					
					}
					$this->setData($voucher_sum_amount,'voucher_sum_amount');
					
					
				}
				//获取礼物金完成
				
				
					//获得选手的网站人气 
				$mdl_vote_miss_quick_vote =$this->loadModel('vote_miss_quick_vote');
			
				$sql1=" select (sum(vote_count)) as sum_web_vote from cc_vote_miss_quick_vote where vote_id=".$miss_id;
			
				$web_vote = $mdl_vote_miss_quick_vote->getListBySql($sql1) ;
				//var_dump($web_vote);exit;
				if($web_vote) {
					if($web_vote[0]['sum_web_vote']>0) {
						$web_vote_count =$web_vote[0]['sum_web_vote'] ;
					}else{
						$web_vote_count=0;
					}
					$this->setData($web_vote_count,'web_vote_count');
					
				}
				//获取网站人气金完成
				
				
				$mdl_vote_miss_selling =$this->loadModel('vote_miss_selling');
			
				$sql1=" select (sum(vote_count)) as web_sale_count from cc_vote_miss_selling where vote_id=".$miss_id;
			
				$web_sell = $mdl_vote_miss_selling->getListBySql($sql1) ;
				//var_dump($sql1);exit;
				if($web_sell) {
					if($web_sell[0]['web_sale_count']>0) {
						$web_sale_count =$web_sell[0]['web_sale_count'] ;
					}else{
						$web_sale_count=0;
					}
					$this->setData($web_sale_count,'web_sale_count');
					
				}
				//获取网站人气金完成
				
				
				
				
				$business_id = $miss_info['couponid'];
				
				
				
			
				if ($business_id) {
					$mdl_coupons =$this->loadModel('coupons');
					//如果该佳丽对应一个商家ID,那么首先获得商家创建的或者勾选的产品数据.
					$sql ="select id, title,pic,voucher_deal_amount from cc_coupons where (createUserId =".$business_id." and isApproved=1 and status=4) or ( id in (select productId from cc_referral_product_program where userId= ".$business_id ." and isApproved=1 and status=4))  ";
					// 然后获得该佳丽对应的主办方运营商旗下所有商家数据.
					//$sql=$sql. " union select id, title,pic,voucher_deal_amount from cc_coupons where createUserId in( select id from cc_user where user_belong_to_agent = 210362) and isApproved=1 and status=4 limit 24";
					// 
					$sql=$sql. " union  select id, title,pic,voucher_deal_amount from cc_coupons where  id in (7539,7534,7533,7532,7531,7346,7514,7511,7518,7525,7523,7522,7518,7514,7511,7501,7507,7509,7504,7475,7528)   limit 24";
					
					
					$couponList = $mdl_coupons->getListBySql($sql);
					//var_dump($sql);exit;
				/*	if(!$couponList) {
						//如果还没有数据则把京东日料和上海会馆的数据放上
						$sql ="select id, title,pic,voucher_deal_amount from cc_coupons where (createUserId =211688 or createUserId=205514) and isApproved=1 and status=4 limit 4";
						$couponList = $mdl_coupons->getListBySql($sql);
						
						//var_dump($sql);exit;
					} */
					$this->setData($couponList,'couponlist');
					
				}
				
				
				
				// 获取佳丽代言商城信息
				
				
				
				// 计算网页投票一次性投票数量
				 $current_date = date("Y-m-j",time());
				 $end_date = date("Y-m-j",1563929999);
		         $days = (int)((1563929999-time())/(60*60*24))+1;
				 $this->setData($days,'days');
				 $this->setData($business_id,'business_id');
				
				//var_dump($days);exit;
				
				
				$this->setData($miss_id,'miss_id');
				
				
				
				
			$this->setData('亲~我叫'.$miss_info['title'].'在参加2019澳洲华裔小姐墨尔本赛区选美，请投票或购物支持我！ ','pageTitle');
			$this->setData('2019墨尔本华裔小姐 '.$miss_info['title'],'pageKeywords');
			$this->setData($miss_info['description'],'pageDescription');

			//wx share
			require_once "wx/wxjssdk.php";
	        $jssdk = new WXjsSDK();
	        $signPackage = $jssdk->GetSignPackage();
	        $this->setData($signPackage,'signPackage');
				
				$this->display('brandstore/2019miss/miss/index');
				
			}
			
			
		}else{
			
			$this->form_response_msg('未能获得佳丽信息!');
			
		}
		
		
		
		
		
	}
	
	/**
	 * 中国好声音在线报名。标定couponID 的指定出口
	 * @return [type] [description]
	 */
	public function enroll_action()
	{	
       // $this->sheader(null,'很抱歉，活动报名已经结束');
		$interface_coupon_id=7122;

		if (is_post()) {
			/**
			 * 验证信息
			 */
			
			$city        = post('city');
			$city 		 =(is_array($city))?reset($city):$city;
			
			$category    = trim(reset(post('category')));
			
			$name        = trim(post('name'));
			
			$gender      = trim(post('gender'));
			
			$occupation  = trim(post('occupation'));
			
			$nationality = trim(post('nationality'));
			
			$phone       = trim(post('phone'));
			
			$address     = trim(post('address'));
			
			$email       = trim(post('email'));
			
			$reason      = trim(post('reason'));
			
			$location    = trim(post('location'));
			
			$payment     = trim(post('payment'));
			
			$card_number = trim(post('card_number'));
			
			$card_security_code   = trim(post('card_security_code'));
			
			$card_expire_month  = trim(post('card_expire_month'));
			
			$card_expire_year   = trim(post('card_expire_year'));


			//Validation
			if (!$this->loadModel( 'reg' )->chkMail( $email ) ) 
				$this->form_response_msg('请填写正确的邮箱');

			if($payment=='creditcard'){
				if(!$card_number||!$card_security_code||!$card_expire_month||!$card_expire_year)
					$this->form_response_msg('请填写完整的信用卡信息');
			}
			
			$s=$this->loadModel('coupons_sub')->get($location);
			if($s['quantity']<1)
				$this->form_response_msg('很抱歉，您来晚了，名额已满');

			/**
			 * 创建用户
			 */
			$mdl_user = $this->loadModel( 'user' );

			
			if($this->loginUser){
				$currentUser=$this->loginUser;

			}else{
				$userObject = new User();

				$username='User';
				while($mdl_user->getCount( "name='$username'" ) > 0) {
					$randnumber =rand(100,999);
					$username .=$randnumber; // append 3 digit until a new one
				}
				$userObject->setName($username);

				$initPassowrd = $this->createRnd();
				$userObject->setPassword($this->md5($initPassowrd));
				$userObject->setInitPassowrd($initPassowrd);


				$userObject->setFullName($name);
				$userObject->setEmail($email);
				$userObject->setBusinessMobile($phone);
				$userObject->setAddress($address);

				$new_id=$mdl_user->insert($userObject->toDBArray());

				$currentUser = $mdl_user->get($new_id);

				$this->session( 'member_user_id', $currentUser['id'] );
				$this->session( 'member_user_shell', $this->md5( $currentUser['id'].$currentUser['name'].$currentUser['password'] ) );

				$this->loadModel('system_mail_queue')->add($currentUser['id'],EmailType::CustomerRegistryNotification);
			}
			
			/**
			 * 创建订单
			 */
			$mdl_coupons = $this->loadModel( 'coupons' );
			$coupon = $mdl_coupons->get($interface_coupon_id);

			$arr_post=array(
				'ids'=>	array( $coupon['id']),
				'sub_ids'=>array( $location),
				'quantities'=>array(1),
				'sub_money'	=>array($coupon['voucher_deal_amount']),

				'money'=> $coupon['voucher_deal_amount'],
		
				'payment'=> $payment,

				'business_userId'=> $coupon['createUserId'],
				'business_staff_id' =>$location,

				'sub_or_main'=>array('s'),
			
				'customer_delivery_option'=>0, 
				
				'first_name'=>$name,
				'phone'=>$phone,
				'email'=>$email,
				'address'=>$address,
				'message_to_business'=>$city.'-'.$category.'-'.$nationality.'-'.$occupation.'-'.$gender.'-'.$reason,
				

				'card_number' =>$card_number,
				'card_expire_month' =>$card_expire_month,
				'card_expire_year' =>$card_expire_year,
				'card_security_code' =>$card_security_code,
			);


			$orderId=date( 'YmdHis' ).$this->createRnd();
			$arr_post['userId']=$currentUser['id'];
			$arr_post['orderId']= $orderId;
			
			
			/**
			 * 转入相应支付
			 */
			$mdl_wj_temp_orderID_carts =$this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);

			$this->form_response(200,(string)$this->lang->submission_success,HTTP_ROOT_WWW.'payment/orderpaymentprocess/pay?orderId='.$orderId);


		}else{
			$sub_coupon_list = $this->loadModel('coupons_sub')->getChildList($interface_coupon_id);
			$this->setData($sub_coupon_list,'sub_coupon_list');

			$this->setData('2019 中国好声音 墨尔本 海选 线上报名 Ubonus-微奖网 ','pageTitle');
			$this->setData('2019 中国好声音 墨尔本 海选 线上报名 Ubonus-微奖网 澳洲  华人 电商 门户 网站 Ubonus','pageKeywords');
			$this->setData('《中国好声音》是灿星制作推出的一档大型励志专业音乐评论电视节目，今年继续进行海外地区海选，墨尔本站就是其中之一','pageDescription');

			//wx share
			require_once "wx/wxjssdk.php";
	        $jssdk = new WXjsSDK();
	        $signPackage = $jssdk->GetSignPackage();
	        $this->setData($signPackage,'signPackage');

			if($this->getUserDevice()=='desktop'){
				$this->display( 'coupon/coupon_enroll' );
			}else{
				$this->display('coupon/coupon_enroll_mobile');
			}
		}
	}

	
    function get_sum_amount_zhongchou(){
		
		$mdl_order =$this->loadModel('order');
		$sql = " select sum(money) as sum  from cc_order where business_userId=210260" ;
		$sum =$mdl_order->getListBySql($sql);
		$total=$sum[0]['sum'];
		return $total;
		
	}
	
	

	/**
	 * 墨尔本大学辩论赛众筹。标定couponID 的指定出口
	 * @return [type] [description]
	 */
	public function unimelb_action()
	{	
       
	 if ( ! $this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}

          
	   // $this->sheader(null,'很抱歉，活动报名已经结束');
		//买一赠多 
		$optionList = [
			
			[
				'optionName'=>'决赛VIP票（售价10刀）+Royal tea 半价奶茶券1张线上领取+东京日料深夜堂食7折券现场领取 + Crown半价自助餐券现场领取）（首批50份） ',
				'amount'=>'$10 决赛VIP票',
				'mainCouponId'=>7261,
				'giftedCouponList'=>[7275],
			],
			[
				'optionName'=>'决赛SVIP票（售价50刀+Royal tea 价奶茶券1张线上领取+东京日料深夜堂食7折券现场领取+Crown半价自助餐券现场领取+大合影机会+嘉宾随机签名海报）（首批10份）',
				'amount'=>'$50 决赛SVIP票',
				'mainCouponId'=>7262,
				'giftedCouponList'=>[7275],
			]
		];

		$total_money =$this->get_sum_amount_zhongchou() +2910; 
		$percent = number_format(($total_money/12000)*100,2);
		$this->setData($total_money,'total_money');
		$this->setData($percent.'%','percent');
		

		if (is_post()) {
			/**
			 * 验证信息
			 */
		
			$name        = trim(post('name'));
			$phone       = trim(post('phone'));
			$email       = trim(post('email'));
			$option 	 = intval(trim(post('option')));

			$payment     = trim(post('payment'));
			
			$card_number = trim(post('card_number'));
			
			$card_security_code   = trim(post('card_security_code'));
			
			$card_expire_month  = trim(post('card_expire_month'));
			
			$card_expire_year   = trim(post('card_expire_year'));

			 if($option ==-1) {
				 
				 	$this->form_response_msg('请选择门票');
			 }

			//Validation
			if (!$this->loadModel( 'reg' )->chkMail( $email ) ) 
				$this->form_response_msg('请填写正确的邮箱');

			if($payment=='creditcard'){
				if(!$card_number||!$card_security_code||!$card_expire_month||!$card_expire_year)
					$this->form_response_msg('请填写完整的信用卡信息');
			}
			
			

			/**
			 * 创建用户
			 */
			$mdl_user = $this->loadModel( 'user' );

			
			if($this->loginUser){
				/*  */
				$this->loginUser['person_first_name']=$name;
				$this->loginUser['phone']=$phone;
				$this->loginUser['email']=$email;
				$userUpdateData =array(
				  'person_first_name'=>$name,
				  'phone'=>$phone,
				  'email'=>$email
				);
				if($mdl_user->update($userUpdateData,$this->loginUser['id'])){
						//var_dump('yes' . $userUpdateData);exit;
					
				}else{
					//var_dump('no');exit;
				}
				
				$currentUser=$this->loginUser;
			}else{
				$userObject = new User();

				$username='User';
				while($mdl_user->getCount( "name='$username'" ) > 0) {
					$randnumber =rand(100,999);
					$username .=$randnumber; // append 3 digit until a new one
				}
				$userObject->setName($username);

				$initPassowrd = $this->createRnd();
				$userObject->setPassword($this->md5($initPassowrd));
				$userObject->setInitPassowrd($initPassowrd);


				$userObject->setFullName($name);
				$userObject->setEmail($email);
				$userObject->setBusinessMobile($phone);

				$new_id=$mdl_user->insert($userObject->toDBArray());

				$currentUser = $mdl_user->get($new_id);

				$this->session( 'member_user_id', $currentUser['id'] );
				$this->session( 'member_user_shell', $this->md5( $currentUser['id'].$currentUser['name'].$currentUser['password'] ) );

				$this->loadModel('system_mail_queue')->add($currentUser['id'],EmailType::CustomerRegistryNotification);
			}
			

			$item = $optionList[$option];
			$mainCouponId = $item['mainCouponId'];
			$giftedCouponList = $item['giftedCouponList'];

			$mainCoupon=$this->loadModel('coupons')->get($mainCouponId);
			if($mainCoupon['qty']<1)
				$this->form_response_msg('很抱歉，这个卖完了');

			$giftedCouponOrderId = [];
			/**
			 * 创建赠送订单临时数据
			 */
			foreach ($giftedCouponList as $interface_coupon_id ) {
				$mdl_coupons = $this->loadModel( 'coupons' );
				$coupon = $mdl_coupons->get($interface_coupon_id);

				$arr_post=array(
					'ids'=>	array( $coupon['id']),
					'sub_ids'=>array( $coupon['id']),
					'quantities'=>array(1),
					'sub_money'	=>array($coupon['voucher_deal_amount']),

					'money'=> $coupon['voucher_deal_amount'],
			
					'payment'=> $payment,

					'business_userId'=> $coupon['createUserId'],

					'sub_or_main'=>array('m'),
				
					'customer_delivery_option'=>0, 
					
					'first_name'=>$name,
					'phone'=>$phone,
					'email'=>$email,

					'card_number' =>$card_number,
					'card_expire_month' =>$card_expire_month,
					'card_expire_year' =>$card_expire_year,
					'card_security_code' =>$card_security_code,
				);


				$orderId=date( 'YmdHis' ).$this->createRnd();
				$arr_post['userId']=$currentUser['id'];
				$arr_post['orderId']= $orderId;
				
				
				$mdl_wj_temp_orderID_carts =$this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);

				$giftedCouponOrderId[] = $orderId;
			}


			/**
			 * 创建主订单临时数据
			 */
			$mdl_coupons = $this->loadModel( 'coupons' );
			$coupon = $mdl_coupons->get($mainCouponId);

			$arr_post=array(
				'ids'=>	array( $coupon['id']),
				'sub_ids'=>array( $coupon['id']),
				'quantities'=>array(1),
				'sub_money'	=>array($coupon['voucher_deal_amount']),

				'money'=> $coupon['voucher_deal_amount'],
		
				'payment'=> $payment,

				'business_userId'=> $coupon['createUserId'],

				'sub_or_main'=>array('m'),
			
				'customer_delivery_option'=>0, 
				
				'first_name'=>$name,
				'phone'=>$phone,
				'email'=>$email,

				'card_number' =>$card_number,
				'card_expire_month' =>$card_expire_month,
				'card_expire_year' =>$card_expire_year,
				'card_security_code' =>$card_security_code,
				'giftedCouponOrderId' => join(',',$giftedCouponOrderId)
			);


			$orderId=date( 'YmdHis' ).$this->createRnd();
			$arr_post['userId']=$currentUser['id'];
			$arr_post['orderId']= $orderId;
			
			
			/**
			 * 转入相应支付
			 */
			$mdl_wj_temp_orderID_carts =$this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);

			$this->form_response(200,(string)$this->lang->submission_success,HTTP_ROOT_WWW.'payment/orderpaymentprocess/pay?orderId='.$orderId);


		}else{
			$this->setData($optionList,'optionList');

			$this->setData('第7届全澳大学华语辩论邀请赛 ','pageTitle');
			$this->setData('第7届全澳大学华语辩论邀请赛 Ubonus-微奖网 澳洲  华人 电商 门户 网站 Ubonus','pageKeywords');
			$this->setData('第7届全澳大学华语辩论邀请赛,(陈铭,林正疆,肖磊,徐卓阳做为全澳赛评委)这是-属于你的全澳赛','pageDescription');

			//wx share
			require_once "wx/wxjssdk.php";
	        $jssdk = new WXjsSDK();
	        $signPackage = $jssdk->GetSignPackage();
	        $this->setData($signPackage,'signPackage');

			if($this->getUserDevice()=='desktop'){
				$this->setData('pc','display_mode');
				$this->display( 'coupon/unimelb_mobile' );
			}else{
				$this->setData('mobile','display_mode');
				$this->display('coupon/unimelb_mobile');
			}
		}
	}

		/**
	 * 2019华裔小姐报名。标定couponID 的指定出口
	 * @return [type] [description]
	 */
	public function chinese_pageant1_action()
	{	
       // $this->sheader(null,'很抱歉，活动报名已经结束');
		$interface_coupon_id=7176;
        $istoday =(int)get2('istoday');
		$this->setData($istoday,'istoday');
		
		if (is_post()) {
			/**
			 * 验证信息
			 */
			$istoday     = post('istoday');
			$city        = post('city');
			if(!$city) {
				$city="Melbourne";
			}
			
			$name        = trim(post('name'));
			if($istoday) {
				
				$name='今日报名-'.$name;
			}
			//$this->form_response_msg($name);

			$name_en        = trim(post('name_en'));

			$address     = trim(post('address'));
			$phone       = trim(post('phone'));
			$email       = trim(post('email'));

			$dob_day      = trim(post('dob_day'));
			$dob_month      = trim(post('dob_month'));
			$dob_year      = trim(post('dob_year'));

			$age      = trim(post('age'));
			
			$place_of_birth  = trim(post('place_of_birth'));
			$nationality = trim(post('nationality'));
			
			$marital_status = trim(post('marital_status'));
			
			
			$language = trim(post('language'));
			$date_of_arrival = trim(post('date_of_arrival'));
			$length_of_residence = trim(post('length_of_residence'));
			
			$resident_status = trim(post('resident_status'));

			$travel_document = trim(post('travel_document'));
			$travel_document_number = trim(post('travel_document_number'));
			$province = trim(post('province'));
			$highschool = trim(post('highschool'));
			$college = trim(post('college'));
			$university = trim(post('university'));
			$year_graduated = trim(post('year_graduated'));
			$employer_name = trim(post('employer_name'));
			$job_title = trim(post('job_title'));
			$employment_start = trim(post('employment_start'));
			$height = trim(post('height'));
			$weight = trim(post('weight'));
			$waist = trim(post('waist'));
			$bust = trim(post('bust'));
			$hips = trim(post('hips'));
			$ever_participated_in_a_beauty_contest = trim(post('ever_participated_in_a_beauty_contest'));
			$name_of_contest = trim(post('name_of_contest'));
			$color_of_eye = trim(post('color_of_eye'));
			$color_of_hair = trim(post('color_of_hair'));
			$hobbies = trim(post('hobbies'));
			$telents = trim(post('telents'));
			$illness = trim(post('illness'));
			$name_of_affliction = trim(post('name_of_affliction'));
			$smoke = trim(post('smoke'));
			$allergies = trim(post('allergies'));
			$name_of_allergies = trim(post('name_of_allergies'));
			$emergency_contact_name = trim(post('emergency_contact_name'));
			$emergency_contact_relation = trim(post('emergency_contact_relation'));
			$emergency_contact_phone = trim(post('emergency_contact_phone'));
			$emergency_contact_address = trim(post('emergency_contact_address'));

			$func = function($k, $v){
				return $k.":".$v;
			};
			$message_to_business = join(array_map($func, array_keys($_POST) , $_POST), ' <br> ');
			
			//Validation
			if (!$this->loadModel( 'reg' )->chkMail( $email ) ) 
				$this->form_response_msg('请填写正确的邮箱');
			
			/**
			 * 创建用户
			 */
			$mdl_user = $this->loadModel( 'user' );

			
			if($this->loginUser){
				$currentUser=$this->loginUser;

			}else{
				$userObject = new User();

				$username='User';
				while($mdl_user->getCount( "name='$username'" ) > 0) {
					$randnumber =rand(100,999);
					$username .=$randnumber; // append 3 digit until a new one
				}
				$userObject->setName($username);

				$initPassowrd = $this->createRnd();
				$userObject->setPassword($this->md5($initPassowrd));
				$userObject->setInitPassowrd($initPassowrd);


				$userObject->setFullName($name);
				$userObject->setEmail($email);
				$userObject->setBusinessMobile($phone);
				$userObject->setAddress($address);

				$new_id=$mdl_user->insert($userObject->toDBArray());

				$currentUser = $mdl_user->get($new_id);

				$this->session( 'member_user_id', $currentUser['id'] );
				$this->session( 'member_user_shell', $this->md5( $currentUser['id'].$currentUser['name'].$currentUser['password'] ) );

				$this->loadModel('system_mail_queue')->add($currentUser['id'],EmailType::CustomerRegistryNotification);
			}
			
			/**
			 * 创建订单
			 */
			$mdl_coupons = $this->loadModel( 'coupons' );
			$coupon = $mdl_coupons->get($interface_coupon_id);


			$arr_post=array(
				'ids'=>	array( $coupon['id']),
				'sub_ids'=>array($coupon['id']),
				'quantities'=>array(1),
				'sub_money'	=>array($coupon['voucher_deal_amount']),

				'money'=> $coupon['voucher_deal_amount'],
		
				'payment'=> 'offline',

				'business_userId'=> $coupon['createUserId'],
				'business_staff_id' =>$location,

				'sub_or_main'=>array('m'),
			
				'customer_delivery_option'=>0, 
				
				'first_name'=>$name,
				'phone'=>$phone,
				'email'=>$email,
				'address'=>$address,
				'message_to_business'=>$message_to_business
			);


			$orderId=date( 'YmdHis' ).$this->createRnd();
			$arr_post['userId']=$currentUser['id'];
			$arr_post['orderId']= $orderId;
			
			
			/**
			 * 转入相应支付
			 */
			$mdl_wj_temp_orderID_carts =$this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);

			$this->form_response(200,(string)$this->lang->submission_success,HTTP_ROOT_WWW.'payment/orderpaymentprocess/pay?orderId='.$orderId);


		}else{
			$sub_coupon_list = $this->loadModel('coupons_sub')->getChildList($interface_coupon_id);
			$this->setData($sub_coupon_list,'sub_coupon_list');

			$this->setData('2019 TBV 华裔小姐 Ubonus-微奖网 ','pageTitle');
			$this->setData('2019 TBV 华裔小姐 线上报名 Ubonus-微奖网 澳洲  华人 电商 门户 网站 Ubonus','pageKeywords');
			$this->setData('TBV 华裔小姐 2019','pageDescription');

			//wx share
			require_once "wx/wxjssdk.php";
	        $jssdk = new WXjsSDK();
	        $signPackage = $jssdk->GetSignPackage();
	        $this->setData($signPackage,'signPackage');

			if($this->getUserDevice()=='desktop'){
				$this->display( 'coupon/coupon_enroll_chinese_pageant' );
			}else{
				$this->display('coupon/coupon_enroll_chinese_pageant_mobile');
			}
		}
	}
	
	public function buy_success_action()
	{
		$id = get2('id');
		$this->setData($id,'orderId');
		$this->display_pc_mobile('coupon/buy_success', 'mobile/coupon/buy_success');

	}

	public function checkout_action(){
		if ( ! $this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}


		$id = (int)get2( 'id' );
		
		$mdl_coupons = $this->loadModel( 'coupons' );

		$mdl_coupons_sub = $this->loadModel( 'coupons_sub' );

		$coupon = $mdl_coupons->get( $id );

		$alert=$mdl_coupons->checkIsPublish($coupon);
		if($alert){
			$this->sheader(null,$alert);
		}		
		
		$this->setData($mdl_coupons_sub->getChildList($id),'sub_coupon');

		if($coupon['staff_region_limited']==1){
			$staff_list = $this->loadModel('user')->getAllStaffFromString($coupon['sales_user_list']);
			$this->setData( $staff_list, 'staff_list' );
		}

		$business_delivery_info =$this->loadModel('user')->getBusinessDeliveryInfo($coupon['createUserId']);
		$this->setData( $business_delivery_info, 'business_delivery_info' );

		
		$this->setData( $coupon, 'coupon' );
		$this->setData( $coupon['title'].' - '.$this->site['pageTitle'], 'pageTitle' );

		if($this->getUserDevice()=='desktop'){
			$this->display( 'coupon/coupon_buy' );
		}else{
			$this->display('mobile/coupon/coupon_buy');
		}

	}
	function buy_action() {
		if ( ! $this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}

		$mdl_coupons = $this->loadModel( 'coupons' );
		$mdl_coupons_sub = $this->loadModel( 'coupons_sub' );

		if ( is_post() ){

			$id = (int)get2( 'id' );
			
			$sub = (int)post('sub');

			$is_main=($id==$sub)?"m":"s";

			$coupon = $mdl_coupons->get( $id );

			$subCoupon = $mdl_coupons_sub->get( $sub );//might be null

			$customer_buy_quantities =(int)post('qty');
			
			$alert=$mdl_coupons->checkIsPublish($coupon);
			if($alert){
				$this->form_response_msg($alert);
			}			

			$phone=trim(post('delivery_phone'));
			if(!$phone){
				$this->form_response_msg('请填写电话信息！');
			}

			$last_name=trim(post('delivery_last_name'));
			if(!$last_name){
				$this->form_response_msg('请填写姓名信息！');
			}

			$first_name=trim(post('delivery_first_name'));
			if(!$first_name){
				$this->form_response_msg('请填写姓名信息！');
			}

			$email=trim(post('delivery_email'));
			if(!$email){
				$this->form_response_msg('请填写正确的邮箱！');
			}

			$qty = intval(post('qty'));
			if($qty < 1){
				$this->form_response_msg('至少购买一个！');
			}
			
			


			//数量check
			if ($is_main=='s' ) { 
				if ( $qty>$subCoupon['quantity'] ) { 
					$this->form_response_msg('当前产品'.$subCoupon['title'].'库存不足,请调整数量,库存为:'.$subCoupon['quantity']);
				}

				$single_price=$subCoupon['customer_amount'];
				$total_price=$single_price*$qty;

			}elseif($is_main=='m'){
				if ( $qty>$coupon['qty'] ) { 
					$this->form_response_msg('当前产品-'.$coupon['title'].'-库存不足,只有:'.$coupon['qty'] );
				}

				$single_price= $coupon['voucher_deal_amount'];
				$total_price=$single_price*$qty;
			}


			$payment =trim(post('payment'));

			if($total_price>0){
				if(!in_array($payment, ['paypal','creditcard','royalpay','hcash','offline']))
					$this->form_response_msg('请选择支付方式');

				if($payment=='creditcard'){
					if(!$card_number||!$card_security_code||!$card_expire_month||!$card_expire_year)
						$this->form_response_msg('请填写完整的信用卡信息');
				}
			}else{
				$payment='offline';
			}
			

			$staff_id=(post('staff_id'));
			

			/**
			 * 创建订单
			 */

			$arr_post=array(
				'ids'=>	array( $id),
				'sub_ids'=>array( $sub),
				'quantities'=>array($qty),
				'sub_money'	=>array($single_price),

				'money'=> $total_price,
		
				'payment'=> $payment,

				'business_userId'=> $coupon['createUserId'],
				'business_staff_id' =>$staff_id,

				'sub_or_main'=>array($is_main),
			
				'customer_delivery_option'=>0, 
				
				'last_name'=>$last_name,
				'first_name'=>$first_name,
				'phone'=>$phone,
				'email'=>$email,
				'message_to_business'=>$msgToBusiness,
				

				'card_number' =>$card_number,
				'card_expire_month' =>$card_expire_month,
				'card_expire_year' =>$card_expire_year,
				'card_security_code' =>$card_security_code,
			);


			$orderId=date( 'YmdHis' ).$this->createRnd();
			$arr_post['userId']=$this->loginUser['id'];
			$arr_post['orderId']= $orderId;
			
			
			/**
			 * 转入相应支付
			 */
			$mdl_wj_temp_orderID_carts =$this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);

			$this->form_response(200,(string)$this->lang->submission_success,HTTP_ROOT_WWW.'payment/orderpaymentprocess/pay?orderId='.$orderId);
		}
	}


	public function voucher_subscription_action()
	{	
		if (!$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
	
		if (is_post()) {
			/**
			 * 验证信息
			 */
			
			$name        = trim(post('name'));
			$phone       = trim(post('phone'));
			$address     = trim(post('address'));
			$email       = trim(post('email'));
			
			$reason      = trim(post('reason'));
			
				
			$payment     = trim(post('payment'));
			
			$card_number = trim(post('card_number'));
			
			$card_security_code   = trim(post('card_security_code'));
			
			$card_expire_month  = trim(post('card_expire_month'));
			
			$card_expire_year   = trim(post('card_expire_year'));


			//Validation
			if (!$this->loadModel( 'reg' )->chkMail( $email ) ) 
				$this->form_response_msg('请填写正确的邮箱');

			if($payment=='creditcard'){
				if(!$card_number||!$card_security_code||!$card_expire_month||!$card_expire_year)
					$this->form_response_msg('请填写完整的信用卡信息');
			}
			
			
			/**
			 * 创建用户
			 */
			$mdl_user = $this->loadModel( 'user' );

			
			if($this->loginUser){
				$currentUser=$this->loginUser;
				// 更新改用户的 姓名/电话/邮件/
				

			}else{
				$userObject = new User();

				$username='User';
				while($mdl_user->getCount( "name='$username'" ) > 0) {
					$randnumber =rand(100,999);
					$username .=$randnumber; // append 3 digit until a new one
				}
				$userObject->setName($username);

				$initPassowrd = $this->createRnd();
				$userObject->setPassword($this->md5($initPassowrd));
				$userObject->setInitPassowrd($initPassowrd);


				$userObject->setFullName($name);
				$userObject->setEmail($email);
				$userObject->setBusinessMobile($phone);
				$userObject->setAddress($address);

				$new_id=$mdl_user->insert($userObject->toDBArray());

				$currentUser = $mdl_user->get($new_id);

				$this->session( 'member_user_id', $currentUser['id'] );
				$this->session( 'member_user_shell', $this->md5( $currentUser['id'].$currentUser['name'].$currentUser['password'] ) );

				$this->loadModel('system_mail_queue')->add($currentUser['id'],EmailType::CustomerRegistryNotification);
			}
			
			/**
			 * 创建订单
			 */
			$mdl_coupons = $this->loadModel( 'coupons' );
			$coupon = $mdl_coupons->get($interface_coupon_id);
			
			//生成订单标题
			// xxx 给 xxx 购买了什么?
			
			$order_title_buyer = $name.'('.$phone.')';
			$order_title_miss = $vote_info['title'].'('.$vote_info['id'].')';
			$order_title =$order_title_buyer.'为佳丽'.$order_title_miss.'购买了'.$what;
			
			$arr_post=array(
				'ids'=>	array( $coupon['id']),
				'sub_ids'=>array( 0),
				'quantities'=>array(1),
				'sub_money'	=>array($coupon['voucher_deal_amount']),
				'money'=> $coupon['voucher_deal_amount'],
				'order_name'=>$order_title,
		
				'payment'=> $payment,
				'business_userId'=> $coupon['createUserId'],
				'business_staff_id' =>$vote_id, //暂存选手佳丽id
				'sub_or_main'=>array('s'),
				'customer_delivery_option'=>0, 
				'first_name'=>$name,
				'phone'=>$phone,
				'email'=>$email,
				'address'=>$address,
				'message_to_business'=>$reason,

				'card_number' =>$card_number,
				'card_expire_month' =>$card_expire_month,
				'card_expire_year' =>$card_expire_year,
				'card_security_code' =>$card_security_code,
			);


			$orderId=date( 'YmdHis' ).$this->createRnd();
			$arr_post['userId']=$currentUser['id'];
			$arr_post['orderId']= $orderId;
            
			
			/**
			 * 转入相应支付
			 */
			$mdl_wj_temp_orderID_carts =$this->loadModel('wj_temp_orderID_carts')->save_temp_data($arr_post,$orderId);
			$this->form_response(200,(string)$this->lang->submission_success,HTTP_ROOT_WWW.'payment/orderpaymentprocess/pay?orderId='.$orderId);


		}else{
			$interface_coupon_id=(int)get2('couponid');
		
			$sub_coupon_list = $this->loadModel('coupons_sub')->getChildList($interface_coupon_id);
			$this->setData($sub_coupon_list,'sub_coupon_list');

			$this->setData("通吃卡",'pageTitle');
			$this->setData("通吃卡",'pageKeywords');
			$this->setData("通吃卡",'pageDescription');

			$this->setData($this->getUserDevice(), 'userdevice');
			$this->display('coupon/voucher_subscription');

			//wx share
			require_once "wx/wxjssdk.php";
	        $jssdk = new WXjsSDK();
	        $signPackage = $jssdk->GetSignPackage();
	        $this->setData($signPackage,'signPackage');

			
		}
	}

	public function choose_city_action()
	{	
		$this->display('choose_city');
	}
}