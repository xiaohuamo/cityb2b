<?php

class mdl_wj_temp_orderID_carts extends mdl_base
{

	protected $tableName = '#@_wj_temp_orderID_carts';

	
	public function save_temp_data($data,$orderId)
	{
		$temp_arr =serialize($data);
		$temp_arr = base64_encode($temp_arr);

		$temp_carts_data =array(
			'orderId'=>$orderId,
			'temp_arr'=>$temp_arr
			);
		$this->insert($temp_carts_data);

		$this->db->insert($temp_carts_data,'cc_wj_temp_orderID_carts_backup');
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