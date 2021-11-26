<?php

/*
 @ctl_name = 兑换记录管理@
*/

class ctl_exchange extends adminPage
{

	public function index_action () #act_name = 列表#
	{
		$mdl_exchange	= $this->loadModel('exchange');
		$where		= "";
		$order		= "createTime desc";
		$pageSql	= $mdl_exchange->getListSql(null, $where, $order);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_exchange->getListBySql($page['outSql']);

		$mdl_user = $this->loadModel( 'user' );
		$mdl_products = $this->loadModel( 'products' );
		$mdl_coupons = $this->loadModel( 'coupons' );
		foreach ( $data as $key => $val ) {
			$data[$key]['createUser'] = $mdl_user->get( $val['userId'] );
			if ( $val['type'] == 'product' ) {
				$product = $mdl_products->get( $val['productId'] );
				$data[$key]['product'] = array( 'url' => HTTP_ROOT_WWW.'product/'.$product['id'], 'name' => $product['title'] );
			}
			else if ( $val['type'] == 'coupon' ) {
				$coupon = $mdl_coupons->get( $val['productId'] );
				$data[$key]['product'] = array( 'url' => HTTP_ROOT_WWW.'coupon/'.$coupon['id'], 'name' => $coupon['title'] );
			}
		}

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set( 'act', 'delete' ), 'delUrl');
		$this->setData($this->parseUrl()->set( 'act', 'view' ), 'viewUrl');
		$this->setData($this->parseUrl()->set(), 'refreshUrl');
		$this->display();
	}

	public function view_action () #act_name = 详细#
	{
		$id = (int)get2( 'id' );
		$mdl_exchange	= $this->loadModel('exchange');
		$data = $mdl_exchange->get( $id );
		if ( ! $data ) {
			$this->sheader( null, '兑换记录不存在' );
		}

		switch ( $data['type'] ) {
			case 'product':
				$mdl_products = $this->loadModel( 'products' );
				$product = $mdl_products->get( $data['productId'] );
				$data['product'] = array( 'url' => HTTP_ROOT_WWW.'product/'.$product['id'], 'name' => $product['title'] );
				break;
			case 'coupon':
				$mdl_coupons = $this->loadModel( 'coupons' );
				$coupon = $mdl_coupons->get( $data['productId'] );
				$data['product'] = array( 'url' => HTTP_ROOT_WWW.'coupon/'.$coupon['id'], 'name' => $coupon['title'] );
				break;
		}

		$this->setData($data, 'data');
		$this->setData($this->parseUrl()->set( 'act' )->set( 'id' ), 'listUrl');
		$this->display();
	}

	/*
	public function delete_action () #act_name = 删除#
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
		$this->sheader($this->parseUrl()->set('act')->set('id'));
	}

	private function _delete ($id)
	{
		$id			= (int)$id;
		$mdl_exchange	= $this->loadModel('exchange');
		$exchange		= $mdl_exchange->get($id);
		if (!$exchange)
		{
			$this->sheader(null, $this->lang->current_record_not_exists."<br />id:$id");
		}
		if ($mdl_exchange->delete($id))
		{
			
		}
		else
		{
			$this->sheader(null, $this->lang->delete_failed."<br />id:$id");
		}
	}*/

}

?>