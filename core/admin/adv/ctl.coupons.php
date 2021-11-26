<?php

/*
 @ctl_name = coupons管理@
*/

class ctl_coupons extends adminPage
{

	public function index_action () #act_name = index#
	{
		$keyword			= get2('keyword');
		$onlyNotApproved	= limitInt(get2('onlyNotApproved'), 0, 1);
		$search = array(
			'keyword'			=> $keyword,
			'onlyNotApproved'	=> $onlyNotApproved
		);
		$where	= array();
		$where[] = " (title like '%$keyword%'  or createUserId like '%$keyword%' or id like '%$keyword%' or businessName like '%$keyword%') ";
		

		//如果登录 用户组为6 role=6 ,那么是属于商家代理组
		// 这样的管理员只能看到他的商家的积分券信息
		
		if($this->user['role']==6 || $this->user['role']==11){
		//	$where[] ="user_belong_to_agent=". $_SESSION['admin_user_id'];
			$where[] =" createUserId in (select id from #@_user where user_belong_to_agent =" . $_SESSION['admin_user_id'].")";
		}
		
		
		if ($onlyNotApproved > 0) $where[] = "isApproved<>1";

		$mdl_coupons	= $this->loadModel('coupons');
		$pageSql	= $mdl_coupons->getListSql( null, $where, 'createTime desc' );
		//echo $pageSql;exit;
		$pageUrl	= $this->parseUrl()->set('page');
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_coupons->getListBySql($page['outSql']);

		$mdl_user = $this->loadModel( 'user' );
		foreach ( $data as $key => $val ) {
			$data[$key]['createUser'] = $mdl_user->get( $val['createUserId'] );
		}

		$this->setData($data, 'data');
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $this->parseUrl(), 'refreshUrl' );
		$this->setData( $this->parseUrl()->set( 'act', 'delete' ), 'delUrl' );
		$this->setData( $this->parseUrl()->set( 'act', 'edit' ), 'editUrl' );
		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
		$this->setData( $search, 'search' );
		$this->display();
	}

	public function edit_action () #act_name = 编辑#
	{
		$id		= (int)get2('id');
		$mdl_coupons	= $this->loadModel('coupons');
		$coupon	= $mdl_coupons->get($id);
		if ( ! $coupon ) $this->sheader( null, '产品不存在' );

		if (is_post())
		{
			$data = post('data');
			$data['isApproved'] = (int)$data['isApproved'];

			if ( empty( $data['title'] ) ) {
				$this->sheader( null, '请将表单填写完整后再提交' );
			}

			if ( $mdl_coupons->update( $data, $coupon['id'] ) ) {
				$this->sheader($this->parseUrl()->set('act')->set('id'));
			}
			else {
				$this->sheader(null, '保存失败');
			}
		}
		else
		{

			$mdl_coupon_type = $this->loadModel('coupon_type');
			$types = $mdl_coupon_type->getList(null, array( 'isApproved' => 1 ), 'sortnum asc');
			$this->setData( $types, 'types' );
			
			$this->setData($coupon);
			$this->setData($this->parseUrl()->set('act')->set('id'), 'listUrl');
			$this->display();
		}
	}

	public function type_action () #act_name = 类型#
	{
		$id = (int)get2( 'id' );
		$cid = trim( get2( 'cid' ) );
		if ( ! preg_match( '/^106/i', $cid ) ) {
			echo '请选择行业子分类';exit;
		}
		$mdl_coupons = $this->loadModel( 'coupons' );
		$mdl_infoclass = $this->loadModel( 'infoClass' );
		$mdl_coupon_type = $this->loadModel('coupon_type');
		$cat = $mdl_infoclass->get( $cid );
		$coupon = $mdl_coupons->get( $id );
		$result = '';
		foreach ( explode( ',', $cat['coupontype'] ) as $ctid ) {
			$ctid = (int)$ctid;
			if ( $ctid > 0 ) {
				$ct = $mdl_coupon_type->get( $ctid );
				$result .= '<input type="radio" name="data[bonusType]" value="'.$ct['id'].'" id="bonusType'.$ct['id'].'"'.( $ct['id'] == $coupon['bonusType'] ? ' checked' : '' ).' /><label for="bonusType'.$ct['id'].'">'.$ct['name'].'</label>';
			}
		}
		echo $result;exit;
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
		$this->sheader('?con=admin&ctl=adv/coupons');
	}

	private function _audit ($id)
	{
		$id				= (int)$id;
		$mdl_coupons	= $this->loadModel('coupons');
		$link			= $mdl_coupons->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_coupons->update(array( 'isApproved' => 1), $id))
		{
		}
		else
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}

	private function _unaudit ($id)
	{
		$id				= (int)$id;
		$mdl_coupons	= $this->loadModel('coupons');
		$link			= $mdl_coupons->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_coupons->update(array( 'isApproved' => 0 ), $id))
		{
		}
		else
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}

	private function _delete ($id)
	{
		$id				= (int)$id;
		$mdl_coupons	= $this->loadModel('coupons');
		$link			= $mdl_coupons->get($id);
		if (!$link)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ( $link['isApproved'] ) {
			$this->sheader(null, "已审核的产品不能删除<br />id:$id");
		}
		if ($mdl_coupons->delete($id))
		{
		}
		else
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}

}

?>