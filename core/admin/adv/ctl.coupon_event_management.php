<?php

/*
 @ctl_name = 活动产品审批@
*/

class ctl_coupon_event_management extends adminPage
{

	public function index_action () #act_name = 列表#
	{	

		$keyword			= get2('keyword');
		$onlyNotApproved	= limitInt(get2('onlyNotApproved'), 0, 1);
		$search = array(
			'keyword'			=> $keyword,
			'onlyNotApproved'	=> $onlyNotApproved
		);
		$where	= array();
		$where[] = "coupon_title like '%$keyword%'";
		
		if ($onlyNotApproved > 0) $where[] = "status<>2";

		$coupon_event_management	= $this->loadModel('coupon_event_management');
		$where[]	= "status != 0";
		$order		= " id desc";
		$pageSql	= $coupon_event_management->getListSql(null, $where, $order);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 50;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $coupon_event_management->getListBySql($page['outSql']);
		$this->setData($data, 'data');

		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
		$this->setData($this->parseUrl()->set( 'act', 'update' ), 'updateUrl');
		$this->setData($this->parseUrl()->set( 'act', 'view' ), 'viewUrl');
		$this->setData( $search, 'search' );
		$this->display();
	}

	public function qty_view_action () #act_name = 库存监控#
	{	
		$onlyApproved	= limitInt(get2('onlyApproved'), 0, 1);

		$search = array(
			'onlyApproved'	=> $onlyApproved
		);

		if ($onlyApproved > 0) $where[] = "status=2";

		$list = $this->loadModel('coupon_event_management')->getList(null,$where);

		$mdl_coupons=loadModel('coupons');
		$mdl_coupons_sub=loadModel('coupons_sub');
		$mdl_shop_stock=loadModel('shop_stock');
		$mdl_shop_guige=loadModel('shop_guige');
		$mdl_user=loadModel('user');

		foreach ($list as $key =>$value) {
			$coupon_id = $value['coupon_id'];
			$coupon = $mdl_coupons->get($coupon_id);

			$list[$key]['qty']=$coupon['qty'];
			$list[$key]['buy']=$coupon['buy'];
			$list[$key]['stage_qty']=$coupon['stage_qty'];

			$sub_coupons = $mdl_coupons_sub->getList(['title','quantity','buy','stage_qty'],array('parent_coupon_id'=>$coupon_id));
			$list[$key]['sub_coupons']=$sub_coupons;


			$couponHasGuige=$mdl_shop_guige->couponHasGuige($coupon_id);
			$list[$key]['couponHasGuige']=$couponHasGuige;

			$stock=$mdl_shop_stock->getFullStockData($coupon_id);
			$mdl_shop_guige_details=loadModel('shop_guige_details');
			foreach ($stock as $k=>$v) {
				$full_guige_desc = $mdl_shop_guige_details->getGuigeName($v['guige1Id'])." ".$mdl_shop_guige_details->getGuigeName($v['guige2Id']);

				if($v['guige1Id']==-1&&$v['guige2Id']==-1)$full_guige_desc='系统默认规格';
				$stock[$k]['guige_desc']=$full_guige_desc;
			}
			$list[$key]['stock']=$stock;

			$user=$mdl_user->get($value['user_id']);
			if(!$user['supportofflinepayment']&&!$user['supportpaypalpayment']&&!$user['supportroyalpaypayment']&&!$user['supporthcashpayment']){
				$list[$key]['payment']='';
			}else{
				$list[$key]['payment']=$user['supportofflinepayment'].'|'.$user['supportpaypalpayment'].'|'.$user['supportroyalpaypayment'].'|'.$user['supporthcashpayment'];
			}

		}

		$this->setData($this->parseUrl()->set('type')->set('onlyApproved'), 'searchUrl');
		$this->setData( $search, 'search' );
		$this->setData($list);
		$this->display();
	}

	public function qty_stage_action () #act_name = 库存分割#
	{	
		$this->loadModel('stage_qty')->qtySegmentationProcess();
		$this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/coupon_event_management&act=qty_view&onlyApproved=1');
	}

	public function qty_fill_action () #act_name = 补库存#
	{
		$this->loadModel('stage_qty')->nextStage();
		$this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/coupon_event_management&act=qty_view&onlyApproved=1');
	}

	public function event_start_action () #act_name = 启动活动#
	{
		$mdl_coupon_event_managemen=$this->loadModel('coupon_event_management');

		$where['status']=CouponEventStatus::Approved;

		$data['status']=CouponEventStatus::EventRunning;
		$data['note']=$mdl_coupon_event_managemen->defaultStatusNote(CouponEventStatus::EventRunning);

		if($mdl_coupon_event_managemen->updateByWhere($data,$where))
			$this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/coupon_event_management&act=index');
		else
			$this->sheader();
	}

	public function event_reset_action () #act_name = 重置活动#
	{
		$mdl_coupon_event_managemen=$this->loadModel('coupon_event_management');

		$where=[];
		
		$data['status']=CouponEventStatus::Init;
		$data['note']=$mdl_coupon_event_managemen->defaultStatusNote(CouponEventStatus::Init);

		if($mdl_coupon_event_managemen->updateByWhere($data,$where))
			$this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/coupon_event_management&act=index');
		else
			$this->sheader();
	}



	public function update_action () #act_name = 审批#
	{
		$ids=post( 'ids' );

		$id = get2( 'id' );

		$status=get2( 'status' );

		if(!$ids&&$id){
				if($this->loadModel('coupon_event_management')->updateStatus($id,$status))
					$this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/coupon_event_management&act=index');
				else
					$this->sheader();
		}else{	
				$id_str = join(',',$ids);

				$where=" coupon_id in ($id_str) ";
				$data['status']=$status;
				$data['note']=$this->loadModel('coupon_event_management')->defaultStatusNote($status);


				if(CouponEventStatus::Approved==$status){
					$cdata['status']=4;
				}else{
					$cdata['status']=1;
				}
				$this->loadModel('coupons')->updateByWhere($cdata," id in ($id_str) ");

				if($this->loadModel('coupon_event_management')->updateByWhere($data,$where))
					$this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/coupon_event_management&act=index');
				else
					$this->sheader();
		}
		
	}

	public function note_update_ajax_action () #act_name = Note#
	{	
		$where['coupon_id']=get2('id');
		$data['note']=get2('note');

		if($this->loadModel('coupon_event_management')->updateByWhere($data,$where))
			echo 'success';
		else
			echo 'fail';
	}


}

?>