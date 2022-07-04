<?php

class mdl_truck extends mdl_base
{

	protected $tableName = '#@_truck';
    public function getAllTruckOfBusiness($business_id) {
		
		$sql =" select t.* ,if(length(contactPersonNickName)>0,u.contactPersonNickName,concat(u.contactPersonFirstname,' ',u.contactPersonLastname)) as driverName  from cc_truck t
         left join cc_user u on t.current_driver =u.id
        where business_id =$business_id and isAvaliable =1";
		$allTruckList  = $this->getListBySql($sql);
		return $allTruckList;
		
		
	}

    public function getAllTruckOfBusiness1($business_id) {

        $sql =" select t.* ,if(length(contactPersonNickName)>0,u.contactPersonNickName,concat(u.contactPersonFirstname,' ',u.contactPersonLastname)) as driverName  from cc_truck t
         left join cc_user u on t.current_driver =u.id
        where business_id =$business_id order by isAvaliable desc ";
        $allTruckList  = $this->getListBySql($sql);
        return $allTruckList;


    }

	public function getAllTruckOfBusinessWithOrderCounts($business_id,$delivery_date) {
		
		$sql =" select t.*,o.count from cc_truck t left join  
		
		(select logistic_truck_No,if null(count(*),0) as count  from cc_order 
        where business_userId =$business_id and  logistic_delivery_date =".strtotime($delivery_date)." and coupon_status ='c01' and (status =1 or accountPay=1) 
		group by logistic_truck_No ) as o  
		on t.id =o.logistic_truck_No
		where t.business_id =$business_id and t.isAvaliable =1";
		var_dump($sql);exit;
		$allTruckList  = $this->getListBySql($sql);
		//var_dump($allTruckList);exit;
		return $allTruckList;
		
		
	}
	
	public function getAllOrdersTruckListwithCount($business_id,$delivery_date) {
		
	/*	$sql =" select o.logistic_truck_No ,t.*,ifnull(count(*),0) as count,concat(u.contactPersonFirstname,' ',u.contactPersonLastname) as driverName from cc_order o
				left join cc_truck t  on o.logistic_truck_No = t.truck_no and o.business_userId=t.business_id  
                left join cc_user u on t.current_driver =u.id
 		    	where o.logistic_delivery_date = ".strtotime($delivery_date)."  and o.business_userId=$business_id and o.coupon_status ='c01' and (o.status =1 or o.accountPay=1)  
				group by o.logistic_truck_No order by o.logistic_truck_No"; */
		//var_dump($sql);exit;
		$sql="select o.logistic_schedule_id ,s.*,t.truck_name,from_unixtime(s.schedule_start_time,'%H:%i') as start_hour,t.plate_number,ifnull(count(DISTINCT o.orderId),0) as count,concat(u.contactPersonFirstname,' ',u.contactPersonLastname) as driverName,

 sum(o.boxesNumber) as boxes

from cc_order o 
     left join cc_truck_driver_schedule s on o.logistic_schedule_id =s.schedule_id and o.business_userId =s.factory_id 
         left join cc_truck t on s.truck_id = t.truck_no and s.factory_id=t.business_id 
    left join cc_user u on s.driver_id =u.id 


where o.logistic_delivery_date =  ".strtotime($delivery_date)."  and o.business_userId=$business_id and o.coupon_status ='c01' and (o.status =1 or o.accountPay=1) group by o.logistic_schedule_id order by o.logistic_schedule_id";


        $allTruckList  = $this->getListBySql($sql);
        //var_dump($sql);exit;
		return $allTruckList;
		
		
	}

    public function  getTruckAndDriverInfo($logistic_truck_No) {

        $rec = $this->get($logistic_truck_No);
        if($rec){
             if($rec['current_driver']) {
                 $driver = loadModel('user')->get($rec['current_driver']);
                 $driverAndTruckInfo = $driver['contactPersonFirstname'].' '.$driver['contactPersonLastname'].'-'.$rec['truck_name'].'-'.$rec['plate_number'];
             }else{
                 $driverAndTruckInfo =$rec['truck_name'].'-'.$rec['plate_number'];
             }

        }else{

            $driverAndTruckInfo ='All';
        }
      return $driverAndTruckInfo;

    }

    public function  getTruckAndDriverInfo1($logistic_truck_No,$factoryId) {

        $where =array(

            'truck_no'=>$logistic_truck_No,
            'business_id'=>$factoryId
        );

        $rec = $this->getByWhere($where);
        if($rec){
            if($rec['current_driver']) {
                $driver = loadModel('user')->get($rec['current_driver']);
                $driverAndTruckInfo = $driver['contactPersonFirstname'].'-'.$rec['truck_name'].'-'.$rec['plate_number'];
            }else{
                $driverAndTruckInfo =$rec['truck_name'].'-'.$rec['plate_number'];
            }

        }else{

            $driverAndTruckInfo ='All';
        }
        return $driverAndTruckInfo;

    }
}

?>