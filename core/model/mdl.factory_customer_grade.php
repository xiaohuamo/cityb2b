<?php

class mdl_factory_customer_grade extends mdl_base
{

	protected $tableName = '#@_factory_customer_grade';

    public function getGradeList($factoryId){
        $sql ="select g.* , user.name from cc_factory_customer_grade g left join cc_user user on g.createUserId =user.id where g.business_id =$factoryId order by g.grade_id";
        return $this->getListBySql($sql);
    }



}

?>