<?php

/*
 @ctl_name = 退货退款中心@
*/

class ctl_dispute_center extends adminPage
{

	public function list_action () #act_name = 列表#
	{	

		$keyword=get2('keyword');
		$onlyActionRequired=get2('onlyActionRequired');

		$type=get2('type');

		$mdl_dispute_center=$this->loadModel('dispute_center');
		$mdl_user = $this->loadModel('user');
		$mdl_order = $this->loadModel('order');

		$loginUserType=CaseActionStep::PLATFORM;

		$where=null;

		$order_by= 'id desc';
		
		if(!$onlyActionRequired){
			if($keyword){
				$where=" (id like '%$keyword%' or order_id like '%$keyword%') ";
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
		}else{
			if($keyword){
				$where=" (id like '%$keyword%' or order_id like '%$keyword%') and status='open' ";
			}else{
				$where=" status='open' ";
			}

			$pageSql =$mdl_dispute_center->getListSql(null,$where,$order_by);
		
	    	$data = $mdl_dispute_center->getListBySql($pageSql);

	        foreach ($data as $key => $value) {
	        	$action = unserialize($value['action']);
	        	$requrieAction=($action->getCurrentStep()->required_user==$loginUserType);

	        	if($requrieAction){
	        		$data[$key]['case_creator_name'] = $mdl_user->getUserDisplayName($value['case_creator_id']);
		        	$data[$key]['order_name'] = $mdl_order->generateOrderName($value['order_id']);
		        	$data[$key]['requrieAction']=$requrieAction;
	        	}else{
	        		unset($data[$key]);
	        	}
	        }
		}

		$search['keyword']=$keyword;
		$search['onlyActionRequired']=$onlyActionRequired;

        $this->setData($this->parseUrl()->set( 'keyword' )->set( 'onlyActionRequired' )->set('page'), 'searchUrl');

		$this->setData($data);

		$this->setData($search,'search');

		$this->display();
	}

	public function view_action () #act_name = 详情#
	{	
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

		$info['loginUserType']= CaseActionStep::PLATFORM;

		$this->setData($this->parseUrl()->set('act','complete_current_step')->set('id',$id),'complete_current_step_url');

		$this->setData($this->parseUrl()->set('act','list')->set('id'),'listUrl');

		$this->setData($case,'data');

		$this->setData($info,'info');

		$this->setData($action,'action');

		$this->display();
	}

	public function create_action () #act_name = 创建#
	{	

		if(is_post()){
			$mdl_dispute_center=$this->loadModel('dispute_center');

			$owner   = $this->loginUser['id'];
			$reason  = trim(post('reason'));
			$orderId = post('orderId');

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

			$orderList = $mdl_order->getList(null,$where);

			$this->setData($orderList,'orderList');

			$this->display('dispute_center/create');
			
		}
		
	}

	public function complete_current_step_action () #act_name = 操作#
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

		$this->sheader($this->parseUrl()->set('act','view')->set('id',$case_id));

	}
}