<?php

/*
 @ctl_name = 充值管理@
*/

class ctl_recharge extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_recharge	= $this->loadModel('recharge');
		$where[]		=" (payment='".BalanceProcess::TYPE_RECHARGE."' or payment='".BalanceProcess::TYPE_SYS_SETTLEMENT_RECHARGE."') ";
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
		$this->setData($this->parseUrl()->set(), 'refreshUrl');
		$this->display();
	}


}

?>