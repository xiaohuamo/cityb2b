 <?php
 


class mdl_wj_customer_coupon_delete_details extends mdl_base
{

	protected $tableName = '#@_wj_customer_coupon_delete_details';

    public function insertCurrentRecord($id,$operUser){
        $sql ="insert into cc_wj_customer_coupon_delete_details select * from cc_wj_customer_coupon where id =$id";
        $this->getListBySql($sql);
        $create_user =array(
            'group_buying_id'=>$operUser
        );
        $this->update($create_user,$id);

 
    }

    public function restoreOrder($id){

        $sql ="insert into cc_wj_customer_coupon select * from cc_wj_customer_coupon_delete_details where order_id =$id";

      // var_dump($sql);exit;
        $this->getListBySql($sql);
        $sql ="select  sum(	new_customer_buying_quantity *voucher_deal_amount) as sum_adjust_total  from cc_wj_customer_coupon_delete_details where order_id =$id";
        $sum_rec =   $this->getListBySql($sql);

        $this->deleteByWhere(array('order_id'=>$id));
        if ($sum_rec) {
            return $sum_rec[0]['sum_adjust_total'];
        }else{
            return 0;
        }

    }


}

?>