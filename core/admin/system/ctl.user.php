<?php

/*
 @ctl_name = 用户管理@
*/

class ctl_user extends adminPage
{

	public function index_action () #act_name = 用户列表#
	{
		$role				= (int)get2('role');
		$type				= get2('type');
		$keyword			= get2('keyword');
		$onlyNotApproved	= limitInt(get2('onlyNotApproved'), 0, 1);
		$search = array(
			'role'				=> $role,
			'type'				=> $type,
			'keyword'			=> $keyword,
			'onlyNotApproved'	=> $onlyNotApproved
		);

		$user	= $this->loadModel('user');
		$roles	= $this->loadModel('role');

		$where	= array();
		if ($role > 0) $where['0#role'] = $role;
		
		$where[]=" (t0.name like '%$keyword%' or t0.id like '%$keyword%') ";

		if ($onlyNotApproved > 0) $where['0#isApproved'] = 0;

		$pageSql	= $user->getAllUserListSql($where);
		$pageUrl	= '?con=admin&ctl=system/user&';
		$pageSize	= 15;
		$maxPage	= 5;

		$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data = $this->_out($user->getAllUserListBySql($page['outSql']));

		$rolelist = $roles->getRoleList();

		$this->setData($data);
		$this->setData($page['pageStr'], 'pager');
		$this->setData($rolelist, 'rolelist');
		$this->setData($search, 'search');
		$this->setData($this->user_id, 'user_id');
		$this->setData(self::_columnChk(), 'hideColumn');
		$this->display();
	}

