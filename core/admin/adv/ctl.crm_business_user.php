<?php

/*
 @ctl_name = 潜在商家管理@
*/

class ctl_crm_business_user extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$type				= get2('type');
		$keyword			= get2('keyword');
		$status_id			= get2('status_id');
		$category_id			= get2('category_id');
		$cityId			= get2('cityId');
		
		
		$search = array(
			'type'				=> $type,
			'keyword'			=> $keyword,
			'status_id'         =>$status_id,
			'category_id'         =>$category_id,
			'cityId'         =>$cityId
		);

		$user	= $this->loadModel('crm_business_user');
		  
		//$where	= array();
		$where	= array();
		switch ($type)
		{
			case 'business_name'	: $where[] = "business_name like '%$keyword%'"; break;
			case 'email'		: $where[] = "email like '%$keyword%'"; break;
		}
		if($status_id) {
			$where[]="status_id ='$status_id'";
			
		}
		if($category_id) {
			$where[]="category ='$category_id'";
			
		}
		if($cityId) {
			$where[]="(city  like '%,$cityId,%'  or city is null )";
			
		}
		
		if($this->user['role']==6){
			$where[] ="createUserId=". $_SESSION['admin_user_id'];
		//	$where[] ="user_belong_to_agent=". $_SESSION['admin_user_id'];
		//	$where[] =" createUserId in (select id from #@_user where user_belong_to_agent =" . $_SESSION['admin_user_id'].")";
		}
		
		
		$status_list=$this->loadModel('crm_business_user_status')->getList();
		$this->setData($status_list,'status_list');
		
			
		$cat_list=$this->loadModel('crm_business_user_cat')->getList();
		$this->setData($cat_list,'cat_list');
		
		
		$mdl_city = $this->loadModel('city');
		    $where1 =array(
			 'parentId' =>6
			);
			$citylist = $mdl_city->getList( null, $where1, 'sortnum asc' );
			
			foreach ( $citylist as $key => $val ) {
			$citylist[$key]['city_div'] =  ','.$val['id'];
		}
		
		$this->setData( $citylist, 'citylist' );

		$pageSql	= $user->getListSql( null, $where, 'createtime desc' );
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 15;
		$maxPage	= 100;

		$page = $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data = $this->_out($user->getlistBySql($page['outSql']));
		
	    foreach ( $data as $key => $val ) {
			$city =$val['city'];
			foreach ( explode( ',', $city ) as $ctid ) {
			$ctid = (int)$ctid;
			if ( $ctid > 0 ) {
				$ct = $mdl_city->get( $ctid );
				$cityname .=$ct['name'].'&nbsp;' ;
			}
		}	
			$data[$key]['cityname']=$cityname;
			$cityname='';
		}

		$this->setData($data);
		$this->setData($page['pageStr'], 'pager');
		$this->setData($search, 'search');
		$this->setData($this->user_id, 'user_id');
		$this->setData(self::_columnChk(), 'hideColumn');
		$this->setData( $this->parseUrl()->set( 'act', 'edit' ), 'editUrl' );
		$this->setData($this->parseUrl(), 'listUrl');
		$this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
		$this->display();
	}

	
	public function add_action () #act_name = add#
	{
		if ( is_post() ) {
			$business_name = trim( post( 'business_name' ) );
			
			$category = trim( post( 'category' ) );
			$telephone = trim( post( 'telephone' ) );
			$weixin = trim( post( 'weixin' ) );
			$email = trim( post( 'email' ) );
			$website = trim( post( 'website' ) );
			$resource = trim( post( 'resource' ) );
			$note = trim( post( 'note' ) );
			$priority = trim( post( 'priority' ) );
		
			$city = post( 'city' );
			if ( $city ) {
				$city = ','.implode( ',', $city ).',';
			}
			else $city = '';
			

			if ( empty( $business_name ) ) {
				$this->sheader( null, '请填写用户名再提交' );
			}
			$mdl_user = $this->loadModel( 'crm_business_user' );
			
			$user = $mdl_user->getbyWhere( array('business_name'=>$business_name));
			if (  $user  ) {
				$this->sheader( null, '商家已经存在' );
			}

			$user = $mdl_user->getbyWhere( array('telephone'=>$telephone));
			if ( $telephone && $user  ) {
				$this->sheader( null, '电话已经存在' );
			}

			
			if ( ! $mdl_user->insert( 
				array(
				'createUserId' => !$this->user ? 1 : (int)session('admin_user_id'), 
				'createtime' => time(),
				'category' => $category, 
				'resource' => $resource, 
				'business_name' =>$business_name, 
				'website' => $website,
				'telephone' => $telephone,
				'weixin' => $weixin,
				'email' => $email,
				'note'=>$note,
				'priority'=>$priority,
				'city'=>$city
				) 
			) 
			)
			$this->sheader( null, '添加潜在商家失败' );
			$this->sheader( $this->parseUrl()->set('act') );
		}
		else
		{
			
			$mdl_city = $this->loadModel('city');
			$citylist = $mdl_city->getList( null, $where, 'sortnum asc' );
			$this->setData( $citylist, 'citylist' );
			
			$cat_list=$this->loadModel('crm_business_user_cat')->getList();
		    $this->setData($cat_list,'cat_list');
			$this->display();
		}
	}
	
	public function edit_action () #act_name = 编辑#
	{
		$id		= (int)get2('id');
		$user	= $this->loadModel('crm_business_user');
		$data	= $user->get($id);
		$oldData = $data;
		
        $mdl_user=$this->loadModel("user");
		
		$sql = " select a.id as createUserId,a.name from  cc_user a where role=6";
		$user_list = $mdl_user->getlistBySql($sql);
		//var_dump($user_list);
		$this->setData($user_list,"user_list");
		
		if (is_post())
		{
			$data = post('data');
			
			$city = post( 'city' );
			if ( $city ) {
				$city = ','.implode( ',', $city ).',';
			}
			else $city = '';
			
			if ( empty( $data['business_name'] ) ) {
				$this->sheader(null, "商家名称不能为空");
			}
			if ($data['email']!=null && $user->getCount( "email='".$data['email']."' and id<>$id" ) > 0 ) {
				$this->sheader(null, "邮箱已被使用，请更换其他邮箱后提交修改");
			}

			if ($data['telephone']!=null && $user->getCount( "telephone='".$data['telephone']."' and id<>$id" ) > 0 ) {
				$this->sheader(null, "电话已被使用，请更换其他电话后提交修改");
			}


			$data1=array(
				'category'=>$data['category'],
				'resource'=>$data['resource'],
				'business_name'=>$data['business_name'],
				'createUserId'=>$data['createUserId'],
				'website'=>$data['website'],
				'telephone'=>$data['telephone'],
				'weixin'=>$data['weixin'], 
				'email'=>$data['email'],	   
				'note'=>$data['note'],
				'priority'=>$data['priority'],	  
				'status_id'=>$data['status_id']					 
			);
			$data1['city'] = $city;

		
			if ( $user->update( $data1, $id ) ) {
				
				$this->sheader($this->parseUrl()->set('act')->set('id'));
			}
			else {
				
				$this->sheader(null, $this->lang->edit_user_failed);
			}
			
		}
		else
		{
			$status_list=$this->loadModel('crm_business_user_status')->getList();
		   $this->setData($status_list,'status_list');
			$mdl_city = $this->loadModel('city');
			
			$citylist = $mdl_city->getList( null, $where, 'sortnum asc' );
			$this->setData( $citylist, 'citylist' );
			
            $cat_list=$this->loadModel('crm_business_user_cat')->getList();
		    $this->setData($cat_list,'cat_list');
			$this->setData($data);
			$this->setData(self::_columnChk(), 'hideColumn');
			$this->setData( $this->parseUrl()->set( 'act', 'edit' ), 'editUrl' );
			$this->setData($this->user_id, 'user_id');
			$this->setData($this->parseUrl()->set('act')->set('id'), 'listUrl');
			$this->display();
		}
	}

	public function check_mobile_exist_action()
	{	
		$mdl_user=$this->loadModel('crm_business_user');
		$telephone=get2('telephone');

		$user = $mdl_user->getbyWhere( array('telephone'=>$telephone));

		if ( $telephone && $user  ) {
			echo "电话已经存在";
		}else{
			echo "电话可以使用";
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
			'user_add'				=> $this->chkAction('adv/crm_business_user/add'),
			'user_edit'				=> $this->chkAction('adv/crm_business_user/edit'),
			'user_delete'			=> $this->chkAction('adv/crm_business_user/delete')
		);
		return $hide;
	}

}

?>