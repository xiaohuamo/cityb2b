<?php

/*
 @ctl_name = 抽奖活动管理@
*/

class ctl_lottery_redeem extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$keyword			= get2('keyword');
		$status_id			= get2('status_id');
		$lottery_id			= get2('lottery_id');
		$onlyNotApproved	= limitInt(get2('onlyNotApproved'), 0, 1);
		$search = array(
			'keyword'			=> $keyword,
			'status_id'         =>$status_id,
			'lottery_id'         =>$lottery_id,
			'onlyNotApproved'	=> $onlyNotApproved
		);
		

		$mdl_lottery_records	= $this->loadModel('lottery_records');
		
		$pageSql = $mdl_lottery_records->getWinningRecordsOfBusiness_keywords_Sql($keyword,$status_id,$lottery_id);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_lottery_records->getListBySql($page['outSql']);

		
		$mdl_user = $this->loadModel( 'user' );
		foreach ( $data as $key => $val ) { 
			$data[$key]['businessName']=$mdl_user->getBusinessNameById($val['createUserId']);
			$where =array(
			  'userId='.$val['userId'].' or ref_userId ='.$val['userId']
			);
			$data[$key]['total_speed']= $mdl_lottery_records->getCount($where);
			
		}
		$mdl_lottery=$this->loadModel('lottery');
		$lottery_list=$mdl_lottery->getList(null,null,'id desc');
		$this->setData($lottery_list,'lottery_list');
		
		
		
		$status_list=$this->loadModel('lottery_status')->getList();
		$this->setData($status_list,'status_list');

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
	
	
	
	public function RedeemDone4_action () #act_name = audit#
	{
		if (is_post())
		{
			$ids = post('ids');
			if (is_array($ids))
			{
				foreach ($ids as $k=>$v)
				{
					self::_RedeemDone4((int)$v);
				}
			}
		}
		else
		{
			self::_RedeemDone4((int)get2('id'));
		}
		$this->sheader($this->parseUrl()->set( 'act' )->set( 'id' ));
	}
	
	private function _RedeemDone4 ($id)
	{
		$id				= (int)$id;
		$mdl_lottery_records	= $this->loadModel('lottery_records');
		$link			= $mdl_lottery_records->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_lottery_records->update(array( 'status' => 4 ), $id))
		{
		}
		else
		{
			$this->sheader(null, "审核通过失败<br />id:$id");
		}
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
		$mdl_lottery_records	= $this->loadModel('lottery_records');
		$link			= $mdl_lottery_records->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_lottery_records->update(array( 'status' => 3 ), $id))
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
		$mdl_lottery_records	= $this->loadModel('lottery_records');
		$link			= $mdl_lottery_records->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
	if ($mdl_lottery_records->update(array( 'status' => 4 ), $id))
		{
		}
		else
		{
			$this->sheader(null, "取消审核失败<br />id:$id");
		}
	}

}

?>