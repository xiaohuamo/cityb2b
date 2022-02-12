<?php

class mdl_truck extends mdl_base
{

	protected $tableName = '#@_truck';
    public function getAllTruckOfBusiness($business_id) {
		
		$sql =" select * from cc_truck where business_id =$business_id and isAvaliable =1";
		$allTruckList  = $this->getListBySql($sql);
		return $allTruckList;
		
		
	}
}

?>