<?php

/*
 @ctl_name = 运营监控中心@
*/

class ctl_operation_monitor extends adminPage
{

	public function warning1_action () #act_name = 未及时发货订单报警#
	{	
		$mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');
		$mdl_user = $this->loadModel('user');


  if (session('admin_user_id')==-1) {
		$pageSql ="SELECT  distinct o.orderId,o.business_userId,o.order_name,o.createTime,(UNIX_TIMESTAMP()-o.createTime) as operation_delay from cc_order as o
		left join cc_order_operation_log as ol on ol.order_id = o.orderId left join cc_user as u on o.business_userId =u.id 
		WHERE    (UNIX_TIMESTAMP()-o.createTime)>3600*36 and o.coupon_status = 'c01' and o.customer_delivery_option =1 and o.customer_delivery_option=1  and (ol.gen_date IS NULL or ol.type!='process')  order by (UNIX_TIMESTAMP()-o.createTime) ";
  }else{
	  $pageSql ="SELECT  distinct o.orderId,o.business_userId,o.order_name,o.createTime,(UNIX_TIMESTAMP()-o.createTime) as operation_delay from cc_order as o
		left join cc_order_operation_log as ol on ol.order_id = o.orderId left join cc_user as u on o.business_userId =u.id 
		WHERE  u.user_belong_to_agent =".session('admin_user_id')." and (UNIX_TIMESTAMP()-o.createTime)>3600*36 and o.coupon_status = 'c01' and o.customer_delivery_option =1 and o.customer_delivery_option=1  and (ol.gen_date IS NULL or ol.type!='process')  order by (UNIX_TIMESTAMP()-o.createTime) ";

	  
  }
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_wj_customer_coupon->getListBySql($page['outSql']);

		foreach ($data as $key => $value) {
			$data[$key]['business_name']=$mdl_user->getBusinessDisplayName($value['business_userId']);
		}
		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set( 'ctl', 'adv/customer_coupon_process' )->set( 'act', 'detail' ), 'viewUrl');
		$this->setData($this->parseUrl(), 'refreshUrl' );

		$this->setData($this->parseUrl()->set( 'act', 'mark_read' ), 'readUrl');

		$this->setData($sk, 'sk');
		$this->setData($status, 'status');
        $this->setData($payment, 'payment');
		$this->display();

	}


	public function warning2_action () #act_name = 未及时查看处理报警#
	{	
		//var_dump(session('admin_user_id'));exit;
		
		
		$mdl_wj_customer_coupon = $this->loadModel('wj_customer_coupon');


         if (session('admin_user_id')==-1) {
				 $pageSql ="SELECT distinct cc.order_id,cc.business_name,cc.gen_date, (UNIX_TIMESTAMP()-cc.gen_date) as operation_delay FROM cc_wj_customer_coupon as cc 
			left join cc_order_operation_log as ol on cc.order_id = ol.order_id left join cc_order as c on cc.order_id=c.orderId left join cc_user u on cc.business_id =u.id 
			WHERE  (UNIX_TIMESTAMP()-cc.gen_date)>3600*36 and cc.coupon_status = 'c01' and c.customer_delivery_option >=1  and (cc.bonus_type=7 or cc.bonus_type=4 or cc.bonus_type=2) and ol.gen_date IS NULL order by (UNIX_TIMESTAMP()-cc.gen_date) ";
			
			 
		 }else{
			$pageSql ="SELECT distinct cc.order_id,cc.business_name,cc.gen_date, (UNIX_TIMESTAMP()-cc.gen_date) as operation_delay FROM cc_wj_customer_coupon as cc 
			left join cc_order_operation_log as ol on cc.order_id = ol.order_id left join cc_order as c on cc.order_id=c.orderId left join cc_user u on cc.business_id =u.id 
			WHERE u.user_belong_to_agent =".session('admin_user_id')." and (UNIX_TIMESTAMP()-cc.gen_date)>3600*36 and cc.coupon_status = 'c01' and c.customer_delivery_option >=1  and (cc.bonus_type=7 or cc.bonus_type=4 or cc.bonus_type=2) and ol.gen_date IS NULL order by (UNIX_TIMESTAMP()-cc.gen_date) ";
			
		 }
		 //var_dump($pageSql);exit;

		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 20;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $mdl_wj_customer_coupon->getListBySql($page['outSql']);

		$this->setData($data, 'data');
		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set( 'ctl', 'adv/customer_coupon_process' )->set( 'act', 'detail' ), 'viewUrl');
		$this->setData( $this->parseUrl(), 'refreshUrl' );

		$this->setData($this->parseUrl()->set( 'act', 'mark_read' ), 'readUrl');

		$this->setData($sk, 'sk');
		$this->setData($status, 'status');
        $this->setData($payment, 'payment');
		$this->display();

	}

	public function mark_read_action () #act_name = 标记忽略#
	{	

		$mdl_order_operation_log=$this->loadModel('order_operation_log');

		$orderId = trim(get2('id'));
		if($orderId)
			$mdl_order_operation_log->order($orderId)->process()->log();

		$ids = post('ids');
		if($ids){
			foreach ($ids as $id) {
				$mdl_order_operation_log->order($id)->process()->log();
			}
			$this->sheader($this->parseUrl()->set('act',post('actionFrom')));
		}

		
	}

}

?>