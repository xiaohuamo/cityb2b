<?php

class mdl_truck extends mdl_base
{

	protected $tableName = '#@_truck';
    public function getAllTruckOfBusiness($business_id) {
		
		$sql =" select * from cc_truck where business_id =$business_id and isAvaliable =1";
		$allTruckList  = $this->getListBySql($sql);
		return $allTruckList;
		
		
	}
	public function getAllTruckOfBusinessWithOrderCounts($business_id,$delivery_date) {
		
		$sql =" select t.*,o.count from cc_truck t left join  
		
		(select logistic_truck_No,if null(count(*),0) as count  from cc_order 
        where business_userId =$business_id and  logistic_delivery_date =".strtotime($delivery_date)." and coupon_status ='c01' and status=1
		group by logistic_truck_No ) as o  
		on t.id =o.logistic_truck_No
		where t.business_id =$business_id and t.isAvaliable =1";
		//var_dump($sql);exit;
		$allTruckList  = $this->getListBySql($sql);
		//var_dump($allTruckList);exit;
		return $allTruckList;
		
		
	}
	
	public function getAllOrdersTruckListwithCount($business_id,$delivery_date) {
		
		$sql =" select o.logistic_truck_No ,t.truck_name,count(*) as count  from cc_order o 
				left join cc_truck t on o.logistic_truck_No=t.id
				where o.logistic_delivery_date = ".strtotime($delivery_date)."  and o.business_userId=$business_id and o.coupon_status ='c01' and o.status=1 
				group by o.logistic_truck_No";
		//var_dump($sql);exit;
		$allTruckList  = $this->getListBySql($sql);
		//var_dump($allTruckList);exit;
		return $allTruckList;
		
		
	}
}

?>