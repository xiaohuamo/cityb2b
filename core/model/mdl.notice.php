<?php

class mdl_notice extends mdl_base
{

	protected $tableName = '#@_notice';
	public function get_notice_list($factory_id){
		$sql ="select n.* , from_unixtime(n.invoice_start_date,'%Y-%m-%d') as invoice_start_date_str,
        from_unixtime(n.invoice_end_date,'%Y-%m-%d') as invoice_end_date_str,if(n.is_approved>0,'Yes','Not') as approveStatus ,if(n.is_send_to_invoice>0,'Yes','Not') as isoninvoice  from cc_notice n where factory_id =$factory_id order by id desc limit 50";
		$list =$this->getListBySql($sql);
		foreach ($list as $key=>$value){
			$currentTime =time();
			$expire_time = $value['invoice_end_date'] +24*60*60;
			if($currentTime>$expire_time) {
				$list[$key]['running'] =0;
				$list[$key]['invocie_sending_closed_str'] ='Expired-Closed';
			}else{
				if($value['invocie_sending_closed']==0 && $value['is_approved']>0 ) {
					$list[$key]['invocie_sending_closed_str'] ='Running';
					$list[$key]['running'] =1;
				}else{
					$list[$key]['invocie_sending_closed_str'] ='Not Running';
					$list[$key]['running'] =0;
				}

			}
		}
		return$list;
	}

	public function getLastNotice($factory_id,$type){
		$current_time =time();
		$sql ="select title from cc_notice where factory_id =$factory_id and upper(notice_type) =upper('$type') and is_approved =1 and (invoice_start_date < $current_time and (invoice_end_date + 24*60*60)>$current_time) and invocie_sending_closed=0 order by id desc ";
		$notice =$this->getListBySql($sql);
	//	var_dump($sql);exit;
		if($notice){
			return $notice[0]['title'];
		}else{
			return 0;
		}

	}
}

?>

