<?php 
class ctl_group_pin extends cmsPage{

	function ctl_group_buy() {
		parent::cmsPage();
		
		$ignore_list=array('user_group_show','index');

		if ( !in_array($act, $ignore_list) && !$this->loginUser ) {
			$this->sheader( HTTP_ROOT_WWW.'member/login?returnUrl='.urlencode( $_SERVER['REQUEST_URI'] ) );
		}
	
	}


	/**
	 * ADMIN FUNCTION
	 */

	public function create_action()
	{
		$mdl_coupons= $this->loadModel('coupons');
		$couponList9 = $mdl_coupons->getCouponListOfType($this->loginUser['id'],9);
		$couponList7 = $mdl_coupons->getCouponListOfType($this->loginUser['id'],7);
		$couponList = array_merge($couponList7,$couponList9);
		$this->setData($couponList,'couponList');

		if(is_post()){
			$coupon_id        =post('coupon_id');
			$group_size_each  =post('group_size_each');
			$group_size_total =post('group_size_total');
			
			$reward_type      =post('reward_type');
			$reward_value     =post('reward_value');



			$mdl_promotionCode=loadModel('wj_promotion_code');

			switch ($reward_type) {
				case 'flat':
					$type=PromotionCode::TYPE_FIXEDAMOUNT;
					break;
				case 'percent':
					$type=PromotionCode::TYPE_PERCENTAGE;
					break;
				case 'none':
					$type=null;
					break;
				default:
					throw new Exception("Unknown REWARD TYPE ", 1);
					break;
			}

			if($type){
				$pcode = new PromotionCode();
				$pcode->setUserId(loadModel('coupons')->getCreateUserId($coupon_id));
				$pcode->setCouponId($coupon_id);
				$pcode->setDescription('拼多多玩法开团奖励');
				$pcode->setType($type, $reward_value);
				//$pcode->setExpireType(PromotionCode::EXPIRETYPE_FIXEDQTY,$group['current']);
				$pcode->setCode(PromotionCode::RANDOM_CODE);
				$mdl_promotionCode->addPromotionCode($pcode);
				$reward=$pcode->getCode();
			}



			$mdl_group_pin=$this->loadModel('group_pin');

			$data=array();

			$data['coupon_id']        =$coupon_id;
			$data['group_size_each']  =$group_size_each;
			$data['group_size_total'] =$group_size_total;
			
			$data['reward_type']      =$reward_type;
			$data['reward_value']     =$reward_value;
			$data['reward']           =$reward;
			
			$data['gen_date']         =time();
			$data['status']           =mdl_group_pin::STATUS_OPEN;
			$data['time_limit']       =mdl_group_pin::DEFAULT_TIME_LIMIT;

			$data['create_user_id']   =$this->loginUser['id'];

			$mdl_group_pin->insert($data);

			$this->form_response(200,'创建成功',HTTP_ROOT_WWW."group_pin/list");
		}

		$this->setData('index_publish','menu');
        $this->setData('group_pin_manage','submenu');

		$this->display('group_pin/create');
	}


	public function list_action()
	{	
		$mdl_group_pin=$this->loadModel('group_pin');
		$mdl_coupons=$this->loadModel('coupons');

		$pageSql = $mdl_group_pin->getListSql(null,array('create_user_id'=>$this->loginUser['id']));
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize =10;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $list = $mdl_group_pin->getListBySql($page['outSql']);

        foreach ($list as $key => $value) {
			$list[$key]['user_list']= $mdl_group_pin->getUserGroupUserList($value['id']);

			$coupon = $mdl_coupons->get($value['coupon_id']);

			$list[$key]['coupon_name']=$coupon['title'];
			$list[$key]['coupon_status']=($coupon['isApproved']==1&&$coupon['status']==4);
		}


		$this->setData($list,'list');

		$this->setData($page['pageStr'], 'pager');

		$this->setData('拼多多团管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');

		$this->setData('index_publish','menu');
        $this->setData('group_pin_manage','submenu');

		$this->display('group_pin/list');
	}

	public function user_group_list_action()
	{
		$group_id = get2('group_id');

		$mdl_group_pin=$this->loadModel('group_pin');
		$mdl_group_pin_user_group = loadModel('group_pin_user_group');

		$this->setData($mdl_group_pin->get($group_id),'group');

        $pageSql = $mdl_group_pin_user_group->getListSql(null, array('group_id' => $group_id), 'id desc');
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 30;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $list = $mdl_group_pin_user_group->getListBySql($page['outSql']);


		foreach ($list as $key => $value) {
			$list[$key]['user_list']= $mdl_group_pin->getUserGroupUserList($value['id']);
		}

		$this->setData($list,'list');

		$this->setData($group_id,'group_id');

		$this->setData($page['pageStr'], 'pager');

		$this->setData('拼多多团管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');

		$this->setData('index_publish','menu');
        $this->setData('group_pin_manage','submenu');

		$this->display('group_pin/user_group_list');

	}

