<?php

/*
 @ctl_name = 个人会员管理@
*/

class ctl_member extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$role				= 4;
		$type				= get2('type');
		$keyword			= get2('keyword');
		$onlyNotApproved	= limitInt(get2('onlyNotApproved'), 0, 1);
		$search = array(
		//	'role'				=> $role,
			'type'				=> $type,
			'keyword'			=> $keyword,
			'onlyNotApproved'	=> $onlyNotApproved
		);

		$user	= $this->loadModel('user');
		$roles	= $this->loadModel('role');

		$where	= array();
		if ($role > 0) $where['0#role'] = $role;

		$where[] = " (t0.phone ='$keyword' or t0.email like '%$keyword%' or t0.name like '%$keyword%' or t0.id ='$keyword') ";
		//如果登录 用户组为6 role=6 ,那么是属于商家代理组
		// 这样的管理员只能看到他的商家的积分券信息
		if($this->user['role']==6 || $this->user['role']==11){
			//$where[] ="user_belong_to_agent=". $_SESSION['admin_user_id'];
		
		}
		
		
		if ($onlyNotApproved > 0) $where['0#isApproved'] = 0;
        if ($this->user['role']!=6 && $this->user['role']!=11) {
			$pageSql	= $user->getAllUserListSql($where);
		}else{
	//	$pageSql = "select a.* from cc_user a   where  a.id in (select b.userId from cc_order b where b.business_userId in (select c.id from cc_user c where c.user_belong_to_agent =". $_SESSION['admin_user_id'] ." )  ) and (a.phone ='$keyword' or a.email like '%$keyword%' or a.name like '%$keyword%' or a.id ='$keyword')";
			//var_dump($pageSql);exit;
			
			$pageSql = "select a.* from cc_user a   where a.phone =$keyword or a.email like '%$keyword%' or a.name like '%$keyword%' or a.id ='$keyword'";
			//var_dump($pageSql);exit;
		}
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 15;
		$maxPage	= 5;

		$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data = $this->_out($user->getAllUserListBySql($page['outSql']));
		if(!$data) {
			//没有搜索刀
			$sql ="select * from cc_user where id in (select userId from cc_order where orderId=$keyword)";
			//var_dump($sql);exit;
			$mdl_order =$this->loadModel('order');
			$data =$mdl_order->getListBySql($sql);
			
		}

		$this->setData($data);
		$this->setData($page['pageStr'], 'pager');
		$this->setData($search, 'search');
		$this->setData($this->user_id, 'user_id');
		$this->setData(self::_columnChk(), 'hideColumn');
		$this->setData($this->parseUrl(), 'listUrl');
		$this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
		$this->display();
	}

	public function edit_action () #act_name = 编辑#
	{
		$id		= (int)get2('id');
		$user	= $this->loadModel('user');
		$data	= $user->getUserById($id);
		$oldData = $data;

		if ($data['role'] != 4)
		{
			$this->sheader(null, $this->lang->no_permission_to_edit_this_user);
		}

		if (is_post())
		{
			$data = post('data');
			
			if ($data = $this->_filterForEdit($data))
			{
				if ( $user->updateUserById( $data, $id ) ) {
					if ( $data['avatar'] && $oldData['avatar'] ) {
						$this->file->deletefile( UPDATE_DIR.$oldData['avatar'] );
					}
					$this->sheader($this->parseUrl()->set('act')->set('id'));
				}
				else {
					if ( $data['avatar'] ) {
						$this->file->deletefile( UPDATE_DIR.$data['avatar'] );
					}
					$this->sheader(null, $this->lang->edit_user_failed);
				}
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
			$this->setData($data);
			$this->setData(self::_columnChk(), 'hideColumn');
			$this->setData($this->user_id, 'user_id');
			$this->setData($this->parseUrl()->set('act')->set('id'), 'listUrl');
			$this->display();
		}
	}
	
	
	public function recharge_action () #act_name = 编辑#
	{
		$id		= (int)get2('id');
		$user	= $this->loadModel('user');
		$mdl_recharge = $this->loadModel('recharge');
		$data	= $user->getUserById($id);
		
		// 获得该用户上次充值时间
		
		$sql ="select * from cc_recharge where userId =$id and payment='recharge' order by id desc";
		$recharge_rec =$mdl_recharge->getListBySql($sql);
		if($recharge_rec) {
			$this->setData($recharge_rec[0]['money'],'recharge_money');
			$this->setData(date('Y-m-d H:i:s',$recharge_rec[0]['createTime']),'recharge_time');
			$last_recharge_hours = (time()-$recharge_rec[0]['createTime'])/(60*60);
			if($last_recharge_hours<24) 
			$this->setData(1,'block_recharge');
			//var_dump($last_recharge_hours);exit;
		}
       
	

		if (is_post())
		{
			$data = post('data');
			
			if ($data = $this->_filterForRecharge($data))
			{
				$data['userId']=$id;
				//var_dump($data);exit;
				if ( $mdl_recharge->insert( $data ) ) {
					
					$this->sheader($this->parseUrl()->set('act')->set('id'));
				}
				else {
					
					$this->sheader(null, $this->lang->edit_user_failed);
				}
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
			$this->setData($data);
			$this->setData(self::_columnChk(), 'hideColumn');
			$this->setData($this->user_id, 'user_id');
			$this->setData($this->parseUrl()->set('act')->set('id'), 'listUrl');
			$this->display();
		}
	}

	/*public function delete_action () #act_name = 删除#
	{
		$id		= (int)get2('id');
		$user	= $this->loadModel('user');
		$data	= $user->getUserById($id);
		if ($data['role'] != 4)
		{
			$this->sheader(null, $this->lang->current_user_no_right_delete);
		}
		if ($user->deleteUserById($id)) {
			
			$this->sheader($this->parseUrl()->set('act')->set('id'));
		}
		else $this->sheader(null, $this->lang->delete_user_failed);
	}*/

	private function _filterForEdit ($data)
	{
		foreach ($data as $key=>$value)
		{
			$data[$key] = trim($value);
		}

		if ($data['password'] != '')
		{
			if (strlen($data['password']) < 6 || $data['password'] != $data['password2']) $this->sheader(null, $this->lang->password_length_error_or_not_match);
			$data['password']	= $this->md5($data['password']);
		}
		else
		{
			$data = $this->array_splice($data, 'password');
		}

		$data['isAdmin']		= 0;
		$data['isApproved']		= (int)$data['isApproved'];
		$data['groupid']		= 1;
		$data['role']			= 4;
		$data['roleExtendType']	= 1;

		return $this->array_splice($data, 'password2');
	}
	
	
	private function _filterForRecharge ($data)
	{
		foreach ($data as $key=>$value)
		{
			$data[$key] = trim($value);
		}

		if ($data['money'] >10)
		{
			 $this->sheader(null, '充值金额不能高于10刀');
			
		}
	 // $orderId = '101'.date( 'YmdHis' ).$this->createRnd(3);
	 // $data['orderId'] =$orderId;
	
	  $data['createTime'] = time();
	  $data['status']=1;
	  $data['note']='system manual recharge';
//	  $data['wj_customer_coupon_id']=$this->user_id;

		return $data;
	}

	private function _out ($data)
	{
		$mdl_city = $this->loadModel( 'city' );
		foreach ($data as $key=>$value)
		{
			if ( $value['lastLoginDate'] ) {
				$data[$key]['lastLoginDate']		= date('Y-m-d H:i', $value['lastLoginDate']);
			}
			else {
				$data[$key]['lastLoginDate']		= '-';
			}
			$data[$key]['createdDate']				= date('Y-m-d H:i', $value['createdDate']);
			$data[$key]['lastModifiedDate']			= date('Y-m-d H:i', $value['lastModifiedDate']);
			$data[$key]['lastPasswordChangedDate']	= date('Y-m-d H:i', $value['lastPasswordChangedDate']);

			$loc = $mdl_city->get( $value['city'] );
			$data[$key]['cityName'] = $loc['name'];
		}

		return $data;
	}

	private function _columnChk ()
	{
		$hide = array(
			'user_add'				=> $this->chkAction('adv/member/add'),
			'user_edit'				=> $this->chkAction('adv/member/edit'),
			'user_delete'			=> $this->chkAction('adv/member/delete')
		);
		return $hide;
	}

}

?>