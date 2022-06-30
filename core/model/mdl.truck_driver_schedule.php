<?php

class mdl_truck_driver_schedule extends mdl_base
{

	protected $tableName = '#@_truck_driver_schedule';

    public function getFactoryNewScheduleId($factory_id){

        $sql ="select schedule_id from cc_truck_driver_schedule where factory_id =$factory_id order by schedule_id desc limit 1";
        $list =$this->getListBySql($sql);
        if($list){
            $schedule_id = $list[0]['schedule_id'];
            return $schedule_id+1;
        }else{
            return 1;
        }

    }

    public function getScheduleList($customer_id,$customer_delivery_date){

        $sql ="select schedule_id from cc_truck_driver_schedule where factory_id =$factory_id order by schedule_id desc limit 1";
        $list =$this->getListBySql($sql);
        if($list){
            $schedule_id = $list[0]['schedule_id'];
            return $schedule_id+1;
        }else{
            return 1;
        }

    }

    public function getDeliveryDateSchedule($factory_id,$customer_delivery_date){
        $delivery_date = strtotime($customer_delivery_date);

        $sql ="SELECT s.*,from_unixtime(s.delivery_date,'%Y-%m-%d') as delivery_date_str ,from_unixtime(s.schedule_start_time,'%H:%i') as start_hour ,from_unixtime(s.schedule_end_time,' %H:%i') as end_hour ,concat(t.truck_name,'-',t.plate_number) as truck_name,u.name,u.displayName,u.person_first_name,u.person_last_name ,if(length(u.displayName)>0,u.displayName,if(length(u.person_first_name)>0,concat(u.person_first_name,' ',u.person_last_name),u.name)) as driverName FROM `cc_truck_driver_schedule` s left join cc_truck t on s.factory_id=t.business_id and s.truck_id =t.truck_no  left join cc_user u on s.driver_id =u.id  where factory_id=$factory_id and delivery_date=$delivery_date";
        $list =$this->getListBySql($sql);
        return $list;
    }

}

?>