	public function user_group_list_all_action()
	{
		$group_id = get2('group_id');

		$mdl_group_pin=$this->loadModel('group_pin');
		$mdl_group_pin_user_group = loadModel('group_pin_user_group');
		$mdl_coupons=$this->loadModel('coupons');

		$this->setData($mdl_group_pin->get($group_id),'group');

		$pageSql = "SELECT p.id as gid, p.coupon_id,p.group_size_each,p.gen_date,p.time_limit,pl.* FROM cc_group_pin as p right join cc_group_pin_user_group as pl on p.id = pl.group_id WHERE p.create_user_id =".$this->loginUser['id'] ." order by pl.status";
        $pageUrl = $this->parseUrl()->set('page');
        $pageSize = 30;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $list = $mdl_group_pin_user_group->getListBySql($page['outSql']);


		foreach ($list as $key => $value) {
			$list[$key]['user_list']= $mdl_group_pin->getUserGroupUserList($value['id']);
			$list[$key]['coupon']= $mdl_coupons->get($value['coupon_id']);
		}

		$this->setData($list,'list');

		$this->setData($group_id,'group_id');

		$this->setData($page['pageStr'], 'pager');

		$this->setData('拼多多团管理 - 商家中心 - ' . $this->site['pageTitle'], 'pageTitle');

		$this->setData('index_publish','menu');
        $this->setData('user_group_list_all','submenu');

		$this->display('group_pin/user_group_list_all');

	}

	public function user_group_update_action()
	{
		$id = get2('id');

		$status = get2('status');

		$mdl_group_pin_user_group = $this->loadModel('group_pin_user_group');

		$mdl_group_pin = $this->loadModel('group_pin');


		if($status==mdl_group_pin_user_group::STATUS_OPEN){
			$mdl_group_pin->updateUserGroupOpen($id);

		}elseif($status==mdl_group_pin_user_group::STATUS_COMPLETE){
			$mdl_group_pin->updateUserGroupComplete($id);

		}elseif($status==mdl_group_pin_user_group::STATUS_EXPIRE){
			$mdl_group_pin->updateUserGroupExpire($id);

		}elseif($status==mdl_group_pin_user_group::STATUS_DELETE){
			$mdl_group_pin_user_group->delete($id);

		}

		if(get2('group_id')){
			$this->sheader(HTTP_ROOT_WWW.'group_pin/user_group_list?group_id='.get2('group_id'));
		}else{
			$this->sheader(HTTP_ROOT_WWW.'group_pin/user_group_list_all');
		}
		
	}



	public function delete_action()
	{
		$id = get2('id');

		$this->loadModel('group_pin')->delete($id);

		$this->sheader(HTTP_ROOT_WWW.'group_pin/list');
	}
	


	/**
	 * DISPLAY FUNCTION
	 */
	
