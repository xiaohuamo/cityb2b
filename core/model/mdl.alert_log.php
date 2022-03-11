<?php

class mdl_alert_log extends mdl_base
{

	protected $tableName = '#@_alert_log';
    public function  insertAlertLog($user,$type,$act){
        $data =array (
            'type' =>$type,
            'user_id'=>$user['id'],
            'gen_time'=>time(),
            'ip'=>ip(),
            'action'=>$act
        );
        $this->insert($data);

    }
}

?>