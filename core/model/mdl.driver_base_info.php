<?php

class mdl_driver_base_info extends mdl_base
{
    protected $tableName = '#@_driver_base_info';

    public function  getDriverbaseInfo($business_id,$id){

        $sql ="select user.id,user.name,user.contactPersonNickName ,driver.driver_id,
       if(length(driver.start_location)>2,driver.start_location,(select googleMap from cc_user where id =$business_id)) as start_location,
        if(length(driver.end_location)>2,driver.end_location,(select googleMap from cc_user where id =$business_id)) as end_location,
      if(length(driver.default_start_time)>1,driver.default_start_time,'07:00') as default_start_time,
        if(length(driver.default_end_time)>1,driver.default_end_time,'16:00') as default_end_time,driver.status ,driver.driver_id ,driver.start_lat, 
      driver.start_long,driver.end_lat,driver.end_long   
       from cc_user user
            left join cc_staff_roles staff on user.id =staff.staff_id
            left join cc_driver_base_info driver on user.id =driver.driver_id
            where user.id =$id and (user.user_belong_to_user =$business_id  or user.id =$business_id) and user.role =20 
            and ( staff.roles like '%,0,%' or staff.roles like '%,1,%'or staff.roles like '%,16,%' )
            ";

        $list= $this->getlistbysql($sql);
        if($list){
            $driverRec = $list[0];
            // split start time & end time
            $start_time =$driverRec['default_start_time'];
            if($start_time) {

                $pos =strpos($start_time,':',0);
                if($pos){
                    $driverRec['start_time_hour'] =substr($start_time,0,$pos);
                   // var_dump(  $driverRec['start_time_hour']);exit;
                    $driverRec['start_time_minute'] =substr($start_time,$pos+1);
                    //var_dump(  $driverRec['start_time_minute']);exit;
                }
            }

            $end_time =$driverRec['default_end_time'];
            if($end_time) {

                $pos =strpos($end_time,':',0);
                if($pos){
                    $driverRec['end_time_hour'] =substr($end_time,0,$pos);
                    // var_dump(  $driverRec['start_time_hour']);exit;
                    $driverRec['end_time_minute'] =substr($end_time,$pos+1);
                  //  var_dump(  $driverRec['end_time_hour']);exit;
                }
            }
           // var_dump(  $driverRec);exit;
            return $driverRec;
        }else{
            return 0;
        }


    }

}

?>