	public function add_action () #act_name = 添加用户#
	{
		$user = $this->loadModel('user');

		if (is_post())
		{
			$data = post('data');
			if ($data = $this->_filter($data))
			{
				if ($user->chkUserName($data['name']) > 0) $this->sheader(null, $this->lang->user_name_exists); 

				$userObject = new User();
				$userObject->setName($data['name']);
				$userObject->setEmail($data['email']);
				$userObject->setRole($data['role']);
				$userObject->setPassword($data['password']);
				$userObject->setPassword($data['agent_commission_rate']);
				$userObject->setIsAdmin($data['isAdmin']);

				if ($user->addUser($userObject->toDBArray())) $this->sheader('?con=admin&ctl=system/user');
				else $this->sheader(null, $this->lang->add_user_failed);
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
			$role		= $this->loadModel('role');
			if ($this->user_id == -1)
			{
				$extendBy	= $user->getAllUserListWithoutById("id<>'$id'");
				$roles		= $role->getRoleList();
			}
			else
			{
				$extendBy	= $user->getAllUserListWithoutById("id<>'$id' and groupid=1");
				$roles		= $role->getRoleList("isSuper=0");
			}
			$this->setData($roles, 'roles');
			$this->setData($extendBy, 'extendBy');
			$this->setData($this->user_id, 'user_id');
			$this->display();
		}
	}

	public function edit_action () #act_name = 编辑用户#
	{
		$id		= (int)get2('id');
		$user	= $this->loadModel('user');
		$data	= $user->getUserById($id);

		if ($data['groupid'] != 1 && $this->user_id != -1)
		{
			$this->sheader(null, $this->lang->no_permission_to_edit_this_user);
		}

		if (is_post())
		{
			$data = post('data');
			
			if ($data = $this->_filterForEdit($data))
			{
				//复制
				$extendBy = (int)post('extendBy');
				if ($extendBy > 0)
				{
					$extendUser			= $user->getUserById($extendBy);
					$data['role']		= $extendUser['role'];
					$data['action']		= $extendUser['action'];
					$data['infoClass']	= $extendUser['infoClass'];
					$data['relation']	= $extendUser['relation'];
					$data['agent_commission_rate']	= $extendUser['agent_commission_rate'];
					$data['isAdmin']	= $extendUser['isAdmin'];
					

				}
				if ($this->user_id != -1)
				{
					$data['groupid'] = 1;
				}

				if ($user->updateUserById($data, $id)) $this->sheader('?con=admin&ctl=system/user');
				else $this->sheader(null, $this->lang->edit_user_failed);
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
		}
		else
		{
			$role		= $this->loadModel('role');
			if ($this->user_id == -1)
			{
				$extendBy	= $user->getAllUserListWithoutById("id<>'$id'");
				$roles		= $role->getRoleList();
			}
			else
			{
				$extendBy	= $user->getAllUserListWithoutById("id<>'$id' and groupid=1");
				$roles		= $role->getRoleList("isSuper=0");
			}
			$this->setData($data);
			$this->setData($roles, 'roles');
			$this->setData($extendBy, 'extendBy');
			$this->setData(self::_columnChk(), 'hideColumn');
			$this->setData($this->user_id, 'user_id');
			$this->display();
		}
	}

	public function delete_action () #act_name = 删除用户#
	{
		$id		= (int)get2('id');
		$user	= $this->loadModel('user');
		$data	= $user->getUserById($id);
		if ($data['groupid'] != 1 && $this->user_id != -1)
		{
			$this->sheader(null, $this->lang->current_user_no_right_delete);
		}
		if ($user->deleteUserById($id)) $this->sheader('?con=admin&ctl=system/user');
		else $this->sheader(null, $this->lang->delete_user_failed);
	}

	public function authorize_action () #act_name = 用户授权#
	{
		$id		= (int)get2('id');
		$user	= $this->loadModel('user');
		$data	= $user->getUserById($id);
		if ($data['groupid'] != 1 && $this->user_id != -1)
		{
			$this->sheader(null, $this->lang->no_permission_to_authorize_this_user);
		}

		if ($data['roleExtendType'] == 1 && $data['role'] > 0) $this->sheader(null, $this->lang->complete_inherited_role_so_can_not_separately_authorize);

		if (is_post())
		{
			//是否需要在保存时去除角色的权限，这样修改继承方式时不会有影响
			$data = array(
				'action'	=> serialize(post('authorize')),
				'info'		=> serialize(post('info')),
				'infoClass'	=> serialize(post('infoClass')),
				'relation'	=> serialize(post('relation'))
			);
			if ($user->updateUserById($data, $id)) $this->sheader('?con=admin&ctl=system/user');
			else $this->sheader(null, $this->lang->authorization_user_failed);
		}
		else
		{
			$data['action']		= unserialize($data['action']);
			$data['info']		= unserialize($data['info']);  //信息分类权限
			$data['infoClass']	= unserialize($data['infoClass']);  //信息分类的排除权限
			$data['relation']	= unserialize($data['relation']);
			$action		= $this->actionPermissionArray();
			$infoClass	= $this->infoClassPermissionArray();
			$infoAction	= $this->infoActionPermissionArray();
			$relation	= $this->relationPermissionArray();

			if (!is_array($data['action'])) $data['action'] = array();
			if (!is_array($data['infoClass'])) $data['infoClass'] = array();
			if (!is_array($data['relation'])) $data['relation'] = array();

			//继承角色权限
			if ($data['roleExtendType'] == 0)  //不继承
			{ }
			else
			{
				$roles	= $this->loadModel('role');
				$role	= $roles->get($data['role']);
				if ($data['roleExtendType'] == 1)  //完全继承
				{
					$data['action']		= unserialize($role['action']);
					$data['info']		= unserialize($role['info']);
					$data['infoClass']	= unserialize($role['infoClass']);
					$data['relation']	= unserialize($role['relation']);
				}
				elseif ($data['roleExtendType'] == 2)  //合并继承
				{
					$data['action']		= array_distinct(array_merge(unserialize($role['action']), $data['action']));
					$data['info']		= array_distinct(array_merge(unserialize($role['info']), $data['info']));
					$data['infoClass']	= array_distinct(array_merge(unserialize($role['infoClass']), $data['infoClass']));
					$data['relation']	= array_distinct(array_merge(unserialize($role['relation']), $data['relation']));
				}
			}

			foreach ($action as $key=>$value)
			{
				foreach ($value['value'][1] as $subkey=>$sub)
				{
					if (in_array($key.'/'.$sub, $data['action'])) $action[$key]['value'][2][$subkey] = true;
				}
			}
			foreach ($infoClass as $key=>$value)
			{
				//分类总权限
				$infoClass[$key]['istrue'] = in_array($value['id'], $data['info']) ? true : false;

				//分类详细权限
				$infoClass[$key]['action'] = $infoAction;
				foreach ($infoAction as $subkey=>$sub)
				{
					foreach ($sub['value'][1] as $kk=>$ss)
					{
						if (in_array($value['id'].'/'.$subkey.'/'.$ss, $data['infoClass'])) $infoClass[$key]['action'][$subkey]['value'][2][$kk] = true;
					}
				}
			}
			foreach ($relation as $key=>$value)
			{
				if ( in_array( $value['id'], $data['relation'] ) ) $relation[$key]['istrue'] = true;
				foreach ($value['child'] as $subkey=>$sub)
				{
					if (in_array($sub['id'], $data['relation'])) $relation[$key]['child'][$subkey]['istrue'] = true;
				}
			}

			$this->setData($data);
			$this->setData($action, 'action');
			$this->setData($infoClass, 'infoClass');
			$this->setData($infoAction, 'infoAction');
			$this->setData($relation, 'relation');
			$this->display();
		}
	}

	public function changepass_action () #act_name = 修改口令#
	{
		$user = & $this->user;
		if (!$user)
		{
			$this->sheader(null, $this->lang->current_login_user_can_not_changepass);
		}

		if (is_post())
		{
			$data = post('data');
			foreach ($data as $key=>$value)
			{
				$data[$key] = trim($value);
			}
			if ($data['newpassword'] == '' || strlen($data['newpassword']) < 6 || $data['newpassword'] != $data['newpassword2'])
			{
				$this->sheader(null, $this->lang->password_length_error_or_not_match);
			}

			if ($this->md5($data['oldpassword']) == $user['password'])
			{
				$bll = $this->loadModel('user');
				if ($bll->updateUserById(array('password' => $this->md5($data['newpassword'])), $user['id'])) $this->sheader('?con=admin&ctl=common/main', $this->lang->password_change_success);
				else $this->sheader(null, $this->lang->change_password_failed);
			}
			else $this->sheader(null, $this->lang->original_password_not_correct);
		}
		else
		{
			$data = $user;
			$this->setData($data);
			$this->display();
		}
	}

	private function _filter ($data)
	{
		foreach ($data as $key=>$value)
		{
			$data[$key] = trim($value);
		}
		if ($data['name'] == '' || $data['password'] == '' || $data['password2'] == '' || strlen($data['password']) < 6 || $data['password'] != $data['password2']) return false;

		$data['password']	= $this->md5($data['password']);
		$data['role']		= (int)$data['role'];

		return $this->array_splice($data, 'password2');
	}

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

		$data['isAdmin']		= (int)$data['isAdmin'];
		$data['isApproved']		= (int)$data['isApproved'];
		$data['groupid']		= (int)$data['groupid'];
		$data['role']			= (int)$data['role'];
		$data['roleExtendType']	= (int)$data['roleExtendType'];

		return $this->array_splice($data, 'password2');
	}

	private function _out ($data)
	{
		foreach ($data as $key=>$value)
		{
			$data[$key]['lastLoginDate']			= date('Y-m-d', $value['lastLoginDate']);
			$data[$key]['createdDate']				= date('Y-m-d', $value['createdDate']);
			$data[$key]['lastModifiedDate']			= date('Y-m-d', $value['lastModifiedDate']);
			$data[$key]['lastPasswordChangedDate']	= date('Y-m-d', $value['lastPasswordChangedDate']);
		}

		return $data;
	}

	private function _columnChk ()
	{
		$hide = array(
			'user_add'				=> $this->chkAction('system/user/add'),
			'user_edit'				=> $this->chkAction('system/user/edit'),
			'user_delete'			=> $this->chkAction('system/user/delete'),
			'user_authorize'		=> $this->chkAction('system/user/authorize')
		);
		return $hide;
	}

}

?>