<?php
//ini_set('memory_limit', '2048M');
class mdl_stock_assign extends mdl_base
{

	protected $tableName = '#@_stock_assign';
    /**
     * 获取订单需要的总箱数
     * @param $orderId 订单id
     */


    public function assgin_single_item_stock($value,$logistic_truck_No,$currentUserId){

          $item_id =$value['id'];
          $item_spec_id =$value['guige1_id'];
          $item_stock_qty = $value['stock_qty'];
          $order_qty =$value['total_quantity'];
          $delivery_date =$value['logistic_delivery_date'];
          $business_id =$value['business_id'];


          $mdl =loadModel('wj_customer_coupon');
          $this->LockItemOrderDetail($item_id,$item_spec_id,$business_id,$delivery_date,$logistic_truck_No);
          $item_details =  $this->getItemOrderDetail($item_id,$item_spec_id,$business_id,$delivery_date,$logistic_truck_No);
        // var_dump($item_details);exit;


          //如果发现分配记录则开始处理，未发现则直接返回；
        if($item_details){
            if($item_stock_qty>=$order_qty) {
                //  var_dump('full assign waiting assign ...');exit;
                //    $this->setAllOrderItemFromStock($value);
                foreach ($item_details as $key =>$value){
                    $data =array(
                        'assign_stock'=>1,
                        'is_producing_done'=>5, //设置标志等待巡检程序处理。
                        'operator_user_id'=>$currentUserId
                    );
                    $mdl->update($data,$value['id']);

                }
                return $order_qty;
            }else{
                // 获得所有的加工明细数据

                // 开始进行分配，并返回分配结果
//var_dump('here');exit;
                $result =    $this->stockAssign($item_details,$item_stock_qty);
                //  var_dump($result);exit;
                $assign_stock_qty =0.00;
                foreach ($result as $key =>$value){
                    $data =array(
                        'assign_stock'=>1,
                        'is_producing_done'=>5 ,//设置标志等待巡检程序处理。
                        'operator_user_id'=>$currentUserId
                    );
                    $mdl->update($data,$value['id']);
                    $assign_stock_qty += $value['new_customer_buying_quantity'];
                }

                //将所有未分配的明细全部设置为 is_producing_done
                foreach ($item_details as $key1 =>$val1){
                    $find=0;
                    foreach ($result as $key2 =>$val2){
                        if($val2['id']==$val1['id']) {
                            $find=1;
                            break;
                        }

                    }
                    //如果在已经分配库存的数组中找到 当前待分配的item ,那么就是不需要做复原工作，如果未发现，则需要做复原操作。
                    if(!$find){
                        $data =array(
                            'is_producing_done'=>0 //
                        );
                        $mdl->update($data,$val1['id']);
                    }
                }

                // 更新加工明细的分配结果，并标记。
                return $assign_stock_qty;
                // 减少相应库存
                // 结束

            }

        }else{
            return 0 ;
        }


    }

    function LockItemOrderDetail($item_id,$item_spec_id,$business_id,$delivery_date,$logistic_truck_No) {
        $sql ="update cc_wj_customer_coupon  set is_producing_done =3 
where restaurant_menu_id =$item_id and guige1_id =$item_spec_id and  business_id =$business_id and is_producing_done =0 and assign_stock =0 and order_id in ( select orderId from cc_order where orderId = cc_wj_customer_coupon.order_id and logistic_delivery_date = $delivery_date  )


";
        if($logistic_truck_No && $logistic_truck_No !='all'){
            $sql ="update cc_wj_customer_coupon  set is_producing_done =3 
where restaurant_menu_id =$item_id and guige1_id =$item_spec_id and  business_id =$business_id and is_producing_done =0 and assign_stock =0 and order_id in ( select orderId from cc_order where orderId = cc_wj_customer_coupon.order_id and logistic_delivery_date = $delivery_date and  logistic_truck_No =$logistic_truck_No )


";
        }

        $mdl =loadModel('wj_customer_coupon');
        $mdl->getListBySql($sql);
        return 1;
    }

