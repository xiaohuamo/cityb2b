<?php	
/**
 * 手持机检票设备的 数据下载
 */
class ctl_ticketvalidation extends cmsPage
{
	
	/**
	 * [download_action seat info]
	 * 
	 * @return [type] [description]
	 */
	public function download_action()
	{	
		
		$name = post('username');
		$pass = post('password');
		$type = post('type');


		$field=array('id','order_id','related_id','customer_name','guige_des');
		$where['bonus_id']=1758;
		$where[]=' coupon_status != "d01"';

		$data = $this->loadModel('wj_customer_coupon')->getList($field,$where);

		$outputData = array();
		foreach ($data as $item) {
			$row['id']=$item['id'];
			$row['code']= $item['order_id'].$itme['related_id'];
			$row['name']= $item['customer_name'];
			$row['seat']= $item['guige_des'];
			array_push($outputData, $row);
		}
		echo json_encode($outputData) ;

	}

	/**
	 * [update single seat status]
	 * @return [type] [description]
	 */
	public function update_action()
	{
		$name = post('username');
		$pass = post('password');
		$type = post('id');
		$status=post('status');	
	}


	/**
	 * [batch_update_action description]
	 * @return [type] [description]
	 */
	public function batch_update_action()
	{
		# code...
	}

	
}




?>