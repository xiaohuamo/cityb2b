<?php

class mdl_picking extends mdl_base
{

	protected $tableName = '#@_picking';

    //根据商家及客户编号返回该客户的信息
    // 正常来说，取货必须是有送货的情况下，所以，我们取该客户最后一笔order的客户信息，如果没有则提示错误
    public function get_picking_customer_info($business_id,$user_id) {
        $sql ="select  * from cc_order where business_userId =$business_id and userId =$user_id order by id desc limit 1";
        $rec =loadModel('order')->getListBySql($sql);
        return $rec[0];

    }


  public function generatePickingListSql($factoryId,$customer_id,$startTime,$endTime){



        $sql ="select * from cc_picking where business_userId=$factoryId ";
        if($customer_id && $customer_id !='all'){
            $sql .= " and userId = $customer_id  ";
        }
        if($startTime) {
            $startTime =strtotime($startTime);
            $sql .= "  and logistic_delivery_date >=$startTime  ";
        }
        if($startTime){
            $endTime =strtotime($endTime);
            $sql .= " and logistic_delivery_date<= $endTime ";
        }
        $sql .= "  order by id desc " ;

      //  var_dump($sql);exit;
        return $sql;
    }

}

