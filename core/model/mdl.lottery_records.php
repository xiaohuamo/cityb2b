<?php

class mdl_lottery_records extends mdl_base
{

	protected $tableName = '#@_lottery_records';

	
	public function getWinningRecordsOfUser($userId)
	{

		$sql = $this->getWinningRecordsOfUserSql($userId);
		return $this->getListBySql($sql);
	}

	public function getWinningRecordsOfUserSql($userId)
	{

		$sql .=" SELECT a.* , b.lottery_sub_name, b.lottery_sub_details, c.status_cn_name, d.businessName";
		$sql .=" FROM cc_lottery_records a, cc_lottery_details b, cc_lottery_status c, cc_user d, cc_lottery e";
		$sql .=" WHERE a.lottery_id = b.lottery_id";
		$sql .=" AND a.status = c.status_id";
		$sql .=" AND a.lottery_id = e.id";
		$sql .=" AND e.createUserId = d.id";
		$sql .=" AND a.lottery_sub_id = b.lottery_sub_id";
		$sql .=" AND a.userId ='$userId'";
		$sql .=" AND a.is_award =1 order by a.id desc";

		return $sql;
	}

	public function getWinningRecordsOfBusiness($businessId,$lotteryId)
	{
        $sql = $this->getWinningRecordsOfBusinessSql($businessId,$lotteryId);
		return $this->getListBySql($sql);
	}

	public function getWinningRecordsOfBusinessSql($businessId,$lotteryId,$search=null)
	{
        $sql.="SELECT a.* , b.title, c.person_first_name, c.person_last_name, c.phone, c.email,";
        $sql.=" d.lottery_sub_name, d.lottery_sub_details,e.status_cn_name";
        $sql.=" FROM cc_lottery_records a, cc_lottery b, cc_user c, cc_lottery_details d, cc_lottery_status e";
        $sql.=" WHERE a.lottery_id = b.id";
        $sql.=" AND a.userId = c.id";
        $sql.=" AND a.lottery_id = d.lottery_id";
        $sql.=" AND a.lottery_sub_id = d.lottery_sub_id";
        $sql.=" AND a.status = e.status_id";
        $sql.=" AND a.is_award =1";
        $sql.=" AND b.createUserId ='$businessId'";
        if($lotteryId)$sql.=" AND b.id ='$lotteryId'";
        if($search)$sql.=" AND (a.id ='$search' or c.phone like '%$search%' or c.email like '%$search%' or c.person_first_name like '%$search%' or c.person_last_name like '%$search%' )";
        $sql.="  order by a.id desc ";

		return $sql;
	}
	
	public function getWinningRecordsOfBusiness_keywords_Sql($keywords,$status_id,$lottery_id)
	{
		
        $sql.="SELECT a.* ,b.createUserId, b.title, c.person_first_name, c.person_last_name, c.phone, c.email,";
        $sql.=" d.lottery_sub_name, d.lottery_sub_details,e.status_cn_name";
        $sql.=" FROM cc_lottery_records a, cc_lottery b, cc_user c, cc_lottery_details d, cc_lottery_status e";
        $sql.=" WHERE a.lottery_id = b.id";
        $sql.=" AND a.userId = c.id";
        $sql.=" AND a.lottery_id = d.lottery_id";
        $sql.=" AND a.lottery_sub_id = d.lottery_sub_id";
        $sql.=" AND a.status = e.status_id";
        $sql.=" AND a.is_award =1 ";
		if($keywords){
		  $sql.=" and( ";
		  $sql.=" c.phone like '%$keywords%' or  ";
		  $sql.=" c.person_first_name like '%$keywords%' or ";	
		  $sql.=" c.person_last_name like '%$keywords%' or ";
		  $sql.=" CONCAT(c.person_first_name,' ',c.person_last_name) like '%$keywords%' or ";
		    $sql.=" CONCAT(c.person_last_name,' ',c.person_first_name) like '%$keywords%' or ";
		   $sql.=" c.email like '%$keywords%' or ";
		  $sql.=" d.lottery_sub_details like '%$keywords%' or ";
		  $sql.=" b.title like '%$keywords%' or ";
		  $sql.=" a.redeem_code like '%$keywords%'  "; 
		  $sql.="  ) ";
		}
		
		if($status_id){
		  $sql.=" and(  a.status ='$status_id' ) ";
		}
		
		if($lottery_id){
		  $sql.=" and(  a.lottery_id ='$lottery_id' ) ";
		}
		
		$sql.=" order by a.id desc";
		
		return $sql;
	}

	public function updateWinningRecordsStatus($id,$status)
	{
		$data['status']=$status;
		return $this->update($data,$id);
	}

}

?>