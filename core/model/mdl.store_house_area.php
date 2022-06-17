<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/3 0003
 * Time: 上午 10:25
 */
class mdl_store_house_area extends mdl_base
{
    protected $tableName = '#@_store_house_area';

    function  getAreaList($id){

        $sql = "select store.code , area.* from cc_store_house_area area left join cc_store_house store on area.store_house_id = store.id where area.store_house_id = $id order by area.sort_id ";
        $result = $this->getListBySql($sql);

        return $result;

    }

     function getCountOfStoreRoomOfFactory($factory_id,$house_id){

        $sql = "select count(*) as count from cc_store_house_area where factory_id = $factory_id and store_house_id =$house_id ";
        $list = $this->getListBySql($sql);
        //var_dump($sql);exit;
        return $list[0]['count'];

    }
}
?>