<?php

/*
 @ctl_name = 取现管理@
*/

class ctl_withdraw extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_recharge	= $this->loadModel('recharge');
		$where[]		=" (payment='".BalanceProcess::TYPE_WITHDRAW."' or payment='".BalanceProcess::TYPE_SYS_SETTLEMENT_WITHDRAW."') ";
		$order		= "createTime desc";
		$pageSql	= $mdl_recharge->getListSql(null, $where, $order);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_recharge->getListBySql($page['outSql']);

		$mdl_user = $this->loadModel( 'user' );
		foreach ( $data as $key => $val ) {
			$data[$key]['createUser'] = $mdl_user->getDisplayName( $val['userId'] );
		}

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set( 'act', 'delete' ), 'delUrl');
		$this->setData($this->parseUrl()->set( 'act', 'view' ), 'viewUrl');
		$this->setData($this->parseUrl()->set(), 'refreshUrl');
		$this->display();
	}


	public function view_action () #act_name = 查看详细#
	{	
		
		$orderId			= get2('id');

		if(!$orderId)$this->sheader(null,'系统错误，单号缺失');

		$mdl_recharge	= $this->loadModel('recharge');
		$mdl_user_account_info	= $this->loadModel('user_account_info');

		$where['orderId']=$orderId;

		$data = $mdl_recharge->getByWhere($where);

		if($data){
			//exist
			$userId=$data['userId'];
			$accountInfo = $mdl_user_account_info->getByWhere(array('userid'=>$userId));

			$this->setData($data,'data');
			$this->setData($accountInfo,'accountInfo');

			$this->setData($this->parseUrl()->set( 'act', 'approve' ), 'submitUrl');

			$this->display();

		}else{
			$this->sheader(null,'系统错误，无此单');
		}

		
	}

	public function approve_action() #act_name = 审批#
	{
		$id = get2('id');
		$action = post('action');
		$note = trim(post('note'));

		if(!$id)$this->sheader(null,'系统错误，单号缺失');

		$mdl_recharge	= $this->loadModel('recharge');

		if($action =='approve'){
			$mdl_recharge->updataTransactionStatus($id,BalanceProcess::SETTLE);
		}elseif($action=='decline'){
			$mdl_recharge->updataTransactionStatus($id,BalanceProcess::VOID);
		}elseif($action=='processing'){
			$mdl_recharge->updataTransactionStatus($id,BalanceProcess::PENDING);
		}

		if($note)$mdl_recharge->updataNote($id,$note);

		$this->sheader($this->parseUrl()->set('act')->set('id'));
	}
}

?>