<?php

/*
 @ctl_name = 抽奖活动管理@
*/

class ctl_lottery extends adminPage
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
		$where[] = "title like '%$keyword%'";
		if ($onlyNotApproved > 0) $where['is_approved'] = 0;

		$mdl_lottery	= $this->loadModel('lottery');
		$order		= "createtime desc";
		$pageSql	= $mdl_lottery->getListSql(null, $where, $order);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_lottery->getListBySql($page['outSql']);

		$mdl_user = $this->loadModel( 'user' );
		foreach ( $data as $key => $val ) {
			$data[$key]['createUser'] = $mdl_user->get( $val['createuserId'] );
			$data[$key]['businessName']=$mdl_user->getBusinessNameById($val['createUserId']);
			
				switch ($data[$key]['status'])
			{
			case 0:
				$data[$key]['status_desc'] ='准备中/下架';
				break;  
			case 1:
		        $data[$key]['status_desc'] ='商家审核完毕';
				break;
			case 2:
		        $data[$key]['status_desc'] ='活动结束';
				break;
			case 3:
		        $data[$key]['status_desc'] ='奖品抽完/关闭';
				break;
			default:
			    $data[$key]['status_desc'] ='状态号未知:'.$data[$key]['status'] ;
				break;
		
			} 
			
		}

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set( 'act', 'delete' ), 'delUrl');
		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
			$this->setData( $this->parseUrl()->set( 'act', 'edit' ), 'editUrl' );
		$this->setData($this->parseUrl()->set(), 'refreshUrl');
		$this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
		$this->setData( $search, 'search' );
		$this->display();
	}
	
	
	
	public function edit_action () #act_name = 编辑#
	{
		$id		= (int)get2('id');
		$mdl_lottery_records	= $this->loadModel('lottery_records');
		$data	= $mdl_lottery_records->get($id);
	
		
			
		if (is_post())
		{
			
			$data = post('data');
			$data['note']=$data['note'];
			
			if ( $mdl_lottery_records->update( $data, $id ) ) {
				
				
			}
			$this->sheader($this->parseUrl()->set('act')->set('id'));
		}
		else
		{
			$this->setData($data);
			$this->setData(self::_columnChk(), 'hideColumn');
			$this->setData($this->parseUrl()->set('act')->set('id'), 'listUrl');
			$this->display();
		}
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
		$mdl_lottery	= $this->loadModel('lottery');
		$link			= $mdl_lottery->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_lottery->update(array( 'is_approved' => 1, 'auditTime' => time(), 'auditUserId' => !$this->user ? 1 : (int)session('admin_user_id') ), $id))
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
		$mdl_lottery	= $this->loadModel('lottery');
		$link			= $mdl_lottery->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_lottery->update(array( 'is_approved' => 0 ), $id))
		{
		}
		else
		{
			$this->sheader(null, "取消审核失败<br />id:$id");
		}
	}

}

?>