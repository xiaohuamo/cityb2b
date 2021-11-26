<?php

/*
 @ctl_name = abn acn 认证@
*/

class ctl_abnacn extends adminPage
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

		$mdl_abn_application	= $this->loadModel('wj_abn_application');
		$order		= "createDate desc";
		$pageSql	= $mdl_abn_application->getListSql(null, $where, $order);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_abn_application->getListBySql($page['outSql']);

	
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
		$mdl_abn_application	= $this->loadModel('wj_abn_application');
		$link			= $mdl_abn_application->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
	
		if ($mdl_abn_application->update(array( 'isApproved' => 1, 'auditTime' => time(), 'auditUserId' => !$this->user ? 1 : (int)session('admin_user_id') ), $id))
		{
			// 审核成功后，将该商家的用户级别trustLevel 从 0 提升为1
			$mdl_user = $this->loadModel('user');
			$data = array(
				'trustLevel'=>'1'	
			);
			if($mdl_user->update($data,$link['userId'])) {
				
				
			}else{
				
				$this->sheader(null, "商家信用级别提升失败<br />id:$id");
			}
						
			
		}
		else
		{
			$this->sheader(null, "审核通过失败<br />id:$id");
		}
	}

	
	private function _delete ($id)
	{
		$id				= (int)$id;
		$mdl_location	= $this->loadModel('wj_abn_application');
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
		$this->sheader('?con=admin&ctl=adv/abnacn');
	}
	
	
	private function _unaudit ($id)
	{
		$id				= (int)$id;
		$mdl_abn_application	= $this->loadModel('wj_abn_application');
		$link			= $mdl_abn_application->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_abn_application->update(array( 'isApproved' => 0 ), $id))
		{
			// 审核成功后，将该商家的用户级别trustLevel 从 0 提升为1
			$mdl_user = $this->loadModel('user');
			$data = array(
					'trustLevel'=>'0'
			);
			if($mdl_user->update($data,$link['userId'])) {
			
			
			}else{
			
				$this->sheader(null, "商家信用级别取消失败<br />id:$id");
			}
			
			
		}
		else
		{
			$this->sheader(null, "取消审核失败<br />id:$id");
		}
	}

}

?>