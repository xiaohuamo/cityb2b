<?php

class ctl_coupon_event_management extends cmsPage
{
	function __construct()
	{	
		parent::cmsPage();

		if (!$this->loginUser) 
            $this->sheader(HTTP_ROOT_WWW . 'member/login?returnUrl=' . urlencode($_SERVER['REQUEST_URI']));
            
	}

	public function list_action()
	{	
		$column=['title','id'];
		$where['createUserId']=$this->loginUser['id'];
			$where['isApproved']=1;
				$where['status']=4;
		$where[]= " (bonusType in (7,9)) ";

		$couponList= $this->loadModel('coupons')->getList($column,$where);

		$list = $this->loadModel('coupon_event_management')->getListOfUser($this->loginUser['id']);

		foreach ($couponList as $key => $value) {
			foreach ($list as $l) {
				if($value['id']==$l['coupon_id'])
					unset($couponList[$key]);
			}
		}

		$this->setData($couponList,'couponList');

		$this->setData($list,'list');

		$this->setData("Ubonus秒杀活动产品委托管理",'pageTitle');

		$this->display('company/coupon_event_management/list');

	}
	public function add_to_list_action()
	{	
		$coupon_id =  get2('coupon_id');
		
		if($this->loadModel('coupon_event_management')->addToList($coupon_id))
			$this->sheader(HTTP_ROOT_WWW.'coupon_event_management/list');
		else
			$this->sheader();
	}

	public function remove_from_list_action()
	{	
		$coupon_id =  get2('coupon_id');

		if($this->loadModel('coupon_event_management')->removeFromList($coupon_id))
			$this->sheader(HTTP_ROOT_WWW.'coupon_event_management/list');
		else
			$this->sheader();
	}

	public function submit_for_approval_action()
	{
		$coupon_id =  get2('coupon_id');

		if($this->loadModel('coupon_event_management')->updateStatus($coupon_id,CouponEventStatus::Processing)){

			//自动增加 ubonus shop 为自己的兑付员工
			$businessId=$this->loginUser['id'];
			$userid = UBONUSSHOPID;
			if(!$this->loadModel('redeem_staff')->existInCompany($userid, $businessId)){
        		$this->loadModel('redeem_staff')->joinCompany($userid, $businessId);
			}
			$this->sheader(HTTP_ROOT_WWW.'coupon_event_management/list');
		}
		else{
			$this->sheader();
		}
	}

	public function withdraw_from_event_action()
	{
		$coupon_id =  get2('coupon_id');

		if($this->loadModel('coupon_event_management')->updateStatus($coupon_id,CouponEventStatus::Init))
			$this->sheader(HTTP_ROOT_WWW.'coupon_event_management/list');
		else
			$this->sheader();
	}

	public function edit_coupon_action()
	{
		$coupon_id =  get2('coupon_id');

		$coupon = $this->loadModel('coupons')->get($coupon_id );

		if ($coupon['bonusType']==9) {
		 	$this->sheader(HTTP_ROOT_WWW.'company/shops_edit?id='.$coupon_id);
		}else{
			$this->sheader(HTTP_ROOT_WWW.'company/coupons_edit?id='.$coupon_id);
		} 
	}
	

}