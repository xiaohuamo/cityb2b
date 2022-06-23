<?php

class mdl_order_return_detail_info extends mdl_base
{

	protected $tableName = '#@_order_return_detail_info';
    // get statement details for certain statement
    public function  getTotalCredit($id){

        $sql =" select d.order_return_id,d.item_order_id,d.return_qty,d.reasonType,d.note,c.*  
  from cc_order_return_detail_info d left join cc_wj_customer_coupon c on d.item_order_id =c.id 
      left join cc_order_return r on d.order_return_id =r.id where r.id =$id";
        $list =$this->getListBySql($sql);
       // var_dump($list);exit;
        $totalAmount =0.00;
        foreach ($list as $key=>$value){
            $totalAmount += $value['voucher_deal_amount'] *$value['return_qty'];

        }
        return $totalAmount;

    }
	

}

?>