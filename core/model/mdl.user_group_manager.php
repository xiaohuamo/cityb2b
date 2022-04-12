<?php

class mdl_user_group_manager extends mdl_base
{

	protected $tableName = '#@_user_group_manager';

    public function getGroupMangerInfo($userId, $factoryId) {

        $where = array(
            'factory_id'=>$factoryId,
            'manager_id'=>$userId
        );
        $count =$this->getCount($where);


        if ($count ==0) {
            return 0;
        }else{
            $sql ="select g.isApproved as g_approved,g.id as g_id,g.factory_id ,g.manager_id,g.nickname as nick,user.* from  cc_user_group_manager g 
                left join cc_user user on g.manager_id =user.id where  g.factory_id =$factoryId and g.manager_id =$userId";

            $data=$this->getListBySql($sql);
            return $data[0];
        }




    }

    public function getGroupListOfFactory($factory_id){
        $sql ="select g.isApproved as g_approved,g.id,g.manager_id as userId,g.nickname as name,user.phone  from cc_user_group_manager g left join cc_user user on g.manager_id =user.id  where factory_id =$factory_id";
        $data =$this->getListBySql($sql);
       // var_dump($sql);exit;
        return $data;

    }

}

?>