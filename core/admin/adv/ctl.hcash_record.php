<?php

/*
 @ctl_name = Hcash 支付审批@
*/

class ctl_hcash_record extends adminPage
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
		$where[] = " (order_id like '%$keyword%' or hcash_order_id like '%$keyword%'  or hcash_order_tag like '%$keyword%' )";
		
		if ($onlyNotApproved > 0) $where[] = "status<>1";

		$hcash_record	= $this->loadModel('hcash_record');
		$order		= " id desc";
		$pageSql	= $hcash_record->getListSql(null, $where, $order);
		$pageUrl	= $this->parseUrl()->set( 'page' );
		$pageSize	= 50;
		$maxPage	= 10;
		$page		= $this->page($pageSql, $pageUrl, $pageSize, $maxPage);
		$data		= $hcash_record->getListBySql($page['outSql']);
		$this->setData($data, 'data');

		$this->setData($page['pageStr'], 'pager');
		$this->setData($this->parseUrl()->set('type')->set('keyword')->set('onlyNotApproved'), 'searchUrl');
		$this->setData($this->parseUrl()->set( 'act', 'update' ), 'updateUrl');
		$this->setData($this->parseUrl()->set( 'ctl', 'adv/customer_coupon_process' )->set( 'act', 'detail' ), 'viewUrl');
		$this->setData( $search, 'search' );
		$this->display();
	}

	public function update_action () #act_name = 审批#
	{
		$ids=post( 'ids' );

		$id = get2( 'id' );

		$status=get2( 'status' );

		if(!$ids&&$id){
			//同时更新相应的订单支付状态
			$hcash_record=$this->loadModel('hcash_record')->get($id);
	        $this->loadModel('order')->updateByWhere(
	        	array('status'=>$status,'paytime'=>time()),
	        	array('orderId'=>$hcash_record['order_id'])
	        	);


			$data['status']=$status;
			if($this->loadModel('hcash_record')->update($data,$id))
				$this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/hcash_record&act=index');
			else
				$this->sheader(null,'数据库操作时错误，请联系管理员');
		}else{	
			$id_str = join(',',$ids);
			$where=" id in ($id_str) ";
			

			//同时更新相应的订单支付状态
			$hcash_records=$this->loadModel('hcash_record')->getList('order_id',$where);
			$order_id_array=array();
			foreach ($hcash_records as $value) {
				array_push($order_id_array, $value['order_id']);
			}
			$order_id_str=join(',',$order_id_array);

			$this->loadModel('order')->updateByWhere(
	        	array('status'=>$status,'paytime'=>time()),
	        	array(" orderId in ($order_id_str) ")
	        	);

	        $data['status']=$status;

			if($this->loadModel('hcash_record')->updateByWhere($data,$where))
				$this->sheader(HTTP_ROOT_WWW.'index.php?con=admin&ctl=adv/hcash_record&act=index');
			else
				$this->sheader(null,'数据库操作时错误，请联系管理员');
		}
		
	}

	public function note_update_ajax_action () #act_name = Note#
	{	
		$where['id']=get2('id');
		$data['note']=get2('note');

		if($this->loadModel('hcash_record')->updateByWhere($data,$where))
			echo 'success';
		else
			echo 'fail';
	}


}

?>