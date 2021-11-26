<?php
/**
 * 手机版分类检索页面
 */
class ctl_category extends cmsPage
{
	

	function index_action() {

		$this->setData( '分类信息 - '.$this->site['pageTitle'], 'pageTitle' );
		
		$section = trim(get2('section'));

		if(strlen($section)>9)$section=substr($section,0,9);

		$this->setData($section,'section');

		if(strlen($section)>6)$psection=substr($section,0,6);

		$this->setData($psection,'psection');
		
		// 获得当前所有用到的分类
		
		

		$this->setData( $this->loadModel( 'infoClass' )->getChild4( '106' ), 'categories' );
		// var_dump($this->loadModel( 'infoClass' )->getChild4( '106' ));exit;
		$this->setData( $this->loadModel( 'infoClass' )->getChild4( '106121102' ), 'food_cats' );
		
		$this->setData( 'category', 'mobile_menu' );
		
		$this->display('mobile/category');
	}


	function category_list_action(){
		$mdl_coupons= $this->loadModel('coupons');

		$cid = get2('cid');
		if(!$cid)$cid='106';

     
				
				
			
		
		
		$where[] =  "categoryId like '%,$cid%'";
		
		/*(select count(*)  from cc_wj_customer_coupon where business_id = c.createUserId and coupon_status<>'d01') as countOfSale*/
        $pageSql ="SELECT DISTINCT c.createUserId as id  , c.EvoucherOrrealproduct, c.categoryId ,c.pic ,c.hits,u.pic as business_pic ,u.displayName as title ,u.displayName_en ,
		0 as countOfSale ,u.deliver_avaliable,u.pickup_avaliable
		from cc_coupons  c left join  cc_user u on c.createUserId =u.id 
		where c.isApproved=1 and c.status=4 and u.id <>319188 
		and (c.categoryId like '%,$cid%' or u.categoryId like '%,$cid%' )
		group by id 
		order by id desc ";
		
		$pageUrl	= $this->parseUrl()->set('page');
		$pageSize	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize);
		$data		= $mdl_coupons->getListBySql($page['outSql']);

		foreach ($data as $key => $value) {
			
			if($this->getLangStr()=='en' && $data[$key]['displayName_en'] !='') {
				 $data[$key]['title']=$data[$key]['displayName_en'];
				 
			}
			if($value['business_pic']){
				  $data[$key]['pic'] =$value['business_pic'];
			}
			if($value['EvoucherOrrealproduct'] =='restaurant_menu'){
				  $data[$key]['path'] ='restaurant';
			}else{
				
				  $data[$key]['path'] ='store';
			}
		}

		if(sizeof($data)>0)
			$this->form_response(200,$data);
		else
			$this->form_response(300,null);	
	}


	function category_list1_action(){
		$mdl_coupons= $this->loadModel('coupons');

		$cid = get2('cid');
		if(!$cid)$cid='106';

      if($this->getLangStr()=='en'){
			
			$where 	= array('isApproved' => 1,
							'status' => 4,
							'EvoucherOrrealproduct <> "restaurant_menu"',
							'isInManagement' => 0,
							'languageType_en' =>1,
							);
							
			}else{
				
				$where 	= array('isApproved' => 1,
							'status' => 4,
							'EvoucherOrrealproduct <> "restaurant_menu"',
							'isInManagement' => 0,
							'languageType_cn' =>1
							);
			
		}
		
		$where[] =  "categoryId like '%,$cid%'";
		
		
        $pageSql = $mdl_coupons->getListSql(array('id', 'title', 'title_en','pic', 'hits', 'voucher_deal_amount', 'bonusType'), $where, "id desc");

		$pageUrl	= $this->parseUrl()->set('page');
		$pageSize	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize);
		$data		= $mdl_coupons->getListBySql($page['outSql']);

		foreach ($data as $key => $value) {
			$data[$key]['price']=$value['voucher_deal_amount'];
			if($this->getLangStr()=='en' && $data[$key]['title_en'] !='') {
				 $data[$key]['title']=$data[$key]['title_en'];
				 
			}
		}

		if(sizeof($data)>0)
			$this->form_response(200,$data);
		else
			$this->form_response(300,null);	
	}
	

}