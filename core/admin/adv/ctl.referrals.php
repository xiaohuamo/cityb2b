<?php

/*
 @ctl_name = 介绍人管理@
*/

class ctl_referrals extends adminPage
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
		$where[] = "name like '%$keyword%'";
		if ($onlyNotApproved > 0) $where['isApproved'] = 0;

		$mdl_referrals	= $this->loadModel('referrals');
		$order		= "createTime desc";
		$pageSql	= $mdl_referrals->getListSql(null, $where, $order);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_referrals->getListBySql($page['outSql']);

		$mdl_user = $this->loadModel( 'user' );
		foreach ( $data as $key => $val ) {
			$data[$key]['createUserName'] = $mdl_user->getUserDisplayName( $val['userId'] );
		}

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set( 'act', 'delete' ), 'delUrl');
		$this->setData( $this->parseUrl()->set( 'act', 'edit'), 'editUrl' );
		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData($this->parseUrl()->set(), 'refreshUrl');
		$this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
		$this->setData( $search, 'search' );
		$this->display();
	}

	public function edit_action () #act_name = 修改#
	{
		$id			= get2('id');

		$mdl_referals = $this->loadModel( 'referrals' );

		if (is_post()) {
			$data = post('data');
			$mdl_referals->update($data,$id);
			$this->sheader($this->parseUrl()->set('act','index')->set('id'));
		}else{

			$data = $mdl_referals->get($id);

			$this->setData($data, 'data');
			$this->setData($this->parseUrl()->set( 'act', 'delete' ), 'delUrl');
			$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
			$this->setData($this->parseUrl()->set(), 'refreshUrl');
			$this->setData($this->parseUrl()->set('act'), 'listUrl');
			$this->display();
		}
	}

	public function audit_action () #act_name = 审核#
	{
		if (is_post())
		{
			$ids = post('ids');
			if (is_array($ids))
			{
				foreach ($ids as $k=>$v)
				{
					self::_audit((int)$v);
				}
			}
		}
		else
		{
			self::_audit((int)get2('id'));
		}
		$this->sheader($this->parseUrl()->set( 'act' )->set( 'id' ));
	}

	public function unaudit_action () #act_name = 取消审核#
	{
		if (is_post())
		{
			$ids = post('ids');
			if (is_array($ids))
			{
				foreach ($ids as $k=>$v)
				{
					self::_unaudit((int)$v);
				}
			}
		}
		else
		{
			self::_unaudit((int)get2('id'));
		}
		$this->sheader($this->parseUrl()->set( 'act' )->set( 'id' ));
	}

	private function _audit ($id)
	{
		$id				= (int)$id;
		$mdl_referrals	= $this->loadModel('referrals');
		$link			= $mdl_referrals->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_referrals->update(array( 'isApproved' => 1, 'auditTime' => time(), 'auditUserId' => !$this->user ? 1 : (int)session('admin_user_id') ), $id))
		{
		}
		else
		{
			$this->sheader(null, "审核通过失败<br />id:$id");
		}
	}

	private function _unaudit ($id)
	{
		$id				= (int)$id;
		$mdl_referrals	= $this->loadModel('referrals');
		$link			= $mdl_referrals->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_referrals->update(array( 'isApproved' => 0 ), $id))
		{
		}
		else
		{
			$this->sheader(null, "取消审核失败<br />id:$id");
		}
	}

}

?>