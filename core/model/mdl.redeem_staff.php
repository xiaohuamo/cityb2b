<?php

class mdl_redeem_staff extends mdl_base
{

	protected $tableName = '#@_redeem_staff';

	public function existInCompany($userId,$businessId){
		$where['user_id']=$userId;
		$where['business_id']=$businessId;

		$data=$this->getByWhere($where);

		return ($data!=null);
	}

	public function joinCompany($userId,$businessId){
		$data['user_id']=$userId;
		$data['business_id']=$businessId;
		$data['date']=time();
		return $this->insert($data);
	}

	public function leaveCompany($userId,$businessId){
		$where['user_id']=$userId;
		$where['business_id']=$businessId;

		$this->deleteByWhere($where);
	}

	public function getStaffList($businessId){
		$where['business_id']=$businessId;

		$sql ="SELECT u.id, u.name, u.nickname,s.date 
				FROM cc_redeem_staff as s 
				LEFT JOIN cc_user as u 
				on s.user_id = u.id 
				WHERE s.business_id = 
				".$businessId;

		return $this->getListBySql($sql);
	}

	public function getBusinessList($userId){
		$where['user_id']=$userId;
		return $this->getList('business_id',$where);
	}

	public function isRedeemStaff($userId){
		$data = $this->	getBusinessList($userId);
		return(sizeof($data)!=0);
	}

	public function getBusinessListArray($userId){
		$data = $this->	getBusinessList($userId);

		$result = array();
		foreach ($data as $item) {
			array_push($result, $item['business_id']);
		}

		return $result;
	}


}

?>