	public function user_group_show_action()
	{	
		$mdl_group_pin_user_group = $this->loadModel('group_pin_user_group');

		$mdl_group_pin = $this->loadModel('group_pin');

		$mdl_coupons= $this->loadModel('coupons');



		$user_group_id = get2('id');

		if($mdl_group_pin->isUserGroupExpire($user_group_id)&&$mdl_group_pin->isUserGroupOpen($user_group_id)){
			$mdl_group_pin->updateUserGroupExpire($user_group_id);
		}

		$user_group=$mdl_group_pin_user_group->get($user_group_id);

		if(!$user_group_id||!$user_group)$this->sheader(null,'找不到该用户团');


		$group_id = $user_group['group_id'];

		$group = $mdl_group_pin->get($group_id);

		if(!$user_group_id||!$user_group)$this->sheader(null,'找不到该用户团的父团');



		$coupon_id = $group['coupon_id'];

		$coupon = $mdl_coupons->get($coupon_id);

		if(!$coupon_id||!$coupon)$this->sheader(null,'找不到该用户团对用的产品');



		$user_list = $mdl_group_pin->getUserGroupUserList($user_group_id,true);



		$this->setData($user_group,'user_group');

		$this->setData($group,'group');

		$this->setData($coupon,'coupon');

		$this->setData($user_list,'user_list');

		$this->setData($this->loadModel('wj_temp_orderID_carts')->getPendingQty($coupon['id'],'m'),'pendingQty');

		if($coupon['bonusType']==7){
			/**
			 * 子卡
			 */
			$mdl_coupons_sub = $this->loadModel( 'coupons_sub' );
			$this->setData($mdl_coupons_sub->getChildList($coupon['id']),'sub_coupon');

			$this->display('coupon_detail/inc/group_pin/user_group_show7');

		}elseif($coupon['bonusType']==9){
			/**
			 * 规格库存显示。 引用详情页 shop
			 */
			
			$couponHasGuige=$this->loadModel('shop_guige')->couponHasGuige($coupon['id']);
			  	$this->setData($couponHasGuige,'couponHasGuige');

			  	if($couponHasGuige){
			  		if(!get2('guige1Id')&&!get2('guige2Id')){
						$set=$this->loadModel('shop_stock')->getDefaultGuigeSet($coupon['id']);
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

					$guigeData=$this->loadModel('shop_guige')->getGuigeStockDataFromGuige($coupon['id'],$guige);

					$this->setData(json_encode($guigeData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE),'guigeData');
			  	}

			$this->setData($this->loadModel('wj_temp_orderID_carts')->getPendingQty($coupon['id'],'m'),'pendingQty');


			$this->display('coupon_detail/inc/group_pin/user_group_show9');
		}

		
	}


	public function index_action()
	{	
		/**
		 * New index page for group pin and group buy
		 */
		
		/**
		 * 产品分类
		 * @var [type]
		 */
		$alias = trim( get2( 'alias' ) );
		if (!$alias)$alias='106';
		$this->setData( $alias,'alias');


		/**
		 * 搜索关键字
		 * @var [type]
		 */
		$searchKeywords = trim( get2( 'key' ) );
		if(!$searchKeywords)$searchKeywords='';

		$this->setData( $searchKeywords, 'searchKeywords' );


		
		$type = get2('type');
		if(!in_array($type, array('group_buy','group_pin')))$type='group_pin';
		$this->setData($type,'type');



		/**
		 * 产品分类模组
		 */
		$mdl_infoclass = $this->loadModel( 'infoClass' );

		/**
		 * 高级检索模组
		 */
		$mdl_advancedKeySearch=$this->loadModel('advancedKeySearch');

		/**
		 * 用户模组
		 */
		$mdl_user = $this->loadModel( 'user' );
		
	



		/**
		 * 页面分类动态显示
		 */
		$category=$mdl_infoclass->getByAlias( $alias );//single

		$parent_category = $mdl_infoclass->getParentListArray( $category['id'] );//array

		$parent_category=array_slice($parent_category, 1);

		$child_category = $mdl_infoclass->getChild4( $category['id'] );//array

		$this->setData( $category, 'category' );

		$this->setData( $parent_category, 'parents' );

		$this->setData( $child_category, 'childs' );


        $currentTime=strtotime ('now');
        
		if($type=='group_pin'){

			//Group Pin List

			$mdl_group_pin=$this->loadModel('group_pin');
			
			$sql = "Select c.title,c.pic,c.voucher_deal_amount,c.voucher_original_amount, gp.coupon_id,gp.group_size_each,gp.group_size_total,gp.reward_type,gp.reward_value from cc_group_pin as gp left join cc_coupons as c on c.id = gp.coupon_id where c.isApproved=1 and c.status=4";

			$sql .= " and c.categoryId like '%,".$category['id']."%' AND !(c.autoOffline=1 AND ('$currentTime'<c.startTime or '$currentTime'>c.endTime))";

			if($searchKeywords)
				$sql .= " and (c.title like '%$searchKeywords%' or c.businessName like '%$searchKeywords%' or c.coupon_summery_description like '%$searchKeywords%') ";

			$sql .=" order by gp.gen_date desc";

			$group_pin_list=$mdl_group_pin->getListBySql($sql);

			$this->setData($group_pin_list,'group_pin_list');


		}elseif($type=='group_buy'){
			//Group Buy List
		
			$sql = "SELECT g.id,g.name,g.status,g.max_user_group,g.condition_level,g.allow_user_group,c.pic FROM `cc_group_buy_status` as g left join cc_coupons as c on g.coupon_id=c.id";

			$sql .= " where g.status !=5 and g.status !=2 "; //[ g.parentId=0 ]child 自开团不显示 //已完成不显示//关闭的不显示

			$sql .= " and c.categoryId like '%,".$category['id']."%' AND c.startTime<=$currentTime AND c.endTime>=$currentTime";

			if($searchKeywords)
				$sql .= " and (c.title like '%$searchKeywords%' or c.businessName like '%$searchKeywords%' or c.coupon_summery_description like '%$searchKeywords%') ";

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
			$this->setData($list,'group_buy_list');

		}
		
		
		$this->setData($this->parseUrl()->set('type','group_buy'),'group_buy_url');
		$this->setData($this->parseUrl()->set('type','group_pin'),'group_pin_url');

		$this->setData( $this->parseUrl()->set( 'alias' ), 'catUrl' );
		$this->setData( $this->parseUrl()->set( 'page' )->set( 'key' ), 'searchUrl' );


		$this-> display_pc_mobile('coupon_detail/inc/group_pin/group_index','coupon_detail/inc/group_pin/group_index_mobile');
	}
	
}
?>