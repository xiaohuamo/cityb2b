<?php

class mdl_wj_temp_orderID_carts_for_yunying extends mdl_base
{

	protected $tableName = '#@_wj_temp_orderid_carts_for_yunying';

	
	public function save_temp_data($data,$orderId)
	{
		$phone=$data['phone'];
		$userId=$data['userId'];
		$name = $data['name'];
		
		$temp_arr =serialize($data);
		$temp_arr = base64_encode($temp_arr);

		$temp_carts_data =array(
			'orderId'=>$orderId,
			'temp_arr'=>$temp_arr,
			'phone'=>$phone,
			'userId'=>$userId,
			'name'=>$name
			);
    if($orderId =='20200822142442314239')	{
		var_dump($temp_carts_data);
		//exit;
		}
		
		
		$this->insert($temp_carts_data);

		//$this->db->insert($temp_carts_data,'cc_wj_temp_orderID_carts_backup');
	}
	
	public function update_temp_data($data,$err_num_cn,$err_num_en)
	{
		
		$userId=$data['userId'];
		
		$orderId=$data['orderId'];
		
		$arr_post =$data['arr_post'];
		
		$temp_arr =serialize($arr_post);
		$temp_arr = base64_encode($temp_arr);
		
        $where =array(
		 'orderId'=>$orderId,
		 'userId'=>$userId
		);
		
		$temp_carts_data =array(
			'err_num_cn'=>$err_num_cn,
			'err_num_en'=>$err_num_en,
			);
			
		if($temp_arr) {
			
			$temp_carts_data['temp_arr']=$temp_arr;
		}
		
		if($data['enter_paying_process']){
			
			$temp_carts_data['enter_paying_process']=1;
		}
		
		//var_dump($where);exit;
		
		$this->updateByWhere($temp_carts_data,$where);

		//$this->db->insert($temp_carts_data,'cc_wj_temp_orderID_carts_backup');
	}

	public function get_temp_data($orderId)
	{
		$where['orderId']=$orderId;
		$data = $this->getByWhere($where);
		return unserialize(base64_decode($data['temp_arr']));
	}

	public function delete_temp_data($orderId)
	{	
		$where['orderId']=$orderId;
		return $this->deleteByWhere($where);
	}

	public function getPendingQty($couponId,$type='m')
	{	

		$totalQty = 0;

		$data =  $this->getList();

		if($type=='m'){
			foreach ($data as $d) {
			$arr_post= unserialize(base64_decode($d['temp_arr']));
			//var_dump($arr);
				 foreach ( $arr_post['ids'] as $key => $val ) {

				 	$customer_buy_quantities= $arr_post['quantities'][$key];

				 	$id = $arr_post['ids'][$key];
				 	$sub_id= $arr_post['sub_ids'][$key];
				 	$sub_or_main=$arr_post['sub_or_main'][$key];

				 	if($couponId==$id&&$sub_or_main=='m')$totalQty+=$customer_buy_quantities;
				 	
				 }
			}
		}elseif($type=='s'){
			foreach ($data as $d) {
			$arr_post= unserialize(base64_decode($d['temp_arr']));
			//var_dump($arr);
				 foreach ( $arr_post['ids'] as $key => $val ) {

				 	$customer_buy_quantities= $arr_post['quantities'][$key];

				 	$id = $arr_post['ids'][$key];
				 	$sub_id= $arr_post['sub_ids'][$key];
				 	$sub_or_main=$arr_post['sub_or_main'][$key];

				 	if($couponId==$sub_id&&$sub_or_main=='s')$totalQty+=$customer_buy_quantities;
				 	
				 }
			}
		}else{
			
		}

		return $totalQty;
		
	}
}

?>