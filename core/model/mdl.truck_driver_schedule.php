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

        $sql ="SELECT s.*,from_unixtime(s.delivery_date,'%Y-%m-%d') as delivery_date_str ,from_unixtime(s.schedule_start_time,'%H:%i') as start_hour ,from_unixtime(s.schedule_end_time,' %H:%i') as end_hour ,concat(t.truck_name,'-',t.plate_number) as truck_name,if(length(u.contactPersonNickName)>0,u.contactPersonNickName,concat(u.contactPersonFirstname,' ',u.contactPersonLastname)) as driverName ,u.name,u.displayName,u.person_first_name,u.person_last_name ,if(length(u.displayName)>0,u.displayName,if(length(u.person_first_name)>0,concat(u.person_first_name,' ',u.person_last_name),u.name)) as driverName1 FROM `cc_truck_driver_schedule` s left join cc_truck t on s.factory_id=t.business_id and s.truck_id =t.truck_no  left join cc_user u on s.driver_id =u.id  where factory_id=$factory_id and delivery_date=$delivery_date order by schedule_start_time";
        $list =$this->getListBySql($sql);
       // var_dump($sql);exit;
        return $list;
    }

    public function  getTruckAndDriverInfo1($logistic_schedule_id,$factoryId) {

        $where =array(

            'schedule_id'=>$logistic_schedule_id,
            'factory_id'=>$factoryId
        );

        $rec = $this->getByWhere($where);
       // var_dump($rec);exit;

        if($rec){

            $where1 =array(
                'truck_no'=>$rec['truck_id'],
                'business_id'=>$factoryId
            );
            $rec_truck = loadModel('truck')->getByWhere($where1);



            if($rec['driver_id']) {
                $driver = loadModel('user')->get($rec['driver_id']);
                $driverAndTruckInfo = $driver['contactPersonFirstname'].' '.$rec_truck['truck_name'].'-'.$rec_truck['plate_number'];
            }else{
                $driverAndTruckInfo =$rec_truck['truck_name'].'-'.$rec_truck['plate_number'];
            }

        }else{

            $driverAndTruckInfo ='All';
        }
        return $driverAndTruckInfo;

    }



}

?>