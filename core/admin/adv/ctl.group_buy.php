<?php

/*
 @ctl_name = 团购管理@
*/

class ctl_group_buy extends adminPage
{

	public function index_action () #act_name = index#
	{

		$mdl_group_buy	= $this->loadModel('group_buy');

		$mdl_user = $this->loadModel('user');

		$sql = "SELECT g.*,c.pic FROM `cc_group_buy_status` as g left join cc_coupons as c on g.coupon_id=c.id";

		$sql .= " where 1 "; //[ g.parentId=0 ]child 自开团不显示 //已完成不显示//关闭的不显示

		$sql .= " ORDER BY
				   CASE g.status
				      WHEN '1' THEN 1
				      WHEN '3' THEN 2
				      WHEN '4' THEN 3
				      WHEN '5' THEN 4
				      WHEN '2' THEN 5
				      WHEN '0' THEN 6
				   END, id desc";

		$pageSql	= $sql;
		$pageUrl	= $this->parseUrl()->set('page');
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_group_buy->getListBySql($page['outSql']);

		foreach ($data as $key => $value) {
			$data[$key]['size'] = end(array_keys(unserialize($value['condition_level'])));
			$data[$key]['create_user_name'] = $mdl_user->getDisplayName($value['create_user_id']);
			$data[$key]['current_group_size'] = $mdl_group_buy->currentGroupSize($value['id']);
		}


		$this->setData($data, 'data');
		$this->setData( $page['pageStr'], 'pager' );
		$this->setData( $this->parseUrl(), 'refreshUrl' );
		$this->setData( $this->parseUrl()->set( 'act', 'delete' ), 'delUrl' );
		$this->setData( $this->parseUrl()->set( 'act' ), 'doUrl' );
		$this->setData( $search, 'search' );
		$this->display();
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
		$this->sheader($this->parseUrl()->set( 'act' )->set( 'index' ));
	}


	private function _delete ($id)
	{	

		$id				= (int)$id;
		$mdl_group_buy	= $this->loadModel('group_buy');
		$group			= $mdl_group_buy->getGroupRawData($id);
		if (!$group)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_group_buy->groupDelete($id))
		{
		}
		else
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}

}

?>