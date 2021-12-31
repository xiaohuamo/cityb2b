<?php

class mdl_wj_abn_application extends mdl_base
{

	protected $tableName = '#@_wj_abn_application';

    public function getBusinessInfo($userid){
       // $sql ="select * from cc_wj_abn_application where userId = $userid";
//var_dump($sql); exit;
        return $this->getByWhere(array('userId'=>$userid));
    }
}

?>