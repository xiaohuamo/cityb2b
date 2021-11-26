<?php

class mdl_lottery extends mdl_base
{

	protected $tableName = '#@_lottery';

	public function getUserRewardItems($userId,$business_id=null)
	{
		$sql = "SELECT lc.userId,l.title, l.id as lid, lc.id as lcid, ld.lottery_sub_name,ld.lottery_sub_details FROM cc_lottery_records as lc left join cc_lottery as l on lc.lottery_id = l.id left join cc_lottery_details as ld on (lc.lottery_id = ld.lottery_id and lc.lottery_sub_id = ld.lottery_sub_id) where ld.is_award = 1 and lc.status=2";

		if($business_id){
			$sql .=" and l.createUserId=".$business_id;
		}

		if($userId){
			$sql .=" and lc.userId=".$userId;
		}

		$sql .=" order by lc.userId ";

		return $this->getListBySql($sql);

	}

}

?>