    //获得当前商家，当  $mdl =loadModel('wj_customer_coupon');前日期，当前产品的订单数量信息
   function    getItemOrderDetail($item_id,$item_spec_id,$business_id,$delivery_date,$logistic_truck_No) {
        $sql ="SELECT c.id,c.business_id,c.restaurant_menu_id,c.guige1_id,c.guige_des,c.new_customer_buying_quantity FROM cc_wj_customer_coupon c 
    left join cc_order o on c.order_id =o.orderId   where  c.business_id=$business_id and c.restaurant_menu_id =$item_id and c.guige1_id =$item_spec_id and o.logistic_delivery_date =$delivery_date and c.assign_stock =0  and c.is_producing_done=3";

     if($logistic_truck_No && $logistic_truck_No !='all'){
         $sql .= " and o.logistic_truck_No =$logistic_truck_No ";
     }
       // var_dump($sql);exit;
       $mdl =loadModel('wj_customer_coupon');
        $result =$mdl->getListBySql($sql);
        return $result;

    }


    public function stockAssign($item_details,$stock_qty)
    {


        $splicing_arr = [];//存储拼箱成功的数据

        //如果有需要拼箱的数据，则计算拼箱的最优方案
        if($item_details){
            $d = array();//存储所有可能的组合排序
            $all_assign_arr = $this->permutations($item_details,$stock_qty);
        }else{
            $all_assign_arr = [];
        }


        return $all_assign_arr;
    }


    /**
     * @todo    Combination of an array
     *
     * @access  public
     * @param   array       $sort       An array of combinations
     * @param   int         $num        Elements to be taken
     * @return  array       $result     Combined array
     * */
    public function Combination($sort, $num)
    {
        $result = $data = array();
        if( $num == 1 ) {
            return $sort;
        }
        foreach( $sort as $k=>$v ) {
            unset($sort[$k]);
            $data   = $this->Combination($sort,$num-1);
            foreach($data as $row) {
                $result[] = $v.','.$row;
            }
        }
        return $result;
    }

    /**
     * 获取所有的最有组合
     * @param $list_qty_arr 需要排列的数组
     */
    public function permutations($list_qty_arr,$stock_qty)
    {
        $result = array();//存储所有的组合方案
        if(empty($list_qty_arr)) {
            return $result;
        }
        $a = array_keys($list_qty_arr);//需要组合排序的数据的key值;
        $old_current_qty_sum =0.00;
        $new_current_qty_sum=0.00;
        $d = array();//存储所有可能的组合排序
        $r = $stock_qty;//组合排序综合最大不能超过数值
        $returnAssignItemList = [];
        $olditemList =[];

        for($i=1; $i<=count($a); $i++) {
            $all_combination =$this->Combination($a, $i);
          foreach( $all_combination as $v){
                $v = explode(',',(string)$v);
                //取出组合中大于0小于1的所有组合
                //取出所有组合中的拼接箱数
                $newitemList = [];
                $selectedItemindex =[];
                foreach ($v as $vv){
                    $newitemList[] = $list_qty_arr[$vv]['new_customer_buying_quantity'];
                    $selectedItemindex[]=$list_qty_arr[$vv];
                }

                $new_current_qty_sum=array_sum($newitemList);
                if($new_current_qty_sum<=$r && $new_current_qty_sum>$old_current_qty_sum){
                    $old_current_qty_sum= $new_current_qty_sum;
                    $olditemList =$newitemList;
                    $returnAssignItemList=$selectedItemindex;
                }



            }
        }

      if($returnAssignItemList) {
          return $returnAssignItemList;
          /*foreach ($returnAssignItemList as $key=>$value) {
              var_dump($key.' '.$value['id'].' '.$value['new_customer_buying_quantity'].'<br>');
              } */

      }else{
          return 0;
      }


    }


}

?>