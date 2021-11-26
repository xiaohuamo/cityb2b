<?php

/*
 @ctl_name = busi_pay_setting_application 认证@
*/

class ctl_busi_pay_setting_application extends adminPage
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
		$where[] = "business_name like '%$keyword%'";
		
		if($this->user['role']==6){
			//	$where[] ="user_belong_to_agent=". $_SESSION['admin_user_id'];
			$where[] ="userId in (select id from #@_user where user_belong_to_agent =" . $_SESSION['admin_user_id'].")";
		}
		
		if ($onlyNotApproved > 0) $where['isApproved'] = 0;

		$mdl_busi_pay_setting_application	= $this->loadModel('wj_busi_pay_setting_application');
		$order		= "createDate desc";
		$pageSql	= $mdl_busi_pay_setting_application->getListSql(null, $where, $order);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_busi_pay_setting_application->getListBySql($page['outSql']);

	
		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set( 'act', 'delete' ), 'delUrl');
		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData($this->parseUrl()->set(), 'refreshUrl');
		$this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
		$this->setData( $search, 'search' );
		$this->display();
	}

	public function audit_action () #act_name = audit#
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

	public function unaudit_action () #act_name = unaudit#
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
		$mdl_busi_pay_setting_application	= $this->loadModel('wj_busi_pay_setting_application');
		$link			= $mdl_busi_pay_setting_application->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		$where =array(
			'id'=>$id,
			'isApproved' => 2		
		);
		if ($mdl_busi_pay_setting_application->updatebyWhere(array( 'isApproved' => 1, 'auditTime' => time(), 'auditUserId' => !$this->user ? 1 : (int)session('admin_user_id') ), $where))
		{
		}
		else
		{
			$this->sheader(null, "审核通过失败<br />id:$id");
		}
	}

	
	private function _delete ($id)
	{
		$id				= (int)$id;
		$mdl_location	= $this->loadModel('wj_busi_pay_setting_application');
		$link			= $mdl_location->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_location->delete($id))
		{
		}
		else
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}
	
	public function delete_action () #act_name = delete#
	{
		if (is_post())
		{
			$ids = post('ids');
			if (is_array($ids))
			{
				foreach ($ids as $k=>$v)
				{
					self::_delete((int)$v);
				}
			}
		}
		else
		{
			self::_delete((int)get2('id'));
		}
	}
	
	
	private function _unaudit ($id)
	{
		$id				= (int)$id;
		$mdl_busi_pay_setting_application	= $this->loadModel('wj_busi_pay_setting_application');
		$link			= $mdl_busi_pay_setting_application->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_busi_pay_setting_application->update(array( 'isApproved' => 0 ), $id))
		{
		}
		else
		{
			$this->sheader(null, "取消审核失败<br />id:$id");
		}
	}

}

?>