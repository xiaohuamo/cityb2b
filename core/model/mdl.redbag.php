<?php

class mdl_redbag extends mdl_base
{

	protected $tableName = '#@_redbag';

    function get_redbagId_byBusinessUserId($userId){
		
		$where =array(
		  'createUserId'=>$userId,
		  'is_approved'=>1,
		  'status'=>1
		);
	     $record = $this->getList(null, $where , " id desc ");
		 if($record){
			  return $record[0]['id'];
		 }else{
			 return 0;
		 }
		 
	}

	function redbag_callback($data_redbag,$id,$redbag_list){
		// 收回红包 一个是 将该红包置为收回状态 ，第二个计算当前的未发放红包的总金额数，然后开始清还给发红包的商家相应的金额红包金额。
		
		$data = array(
			'status'=>3
		);
		
		$mdl_recharge =loadModel('recharge');
		
		$mdl_recharge->begin();
		
		if($this->update($data,$id)) {
			// 开始计算剩余红包金额，及返回操作
	
			$total =0.00;
			foreach ( $redbag_list as $key => $val ) {
			  	$total += $redbag_list[$key]['amount'];
			}
			    
		 	$data_recharge = array(
				'orderId' => date( 'YmdHis' ).$this->createRnd(),
				'userId' => $data_redbag['createUserID'],
				'money' => $total,
				'payment' => 'redbag',
				'status' => 1,
				'createTime' => time(),
				'createIp' => ip(),
				'coupon_name'=>'红包退款',
				'coupon_id'=>$id,
				'main_coupon_id'=>$id
			);
			
			$rechargeid = $mdl_recharge->insert( $data_recharge );
			
			 if($rechargeid){
			
				 $mdl_recharge->commit();
				 return 1;
		 	 }else{
				 
				 $mdl_recharge->rollback();
			 }
			
		
		}else{
		  
			$mdl_recharge->rollback();
		}
		
		
		return 0;
		
	}

	private function createRnd ($length = 6)
	{
		$rnd = '';
		while (strlen($rnd) < $length)
		{
			$rnd .= rand();
		}
		if (strlen($rnd) > $length) $rnd = left($rnd, $length);
		return $rnd;
	}

	public function checkExpired()
	{	
		// 当用户进入该页面之前，需要检查一下他发放的红包是否有到期的，如果由则自动回收。
		// 注意可能由多个红包没有收回
		
		//首先获得当前用户发的红包状态为1的所有记录，然后检查时间是否已经大于24小时。如果大于24小时的红包，主表设为过期，然后进行退款操作。
		
		$mdl_redbag_details = loadModel( 'redbag_details' );
		$sql ="select * from cc_redbag where status =1 and createUserID=".$this->loginUser['id'];
		$redbag = $this->getListBySql($sql);
		
		foreach ($redbag as $key => $value) {
			$id = $redbag[$key]['id'];
			$redbagMain=$this->get($id);
			 $sql ="select * from cc_redbag_details  where redbag_id =".$id." and userId=0";
		     $redbag_list = $mdl_redbag_details->getListBySql($sql);
			$differ_time = (time()-$redbagMain['createtime'])/3600;
		  if($differ_time>=24) {
		      $this->redbag_callback($redbagMain,$id,$redbag_list);
		   }
		}
	}
	
}

?>