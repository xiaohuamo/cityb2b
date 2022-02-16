<?php

/*
 @ctl_name = 商家会员管理@
*/

class ctl_company extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$role				= 3;
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
		$where[] = " (t0.role=3 or t0.role=20) ";
		
		$where[] = " (t0.id='$keyword' or t0.name like '%$keyword%' or t0.businessName like '%$keyword%' or t0.displayName like '%$keyword%') ";
		//如果登录 用户组为6 role=6 ,那么是属于商家代理组
		// 这样的管理员只能看到他的商家的积分券信息
		//var_dump($this->user['role']);exit;

		if($this->user['role']==6 || $this->user['role']==11 ){
			$where[] ="user_belong_to_agent=". $_SESSION['admin_user_id'];
		
		}
		if($this->user['role']==8){
			//$where[] ="belong_to_sales_manager=". $_SESSION['admin_user_id'];
				}
		//echo $this->user['role'];exit;
		
		if ($onlyNotApproved > 0) $where['0#isApproved'] = 0;
        
		$pageSql	= $user->getAllUserListSql($where);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 15;
		$maxPage	= 5;

		$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data = $this->_out($user->getAllUserListBySql($page['outSql']));
       
	   
		$this->setData($data);
		
		$this->setData($page['pageStr'], 'pager');
		$this->setData($search, 'search');
		$this->setData($this->user_id, 'user_id');
	
		$this->setData(self::_columnChk(), 'hideColumn');
		$this->setData($this->parseUrl(), 'listUrl');
		$this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
		$this->display();
	}

	public function add_action () #act_name = 创建#
	{
		$user	= $this->loadModel('user');
		if (is_post())
		{
			$data = post('data');
						
			if ($data = $this->_filter($data))
			{
				
				if ( $user->getCount( "email='".$data['email']."'" ) > 0 ) {
					$this->sheader(null, (string)$this->lang->email_exist_1);
				}
				$data['name'] = $data['email'];

				
				//商家介绍人设置及比率
				if ( $data['isBusinessReferalExist'] ) {
					$data['referralId'] = (int)$data['referralId'];
					$data['businessRefPointPercent'] = (int)$data['businessRefPointPercent'];
					if ( $data['businessRefPointPercent'] == 0 ) $data['businessRefPointPercent'] = 10;  //默认值是10
					if ( $data['referralId'] <= 0 || $data['businessRefPointPercent'] < 5 || $data['businessRefPointPercent'] > 30 ) {
						//取值范围是5-30
						$this->sheader(null, "商家介绍人设置及比率填写错误");
					}
					$mdl_referrals = $this->loadModel( 'referrals' );
					$referral = $mdl_referrals->get( $data['referralId'] );
					if ( ! $referral ) {
						$this->sheader(null, "输入的商家介绍人ID不存在");
					}
					$data['memberId'] = $referral['userId'];
				}
				else {
					$data['isBusinessReferalExist'] = 0;
					$data['referralId'] = 0;
					$data['businessRefPointPercent'] = 0;
				}
				//客户介绍人分成比率
				$data['customerRefPointPercent'] = (int)$data['customerRefPointPercent'];
				if ( $data['customerRefPointPercent'] === 0 ) $data['customerRefPointPercent'] = 10;
				if ( $data['customerRefPointPercent'] < 5 || $data['customerRefPointPercent'] > 30 ) {
					//取值范围是5-30
					$this->sheader(null, "客户介绍人分成比率填写错误");
				}
				
				//信用级别
				$data['trustLevel'] = 1;
				if ( $data['trustLevel'] < 0 || $data['trustLevel'] > 4 ) {
					//取值范围是0-4
					$this->sheader(null, "信用级别填写错误");
				}
				
				
                $data['user_belong_to_agent'] =(int)session('admin_user_id');

				$data['role'] = 3;
				$data['groupid'] = 1;
				$data['createdDate'] = time();
				$data['lastLoginIp'] = ip();
				$data['lastLoginDate'] = time();
				$data['loginCount'] = 1;
				$data['isAdmin'] = 0;

				

				
				if ( $user->addUser( $data ) ) {
					
					$this->sheader($this->parseUrl()->set('act')->set('id'));
				}
				else {
					
					$this->file->deletefile( $new_files );
					$this->sheader(null, '添加会员失败');
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
	
	
	public function edit_action () #act_name = 编辑#
	{
		$id		= (int)get2('id');
		$user	= $this->loadModel('user');
		$data	= $user->getUserById($id);
	
		
		$oldData = $data;

		if ($data['role'] != 3 && $data['role'] != 4 && $data['role'] != 20 && $data['role'] != 5 && $data['role'] != 6)
		{
		   
			$this->sheader(null, $this->lang->no_permission_to_edit_this_user);
		}

		$this->setData($this->user['role'], 'user_role');
		if (is_post())
		{
			$data = post('data');
			$all_percent_commission_change = post('all_percent_commission_change');
			$cats = post('categoryId');
			$categoryId = ',';
			$cityId = (int)end( post( 'city' ) );
			if (!$cityId) {
				$cityId =trim(post('backup_city'));
			
			}
			
			
			
				if($all_percent_commission_change==0){
				// var_dump('不改变');exit;
			}else{
			   //跟随变化
			// var_dump($id);exit;
			// 传进来的参数为 商家 ID , 然后判断当前用户是否拥有系统管理元特权，或者是该商家的代理。 如果满足，将该商家所有的产品的commission 修改为指定的数值。
	
		
				
					//var_dump($id);exit;
					$mdl_coupons	= $this->loadModel('coupons');
				
					$mdl_coupons->updateByWhere(array( 'platform_commission_rate' =>$data['platform_commission_rate'] ),array('createUserId' =>$id));

					//var_dump($data['role']);exit;
					
				
			
			
			
			
				
			}
			
			
			
            $gst_type=$data['gst_type'];
            //如果商家的gst设置被更新了, 则根据新的设置更新商家的生鲜产品gst设置
            if($oldData['gst_type']!=$gst_type){
                $mdl_restaurant_menu =$this->loadModel('restaurant_menu');
                $update_restaurant_menu_data =array();

                if($gst_type==1||$gst_type==3){//全部包含gst或者大部分包含gst
                    $update_restaurant_menu_data['include_gst'] = 1;
                } elseif($gst_type==2||$gst_type==4){//全部为无gst或者大部分为无gst
                    $update_restaurant_menu_data['include_gst'] = 0;
                }
                $mdl_restaurant_menu->updateByWhere($update_restaurant_menu_data,array('restaurant_id' =>$id));
            }

			foreach ( $cats as $cat ) {
				if ( !preg_match( '/^106/', $cat ) || strlen($cat)%3!=0 ) continue;
				//$categoryId .= substr($cat, 0, 6).',';
				$categoryId .= $cat.',';
			}
			$data['categoryId'] = $categoryId;
			
			if ($data = $this->_filterForEdit($data))
			{
				
				//信用级别
				$data['trustLevel'] = (int)$data['trustLevel'];
				$data['user_belong_to_agent'] = (int)$data['user_belong_to_agent'];
				if ( $data['trustLevel'] < 0 || $data['trustLevel'] > 4 ) {
					//取值范围是0-4
					$this->sheader(null, "信用级别填写错误");
				}
			
				
				//挂起者
				$data['isSuspended'] = (int)$data['isSuspended'];
				if ( $data['isSuspended'] === 1 && $oldData['isSuspended'] !== $data['isSuspended'] ) {
					$data['suspendUser'] = !$this->user ? 1 : (int)session('admin_user_id');
					$data['suspendDate'] = time();
				}
				
				if ($data['isSuspended'] ) {
					//取值范围是1-5
					//$this->sheader(null, "被挂起");
					$mdl_coupons = $this->loadModel('coupons');
					
					if($mdl_coupons->updateByWhere(array( 'isApproved' => 0 ),array('createUserId' =>$id))){
					
					
					
					}else{
						
						$this->sheader(null, "挂起该用户附属的产品无效");
					}
				}
				
				//上传头像
				$avatarObj = $_FILES['avatar'];
				if ( $avatarObj['size'] > 0 ) {
					$allow_exts = array( 'jpg', 'jpeg', 'gif', 'png' );
					$filepath = date( 'Y-m' );
					$this->file->createdir( 'data/upload/'.$filepath );
					$avatar = $this->file->upfile( $allow_exts, $avatarObj, UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
					if ( $avatar ) {
						$data['avatar'] = $avatar;
					}
				}

				$old_pics = unserialize($oldData['pics']);
				$ops = post('op');
				$opns = post('opn');
				$del_pics = array();
				$saved_pics = array();
				foreach ( $old_pics as $op ) {
					if ( in_array( $op['pic'], $ops ) ) {
						$saved_pics[] = $op;
					}
					else $del_pics[] = UPDATE_DIR.$op['pic'];
				}
				$pics_count = count( $saved_pics );
				$upload_error = 0;
				$pics = array();
				$new_files = array();
				$files = $_FILES['pic'];
				$image_exts = array( 'jpg', 'jpeg', 'gif', 'png' );
				$filepath = date( 'Y-m' );
				$this->file->createdir( 'data/upload/'.$filepath );
				foreach ( $files['name'] as $key => $name ) {
					if ( $pics_count >= 10 ) {
						break;
					}
					if ( $files['size'][$key] > 0 ) {
						$ext = strtolower( end( explode( '.', $name ) ) );
						$filename = $this->file->upfile( $image_exts, array( 'tmp_name' => $files['tmp_name'][$key], 'size' => $files['size'][$key], 'name' => $name ), UPDATE_DIR, $filepath.'/'.date( 'YmdHis' ).$this->createRnd() );
						if ( $filename ) {
							$pics_count++;
							$pics[] = array( 'pic' => $filename, 'name' => $name );
							$new_files[] = UPDATE_DIR.$filename;
						}
						else {
							$upload_error++;
						}
					}
				}
				$data['pics'] = serialize(array_merge($saved_pics, $pics));

				if ( $user->updateUserById( $data, $id ) ) {
					if ( $data['avatar'] && $oldData['avatar'] ) {
						$this->file->deletefile( UPDATE_DIR.$oldData['avatar'] );
					}
					$this->file->deletefile( $del_pics );
					$this->sheader($this->parseUrl()->set('act')->set('id'));
					
					// $data['avatar']
				}
				else {
					if ( $data['avatar'] ) {
						$this->file->deletefile( UPDATE_DIR.$data['avatar'] );
					}
					$this->file->deletefile( $new_files );
					$this->sheader(null, $this->lang->edit_user_failed);
				}
			}
			else $this->sheader(null, $this->lang->your_submit_incomplete);
			
		
			
			
		}
		else
		{
			$data['pics'] = unserialize($data['pics']);
			$mdl_user = $this->loadModel( 'user' );
			if ( $data['adultUser'] ) $data['adultUser'] = $mdl_user->get( $data['adultUser'] );
			if ( $data['suspendUser'] ) $data['suspendUser'] = $mdl_user->get( $data['suspendUser'] );
			//挂起者
			if ( $data['isSuspended'] === 1 && $oldData['isSuspended'] !== $data['isSuspended'] ) {
				$data['suspendUser'] = !$this->user ? 1 : (int)session('admin_user_id');
				$data['suspendDate'] = time();
			}
			$mdl_city = $this->loadModel( 'city' );
			$citylist = $mdl_city->getChild();
			$this->setData( $citylist, 'citylist' );
			$mdl_infoclass = $this->loadModel('infoClass');
			$categories_all = $mdl_infoclass->getChild4( '106' );
			$this->setData( $categories_all, 'categories_all' );
			$this->setData($data);
			$this->setData(self::_columnChk(), 'hideColumn');
			$this->setData($this->user_id, 'user_id');
			$this->setData($this->parseUrl()->set('act')->set('id'), 'listUrl');
			$this->setData($_SESSION['admin_user_id'],'admin_user_id');
			$this->display();
		}
	}

	/*public function delete_action () #act_name = 删除#
	{
		$id		= (int)get2('id');
		$user	= $this->loadModel('user');
		$data	= $user->getUserById($id);
		if ($data['role'] != 3)
		{
			$this->sheader(null, $this->lang->current_user_no_right_delete);
		}
		if ($user->deleteUserById($id)) {
			
			$this->sheader($this->parseUrl()->set('act')->set('id'));
		}
		else $this->sheader(null, $this->lang->delete_user_failed);
	}*/

	private function _filter ($data)
	{
		foreach ($data as $key=>$value)
		{
			$data[$key] = trim($value);
		}
		if ($data['password'] == '' || $data['password2'] == '' || strlen($data['password']) < 6 || $data['password'] != $data['password2']) return false;

		$reg = $this->loadModel('reg');
		if (!$reg->chkMail($data['email'])) return false;

		$data['password']	= $this->md5($data['password']);
		$data['isApproved']	= (int)$data['isApproved'];
		$data['groupid']	= 1;
		$data['roleExtendType']	= 1;
		$data['createdDate']	= time();

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

		$data['isAdmin']		= 0;
		$data['isApproved']		= (int)$data['isApproved'];
		$data['groupid']		= 1;
		//$data['role']			= 3;
		$data['roleExtendType']	= 1;

		return $this->array_splice($data, 'password2');
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