<?php

class mdl_staff_roles extends mdl_base
{
    protected $tableName = '#@_staff_roles';


    public function getRoleList($loginUser) {
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
            $role_result= $this->filterRolesResult($role_list);
            return $role_result;
        }

        return  null;

    }
    // 根据当前登陆的企业员工的角色，创建管理pannel显示面板，包括中英文的相关信息和链接信息
    function filterRolesResult($role_list){
       //var_dump($role_list);exit;



        $allPermission =0;

        foreach ($role_list as $key=>$value){
            if($value['id'] ==0 || $value['id']==1)  { //如果为管理元或ceo 则拥有全部权限；
                $allPermission =1;
            }
        }


        foreach ($role_list as $key=>$value){
            $roleId =$value['id'];
            if($roleId ==0 || $roleId==1)  { //如果为管理元或ceo 则拥有全部权限；

            }else{
                if($roleId==5 || $roleId==6 ){ //销售
                    $role_list[$key]['title'] = 'Sales Management Panel';
                    $role_list[$key]['title_cn'] = '销售管理入口';
                    $role_list[$key]['link'] =HTTP_ROOT_WWW."factory/employee_navigation_panel($roleId)";
                    $role_list[$key]['show'] =1;
                    continue;
                }
                if($roleId==9 ||$roleId==10 || $roleId==12){ //销售
                    $role_list[$key]['title'] = 'Dispatching Managment Panel';
                    $role_list[$key]['title_cn'] = '物流管理入口';
                    $role_list[$key]['link'] =HTTP_ROOT_WWW."factory/employee_navigation_panel($roleId)";
                    $role_list[$key]['show'] =1;
                    continue;
                }
                if($roleId==11 ){ //销售
                    $role_list[$key]['title'] = 'Producing Management Panel';
                    $role_list[$key]['title_cn'] = '生产管理入口';
                    $role_list[$key]['link'] =HTTP_ROOT_WWW."factory/employee_navigation_panel($roleId)";
                    $role_list[$key]['show'] =1;
                    continue;
                }
                if($roleId==16 ){ //销售
                    $role_list[$key]['title'] = 'Driver Management Panel';
                    $role_list[$key]['title_cn'] = '司机入口';
                    $role_list[$key]['link'] =HTTP_ROOT_WWW."factory/employee_navigation_panel($roleId)";
                    $role_list[$key]['show'] =1;
                    continue;
                }



            }


        }
     //做数组去重，及去掉 show 字段为0 的数组
        $key1 ='title';
        $role_list = $this->assoc_unique($role_list, $key1);
      var_dump($role_list); exit;
      这里看一下，要将没有show的去掉，
      另外这一块再整理一下，做到相当规范。
    }


    function assoc_unique($arr, $key) {

        $tmp_arr = array();

        foreach ($arr as $k => $v) {

            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true

                unset($arr[$k]);

            } else {

                $tmp_arr[] = $v[$key];

            }

        }

        sort($arr); //sort函数对数组进行排序

        return $arr;

    }

}

?>