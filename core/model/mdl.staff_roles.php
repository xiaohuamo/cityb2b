<?php

class mdl_staff_roles extends mdl_base
{
    protected $tableName = '#@_staff_roles';


    public function getRoleList($loginUser,$role) {
        //如果用户类型为3 代表为商家owner ,直接显示所有可用的导航面板
       if ($role ==3) {
           $role_result= $this->filterRolesResult(null,3);
          // var_dump('here'); exit;
           return $role_result;
       }

        $where =array(
          'staff_id'=>$loginUser
        );

        $staff_roles = $this->getByWhere($where);

        if($staff_roles){
        /*
          $rolesArr= explode(',',$staff_roles['roles']);
          foreach ($rolesArr as $key=>$value) {
              laod
          } */
            $roles = $staff_roles['roles'];
            $roles ='-1'.$roles.'-1';
            $sql ="select * from cc_roles where id in ($roles) " ;
            $role_list = loadModel('roles')->getListBySql($sql);
            $role_result= $this->filterRolesResult($role_list,20);
            return $role_result;
        }

        return  null;

    }



   function generateFullPanelList() {
       $admin_list =array();
       $data=array(
           'id'=>6,
           'title'=>  'Sales Management Panel',
           'title_cn'=> '销售管理入口',
           'link'=>  HTTP_ROOT_WWW."factory/employee_navigation_panel?role_id=6",//销售经理
           'role_pic'=>HTTP_ROOT_WWW.'themes/'.STYLE.'img/storeMe4.png'
       );

       $admin_list[] =$data;

       $data=array(
           'id'=>9,
           'title'=>  'Dispatching Managment Panel',
           'title_cn'=> '物流管理入口',
           'link'=>  HTTP_ROOT_WWW."factory/employee_navigation_panel?role_id=9",//销售经理
           'role_pic'=>HTTP_ROOT_WWW.'themes/'.STYLE.'img/jhy8_.png'
       );

       $admin_list[] =$data;

       $data=array(
           'id'=>11,
           'title'=>  'Producing Management Panel',
           'title_cn'=> '生产管理入口',
           'link'=>  HTTP_ROOT_WWW."factory/employee_navigation_panel?role_id=11",//销售经理
           'role_pic'=>HTTP_ROOT_WWW.'themes/'.STYLE.'img/jhy4.png'
       );

       $admin_list[] =$data;


       $data=array(
           'id'=>16,
           'title'=>  'Driver Management Panel',
           'title_cn'=> '司机入口',
           'link'=>  HTTP_ROOT_WWW."factory/employee_navigation_panel?role_id=16",//销售经理
           'role_pic'=>HTTP_ROOT_WWW.'themes/'.STYLE.'img/driverMe2.png'
       );

       $admin_list[] =$data;
       return  $admin_list;


   }






    // 根据当前登陆的企业员工的角色，创建管理pannel显示面板，包括中英文的相关信息和链接信息
    function filterRolesResult($role_list,$roleType){
       //var_dump($role_list);exit;
       if($roleType==3) {
           $admin_list = $this->generateFullPanelList();


           return $admin_list;

       }




        foreach ($role_list as $key=>$value){
            if($value['id'] ==0 || $value['id']==1)  { //如果为管理元或ceo 则拥有全部权限；

                $admin_list = $this->generateFullPanelList();


              //   var_dump($admin_list);exit;
                 return $admin_list;
            }
        }


        foreach ($role_list as $key=>$value){
            $roleId =$value['id'];

                if($roleId==5 || $roleId==6 ){ //销售
                    $role_list[$key]['title'] = 'Sales Management Panel';
                    $role_list[$key]['title_cn'] = '销售管理入口';
                    $role_list[$key]['link'] =HTTP_ROOT_WWW."factory/employee_navigation_panel?role_id=$roleId";
                    $role_list[$key]['show'] =1;
                    $role_list[$key]['role_pic'] =HTTP_ROOT_WWW.'themes/'.STYLE. $role_list[$key]['role_pic'];

                    continue;
                }
                if($roleId==9 ||$roleId==10 || $roleId==12){ //销售
                    $role_list[$key]['title'] = 'Dispatching Managment Panel';
                    $role_list[$key]['title_cn'] = '物流管理入口';
                    $role_list[$key]['link'] =HTTP_ROOT_WWW."factory/employee_navigation_panel?role_id=$roleId";
                    $role_list[$key]['show'] =1;
                    $role_list[$key]['role_pic'] =HTTP_ROOT_WWW.'themes/'.STYLE. $role_list[$key]['role_pic'];
                    continue;
                }
                if($roleId==11 ){ //销售
                    $role_list[$key]['title'] = 'Producing Management Panel';
                    $role_list[$key]['title_cn'] = '生产管理入口';
                    $role_list[$key]['link'] =HTTP_ROOT_WWW."factory/employee_navigation_panel?role_id=$roleId";
                    $role_list[$key]['show'] =1;
                    $role_list[$key]['role_pic'] =HTTP_ROOT_WWW.'themes/'.STYLE. $role_list[$key]['role_pic'];
                    continue;
                }
                if($roleId==16 ){ //销售
                    $role_list[$key]['title'] = 'Driver Management Panel';
                    $role_list[$key]['title_cn'] = '司机入口';
                    $role_list[$key]['link'] =HTTP_ROOT_WWW."factory/employee_navigation_panel?role_id=$roleId";
                    $role_list[$key]['show'] =1;
                    $role_list[$key]['role_pic'] =HTTP_ROOT_WWW.'themes/'.STYLE. $role_list[$key]['role_pic'];
                    continue;
                }



          


        }
     //做数组去重，及去掉 show 字段为0 的数组
        $key1 ='title';
        $role_list = $this->assoc_unique($role_list, $key1);
      //  var_dump($role_list); exit;
        return $role_list;
    }


