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
	
	public function getGroupListOfFactory($factoryId,$sk=null){
		
		if ($sk) {
			
			$where = " and (u.nickname like '%$sk%' or u.name like '%$sk%' or u.person_first_name like '%$sk%'  or u.person_last_name like '%$sk%' or u.displayName like '%$sk%' or  u.businessName like '%$sk%' )";
		}

       $sql =" SELECT g.manager_id, u.nickname,u.name,u.person_first_name,u.person_last_name,u.displayName,u.businessName
			 FROM `cc_user_group` g 
			 left join cc_user u on g.manager_id =u.id
			 WHERE g.factory_id =factoryId $where 
			 group by g.manager_id ";
	   
       $data =$this->getListBySql($sql);
        return $data;
    }

}

?>