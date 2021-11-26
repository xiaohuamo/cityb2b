<?php

class ctl_dispute_center extends cmsPage
{

	public function list_action()
	{	
		$type=get2('type');

		$mdl_dispute_center=$this->loadModel('dispute_center');
		$mdl_user = $this->loadModel('user');
		$mdl_order = $this->loadModel('order');

		$loginUserId = $this->loginUser['id'];
		$loginUserType ;

		$order_by= 'id desc';

		if($type=='customer'){
			$where="(customer_id =$loginUserId or case_creator_id= $loginUserId)";
			$loginUserType=CaseActionStep::USER;

		}elseif($type=='business'){
			$where="(business_id =$loginUserId or case_creator_id= $loginUserId)";
			$loginUserType=CaseActionStep::BUSINESS;

		}else{
			$this->sheader(null,'是商家还是用户');
		}

		$pageSql =$mdl_dispute_center->getListSql(null,$where,$order_by);
		$pageUrl = $this->parseUrl()->set('page');
        $pageSize = 5;
        $maxPage = 10;
        $page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
        $data = $mdl_dispute_center->getListBySql($page['outSql']);


        $this->setData($page['pageStr'],'pager');

        foreach ($data as $key => $value) {
        	$data[$key]['case_creator_name'] = $mdl_user->getUserDisplayName($value['case_creator_id']);
        	$data[$key]['order_name'] = $mdl_order->generateOrderName($value['order_id']);

        	$action = unserialize($value['action']);
        	$data[$key]['requrieAction']=($action->getCurrentStep()->required_user==$loginUserType);
        }

        if($type=='customer'){
			$this->setData('myorder','menu');
			$this->setData('dispute_center','submenu');
		}elseif($type=='business'){
			$this->setData('online_center','menu');
			$this->setData('dispute_center','submenu');
		}

        $this->setData($type,'type');

		$this->setData($data);

		$this->display('dispute_center/list');
	}

	public function view_action()
	{	
		$type=get2('type');

		$id= get2('id');

		$mdl_dispute_center=$this->loadModel('dispute_center');
		$mdl_user = $this->loadModel('user');
		$mdl_order = $this->loadModel('order');

		$case = $mdl_dispute_center->get($id);

		$info['case_creator_name'] = $mdl_user->getUserDisplayName($case['case_creator_id']);
		$info['customer_name']     = $mdl_user->getUserDisplayName($case['customer_id']);
		$info['business_name']     = $mdl_user->getBusinessDisplayName($case['business_id']);
		$info['order_name']        = $mdl_order->generateOrderName($case['order_id']);
		$info['actionDesc']        = $mdl_dispute_center->actionDesc($case['action_type']);

		$action = unserialize($case['action']);

		if($this->loginUser['id']==$case['business_id']&&$this->loginUser['id']==$case['customer_id']){
			$ru = $action->getCurrentStep()->required_user;
			if($ru!= CaseActionStep::PLATFORM){
				$info['loginUserType']= $ru;
			}

		}elseif($this->loginUser['id']==$case['customer_id']){
			$info['loginUserType']=CaseActionStep::USER;

		}elseif($this->loginUser['id']==$case['business_id']){
			$info['loginUserType']=CaseActionStep::BUSINESS;

		}

		// $info['loginUserType']= CaseActionStep::PLATFORM;
		// $info['loginUserType']= CaseActionStep::BUSINESS;
		

		$this->setData(HTTP_ROOT_WWW.'dispute_center/complete_current_step?id='.$id,'complete_current_step_url');

		$this->setData($case,'data');

		$this->setData($info,'info');

		$this->setData($action,'action');

		$this->setData($type,'type');

		if($type=='customer'){
			$this->setData('myorder','menu');
			$this->setData('dispute_center','submenu');
		}elseif($type=='business'){
			$this->setData('online_center','menu');
			$this->setData('dispute_center','submenu');
		}

		$this->display('dispute_center/view');
	}

	public function create_action()
	{	

		if(is_post()){
			$mdl_dispute_center=$this->loadModel('dispute_center');

			$owner   = $this->loginUser['id'];
			$reason  = trim(post('reason'));
			$orderId = post('orderId');

			if(!$owner)
				$this->form_response(500,'缺少提交用户');

			if(!$reason)
				$this->form_response(500,'请填写退货原因');

			if(!$orderId)
				$this->form_response(500,'请选择退货订单');

			$mdl_dispute_center->owner($owner);
			$mdl_dispute_center->reason($reason);
			$mdl_dispute_center->order($orderId);

			$case_id = $mdl_dispute_center->openNewCase();

			$this->form_response(200,'提交成功',HTTP_ROOT_WWW.'dispute_center/view?id='.$case_id);

		}else{
			$type =get2('type');

			//view page
			$mdl_order=$this->loadModel('order');

			if($type=='business'){
				
				$where['business_userId']=$this->loginUser['id'];

			}elseif($type=='customer'){

				$where['userId']=$this->loginUser['id'];

			}else{
				$this->sheader(null,'提交投诉申请需要明确您是商家还是用户');
			}
			$where['coupon_status']='b01';
			$orderby='id desc';

			$orderList = $mdl_order->getList(null,$where,$orderby);

			$this->setData($orderList,'orderList');
			$this->setData('myorder', 'menu');
			$this->setData('dispute', 'submenu');
			$this->display('dispute_center/create');
			
		}
		
	}

	public function complete_current_step_action()
	{
		$case_id =get2('id');

		$mdl_dispute_center=$this->loadModel('dispute_center');
		$mdl_user = $this->loadModel('user');
		$mdl_order = $this->loadModel('order');

		$case = $mdl_dispute_center->getCase($case_id);

		$case->action->completeCurrentStep();

		if($case->action->isActionComplete()){
			$case->status=mdl_dispute_center::CLOSE;
		}

		$case->updateCase();

		$this->sheader(HTTP_ROOT_WWW.'dispute_center/view?id='.$case_id);

	}
}