    function assoc_unique($arr, $key) {

        $tmp_arr = array();

        foreach ($arr as $k => $v) {
           if(!$v[$key]) {
               unset($arr[$k]);
               continue;
           }

            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true

                unset($arr[$k]);

            } else {

                $tmp_arr[] = $v[$key];

            }

        }

        sort($arr); //sort函数对数组进行排序

        return $arr;

    }


    public function  getAllDriverOfBusiness($business_id){

        $sql ="select user.id,user.name,user.contactPersonNickName,driver.default_start_time,default_end_time  from cc_user user
            left join cc_staff_roles staff on user.id =staff.staff_id 
            left join cc_driver_base_info driver on user.id =driver.driver_id  
            where user.user_belong_to_user =$business_id and user.role =20 
            and driver.status =1  and ( staff.roles like '%,0,%' or staff.roles like '%,1,%'or staff.roles like '%,16,%' )
            ";

        $list= $this->getlistbysql($sql);

        foreach ($list as $key=>$value){

            $start_time =$value['default_start_time'];
            if($start_time) {

                $pos =strpos($start_time,':',0);
                if($pos){
                    $list[$key]['start_time_hour'] =substr($start_time,0,$pos);
                    // var_dump(  $value['start_time_hour']);exit;
                    $list[$key]['start_time_minute'] =substr($start_time,$pos+1);
                    //var_dump(  $value['start_time_minute']);exit;
                }
            }

            $end_time =$value['default_end_time'];
            if($end_time) {

                $pos =strpos($end_time,':',0);
                if($pos){
                    $list[$key]['end_time_hour'] =substr($end_time,0,$pos);
                    // var_dump(  $value['start_time_hour']);exit;
                    $list[$key]['end_time_minute'] =substr($end_time,$pos+1);
                    //  var_dump(  $value['end_time_hour']);exit;
                }
            }




        }
        return $list;

    }

    public function  getAllDriverOfBusinessWithDriverbaseInfo($business_id){

        $sql ="select user.id,user.name,user.contactPersonNickName ,
       if(length(driver.start_location)>2,driver.start_location,(select googleMap from cc_user where id =$business_id)) as start_location,
        if(length(driver.end_location)>2,driver.end_location,(select googleMap from cc_user where id =$business_id)) as end_location,
      if(length(driver.default_start_time)>1,driver.default_start_time,'00:00') as default_start_time,
        if(length(driver.default_end_time)>1,driver.default_end_time,'00:00') as default_end_time,driver.status,driver.driver_id 
            
       from cc_user user
            left join cc_staff_roles staff on user.id =staff.staff_id
            left join cc_driver_base_info driver on user.id =driver.driver_id
            where user.user_belong_to_user =$business_id and user.role =20 
            and ( staff.roles like '%,0,%' or staff.roles like '%,1,%'or staff.roles like '%,16,%' )
            ";

        $list= $this->getlistbysql($sql);
        return $list;

    }
    public function  getAllDriverOfBusinessSchedueld($business_id){

        $sql ="select user.id,user.name,user.contactPersonNickName from cc_user user
            left join cc_staff_roles staff on user.id =staff.staff_id 
           where user.user_belong_to_user =$business_id  and user.id in (select distinct driver_id from cc_truck_driver_schedule where factory_id =$business_id) ";
        $list= $this->getlistbysql($sql);
        return $list;

    }

    public function  CanOperateAllCustomer($user_id){
      $rec =$this->getByWhere(array('staff_id'=>$user_id));
      $roles =$rec['roles'];
      if(strstr($roles,',0,') || strstr($roles,',1,') || strstr($roles,',2,')){
         // var_dump('eiwowo');exit;
          return 1;
      }
        return 0;
    }

}

?>