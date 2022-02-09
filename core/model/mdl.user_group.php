<?php

class mdl_user_group extends mdl_base
{

	protected $tableName = '#@_user_group';

    public function getListOfGroupUser($manager_id){

        $sql ="SELECT u.*
                FROM cc_user_group g 
                LEFT JOIN cc_user u ON g.user_id  = u.id 
			    WHERE g.manager_id =$manager_id ";


        $list = $this->getListBySql($sql);

        return $list;
    }

    public function getCountOfMembers($userId){

        $where =array(
            'manager_id'=>$userId
        );
        $count = $this->getCount($where);

        return $count;
    }

}

?>