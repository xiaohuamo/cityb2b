
<?php

class mdl_wj_user_delivery_info extends mdl_base
{

	protected $tableName = '#@_wj_user_delivery_info';
	public function getDeliverUserInfo($user_id){

        $where =array(
            'userId'=>$user_id,
            'isDefaultAddress'=>1
        );
        //获得默认地址信息
        $wj_user_delivery_info = $this->getbyWhere($where);
        //如果没有默认地址信息，看看有没有其它信息
       // var_dump($wj_user_delivery_info);exit;
        if(!$wj_user_delivery_info){
            $where =array(
                'userId'=>$user_id
            );
            $wj_user_delivery_info = $this->getbyWhere($where);
            //var_dump($wj_user_delivery_info);exit;
            if($wj_user_delivery_info){
                $this->update(array('isDefaultAddress'=>1),$wj_user_delivery_info['id']);

            }
            //如果有，把这个信息改为默认地址信息，然后返回这个地址信息
            return $wj_user_delivery_info;
        }
        return $wj_user_delivery_info;
    }
}

?>