<?php

class mdl_order extends mdl_base
{
    protected $tableName = '#@_order';

   
 public function getListofAvailableDates($current_user_id) {
	 
	   
  	    $sql ='SELECT DISTINCT logistic_delivery_date from cc_order where logistic_delivery_date >'.(time()-3600*24*7). ' and ';
		$sql .= ' (business_userId ='.$current_user_id;
		
		$sql .= ' or  business_userId in (select business_id from cc_dispatching_centre_customer_list where dispatching_centre_id='.$current_user_id.')'  ;
		$sql .= ' or  business_userId in (select customer_id from cc_factory2c_list where factroy_id='.$current_user_id.') ';
		$sql .= ' or  business_userId in (select customer_id from cc_factory_2blist where factroy_id='.$current_user_id.')  ';
		$sql .= ')';
	  
		$availableDates = loadModel('order')->getListBySql($sql);
    	$availableDates = array_map(function($d){
    		return date('Y-m-d',$d['logistic_delivery_date']);
    	}, $availableDates);
		
		return $availableDates;
 }

public function getPostCodeGroupAndCountOfOrder($factory_id,$logistic_delivery_date) {

     $sql ="SELECT `postalcode`,`city`,count(*) as count 
            FROM `cc_order` WHERE DATE_FORMAT(from_unixtime(logistic_delivery_date),'%Y-%m-%d')='$logistic_delivery_date'  and business_userId =$factory_id 
            group by postalcode 
            order by postalcode ";
    // var_dump($sql);exit;
     return $this->getListBySql($sql);
}

 public function check_if_order_belong_to_login_user($loginUserId,$orderid) {
	 
	 $sql ="select id  from cc_order  where id=".$orderid. " and business_userId=".$loginUserId;
	
	 if($this->getListBySql($sql)){
		 return  1;
	 }else{
		 return  0;
	 }
	 
	 
 }


 // date('Y-m-d',$d['logistic_delivery_date']

    public function generateLogisticSequence($business_id,$dateTimestamp)
    {



       // $dateStr =date('Y-m-d',$order_rec['logistic_delivery_date'];



        $orders = $this->getOrderOnDeliverDate($dateTimestamp,$business_id); //all today's order

        $max_logistic_number  = 0;

        foreach ($orders as $order) {
            if ($order['logistic_sequence_No'] > $max_logistic_number)
                $max_logistic_number = $order['logistic_sequence_No'];
        }
        //????????????????????? seq_number ;




        foreach ($orders as $order) {
            //?????????????????????????????? ?????????????????????????????? ??????????????????
            //$logistic_suppliers_info=$mdl_order->gen_logistic_suppliers_info( $order['orderId'],$this->lang);

            $data = [
                //'logistic_suppliers_info'=>$logistic_suppliers_info['logistic_suppliers_info'],
                //'logistic_suppliers_count'=>$logistic_suppliers_info['logistic_suppliers_count']
            ];

            if ($order['logistic_sequence_No'] < 1) {
                $max_logistic_number +=1;
                $data ['logistic_sequence_No'] =$max_logistic_number;

            }


                $this->updateByWhere(
                    $data,
                    ['orderId' => $order['orderId']]			);


        }

        return ($max_logistic_number+1);

    }




    public function getOrderOnDeliverDate($timestamp,$business_id)
    {


        if ($timestamp === false) {
            throw new Exception("dateStr is not recognized", 1);
        }
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);
        $dateTime->setTime(0,0,0);
        $timestamp = $dateTime->getTimestamp();


        $mdl_order = loadModel('order');
        $current_user_id =$business_id;



        $sql ="select f.nickname ,cc_order.* from cc_order left join cc_user_factory f on cc_order.userId =f.user_id and cc_order.business_userId = f.factory_id where logistic_delivery_date =$timestamp   ";

        $sql .= " and ( business_userId =$business_id  ";
        $sql .= " or business_userId  in ( select cc_logistic_customers_id from cc_freshfood_logistic_customers where cc_logistic_business_id = $current_user_id) ";
        $sql .="  or business_userId in  (select customer_id from cc_factory2c_list where factroy_id =$business_id )  ";
        $sql .="  or business_userId in  (select customer_id from cc_factory_2blist where factroy_id =$business_id )  ";
        $sql .=" )";
        //var_dump($sql);exit;





        $ubonusOrderList =$mdl_order->getListBySql($sql);



        foreach ($ubonusOrderList as $key => $value) {

            $ubonusOrderList[$key]['data_source'] = '1'; //data from ubonus
        }

        return $ubonusOrderList;

    }

   public function  get_first_order_sameuserId_sameday ($orderId){
	   
 	   $curr_rec =$this->get($orderId);
	   $sql ="select DISTINCT id,orderId,money,money_new,sent_to_xero from cc_order where userID =".$curr_rec['userId']." 
	   and business_userId =".$curr_rec['business_userId']." and id !=$orderId and coupon_status='c01' and logistic_delivery_date=".$curr_rec['logistic_delivery_date']." order by id ";
			
	   $rec =$this->getListBySql($sql);
	   $first_rec=$rec[0];
	  

	   return $first_rec;
	   
		
	   
	   
   }


    // ????????????orderid?????????????????????order 
	public function merge_order ($merge_id,$original_order) {
		

			// ?????????????????????????????????????????????????????????
			
			$merge_order  = $this->get($merge_id);
			
			
			$data_order_1=array(
			 'merge_to_another_order'=>1,                   // ??????????????????????????????????????????????????????
			 'merge_order_id'=>$original_order['orderId'],          //??????????????????
			 'coupon_status'=>'d01' ,                     //?????????????????????????????????????????????
			 'cn_coupon_status_name'=>'????????????',
			 'en_coupon_status_name'=>'order merged'
			);
			
			
				
			
			//??????????????????????????????????????????????????????????????????????????? 
			$data_order_2=array(
			 'money'=>$merge_order['money']+$original_order['money'], //????????? ?????????????????????????????????????????????
			 'money_new'=>$merge_order['money_new']+$original_order['money_new'],
			);
			
			
			$data_customer_coupon =array(
			  'order_id'=>$original_order['orderId'],           // ?????????????????????????????????????????? ????????? ???????????????????????????????????????
			  'old_order_id'=>$merge_order['orderId'],           // ??????????????????????????????????????????
			  'redeem_code'=>$original_order['redeem_code']
			);
			
			
			//??????????????????????????????????????????????????? 
			$data_active_log =array(
			  'action_id'=>'d01',
			  'merge_to_order_id'=>$original_order['orderId'] // ????????????
			);
			
			
			
			
			$this->begin();
			
			if(!$this->update($data_order_1,$merge_id) ) {  //?????????????????????????????????????????????????????????
				
				$error ='error happen when update orderstatusdata_order_1 ';
				
			}  
			
			if(!$this->update($data_order_2,$original_order['id']) ) {  //?????????????????????????????????????????????????????????
				
				$error ='error happen when update orderstatus data_order_2 ';
				
			}  
			
			if(!loadModel('wj_customer_coupon')->updateByWhere($data_customer_coupon,array('order_id'=>$merge_order['orderId'])) ) {  
				
				$error ='error happen when update wj_customer_coupon ';
				
			}  
			
			if(!loadModel('wj_user_coupon_activity_log')->updateByWhere($data_active_log,array('orderId'=>$merge_order['orderId'])) ) {  
				
				$error ='error happen when update wj_user_coupon_activity_log ';
				
			}  
			
			if($error) {
				
				$this->rollback();
				return $error;
				
			}else{
				
				$this->commit();
				return 0;
			}
			
			
			
		
		
	}

public function getdriversheetList($factoryId,$dateOfDelivery,$logistic_schedule_id){

     $sql ="SELECT o.xero_invoice_id,f.nickname,o.edit_boxesNumber,o.boxesNumber,o.address,o.logistic_stop_No,o.logistic_sequence_No ,o.logistic_delivery_date	,
       o.phone,'' as boxes,o.message_to_business,' ' as signed  from cc_order as o
                   left join cc_user_factory f on o.userId=f.user_id and o.business_userId = f.factory_id 
        where  o.business_userId =$factoryId and  (o.coupon_status='c01' or o.coupon_status='b01' )
          and (o.status =1 or o.accountPay=1) 
          and DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$dateOfDelivery' 
          and logistic_schedule_id = '$logistic_schedule_id' 
        order by logistic_stop_No,f.nickname  ";
     $list = $this->getListBySql($sql);
    // var_dump($sql);exit;
     return $list;
}

public function get_manual_producing_data($dateSearcch,$factoryId,$logistic_schedule_id){
     $sql ="SELECT o.orderId,if(length(u.contactPersonFirstname)>0,if(length(u.contactPersonLastname)>0,
    concat(u.contactPersonFirstname,'-',u.contactPersonLastname),u.contactPersonFirstname) ,u.name) as DriverName ,
       concat(t.plate_number,'-',t.truck_name) as truckName, if(length(f.xero_account_number)>0,
           f.xero_account_number,f.user_id) as accountNumber, if(length(f.nickname)>0,f.nickname,o.displayName) as CustomerCode,
       concat(o.first_name,' ',o.last_name) as ContactName,o.logistic_truck_No as TruckNo,
       o.logistic_sequence_No as SeqNo,o.logistic_stop_No as StopNo,o.message_to_business as Message ,
       o.city,o.postalcode ,details.* 
    from cc_order as o  left join cc_user_factory f on o.userId =f.user_id and o.business_userId =f.factory_id 
        left join cc_truck t on t.business_id =o.business_userId and t.truck_no =o.logistic_truck_No
        left join cc_user u on t.current_driver =u.id 
        left join ( select c.order_id ,m.menu_id as ItemCode,upper(if(length(spec.menu_en_name)>0,spec.menu_en_name,'')) as specName ,if(length(m.menu_code)>0,m.menu_code,if(length(m.menu_en_name)>0,m.menu_en_name,c.bonus_title ))  as ItemName ,
                    c.customer_buying_quantity as Quantity, upper(if(length(m.unit_en)>0,m.unit_en,m.unit)) as Unit,
                    c.message as ItemMessage
                    
                    from cc_wj_customer_coupon c
                    left join cc_restaurant_menu m  on c.restaurant_menu_id =m.id 
                    left join cc_restaurant_menu_option spec on c.guige1_id =spec.id) details on o.orderId =details.order_id 
    where  business_userId= $factoryId  and DATE_FORMAT(from_unixtime(o.logistic_delivery_date),'%Y-%m-%d') = '$dateSearcch' 
      and (o.coupon_status='c01' or o.coupon_status ='b01') 
      and (o.status =1 or o.accountPay=1) " ;
      
      if ($logistic_schedule_id !='all') {
        $sql .=  " and o.logistic_schedule_id=$logistic_schedule_id ";
      }
      $sql .= "  order by o.logistic_schedule_id,o.city";
     $data =$this->getListBySql($sql);
//var_dump($sql);exit;
   /* if($data){

        foreach ($data as $key=>$value){

        $orderid=$value['orderId'];
            $sql1 ="select m.menu_id as ItemCode,upper(if(length(spec.menu_en_name)>0,spec.menu_en_name,'')) as specName ,if(length(m.menu_en_name)>0,m.menu_en_name,c.bonus_title ) as ItemName ,
                    c.customer_buying_quantity as Quantity, upper(if(length(m.unit_en)>0,m.unit_en,m.unit)) as Unit,
                    c.message as ItemMessage
                    
                    from cc_wj_customer_coupon c
                    left join cc_restaurant_menu m  on c.restaurant_menu_id =m.id 
                    left join cc_restaurant_menu_option spec on c.guige1_id =spec.id
                    
                    where order_id =$orderid";
          }

    }else{

    }
   */
     return $data;

}

   public function getByOrderId($orderid)
    {
        $where = "orderId='$orderid'";

        if ($this->lang) {
            $where .= " and lang='".$this->getLang()."'";
        }

        return $this->db->selectOne(null, $this->tableName, $where);
    }

    public function gen_logistic_suppliers_info($orderId, $lang)
    {

        if (! $orderId) {
            return false;
        }

        // ????????????????????????????????????
        $sql = "select DISTINCT 	business_id,name  from cc_wj_customer_coupon a ,cc_user b  where a.business_id=b.id and order_id=".$orderId;
        $rec_businame = loadModel('wj_customer_coupon')->getListBySql($sql);

        // ????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????
        if ($rec_businame) {
            $businessUserId = $rec_businame[0]['business_id'];
            $sql = " select suppliers_id,suppliers_name  from cc_freshfood_disp_centre_suppliers where business_id =  (select DISTINCT business_id from cc_freshfood_disp_centre_suppliers where suppliers_id =$businessUserId)";
            $busienssList = loadModel('freshfood_disp_centre_suppliers')->getListBySql($sql);
            //	var_dump($busienssList);exit;
        }

        $countofsuppliers = sizeof($rec_businame);
        if ($countofsuppliers >= 1) {

            foreach ($busienssList as $key1 => $listBusiness) {

                if ($key1 == 0) {
                    $suppliers_info .= $listBusiness['suppliers_name'];;
                } else {
                    $suppliers_info .= '___|'.$listBusiness['suppliers_name'];;
                }

                $businessIn = 0; //?????????0
                foreach ($rec_businame as $key => $PurchaseBusiness) {
                    if ($PurchaseBusiness['business_id'] == $listBusiness['suppliers_id']) {
                        $businessIn = 1;
                    }
                }
                if ($businessIn) {
                    $suppliers_info .= '(Y)';
                } else {
                    $suppliers_info .= '(N)';
                }
            }
        } else {

            $suppliers_info = 'Total 1 suppliers('.$rec_businame[0]['name'].")";
        }

        $suppliers_arr['logistic_suppliers_info'] = $suppliers_info;
        $suppliers_arr['logistic_suppliers_count'] = $countofsuppliers;

        return $suppliers_arr;
    }

    public function generateOrderName($orderId, $lang)
    {

        if (! $orderId) {
            return false;
        }

        // ????????????????????????????????????
        $sql = "select DISTINCT name  from cc_wj_customer_coupon a ,cc_user b  where a.business_id=b.id and order_id=".$orderId;
        $rec_businame = loadModel('wj_customer_coupon')->getListBySql($sql);
        //var_dump($rec_businame);exit;
        $countofsuppliers = sizeof($rec_businame);
        if ($countofsuppliers > 1) {
            foreach ($rec_businame as $key => $value) {
                if ($key == 0) {

                    $ordername .= $value['name'];
                } else {
                    $ordername .= '/'.$value['name'];
                }
            }
            $ordername = 'Total '.$countofsuppliers.' suppliers('.$ordername.")";
        }

        //var_dump($ordername);exit;

        $items = loadModel('wj_customer_coupon')->getList(['bonus_title', 'business_name'], ['order_id' => $orderId]);

        $count = sizeof($items);
        if ($count == 0) {
            return false;
        } elseif ($count == 1) {
            return $items[0]['bonus_title'];
        } else {
            if ($countofsuppliers > 1) {
                $name = $ordername.$lang->together_count."$count";
            } else {
                $name = $items[0]['business_name'].$lang->together_count."$count";
            }

            return $name;
        }
    }



    public function getMoneyDetail1($orderId,$business_id)
    {
        $order = $this->getByOrderId($orderId);

        $data = [];

        $data['useMoney'] = $order['confirmedMoneyAppliedAmount'];
        $data['transactionBalance'] = $order['money'];
        $data['transactionBalance_new'] = $order['money_new'];
        $data['deliveryFee'] = $order['delivery_fees'];
        $data['platformFee'] = $order['booking_fees'];
        $data['promotionTotal'] = $order['promotion_total'];
        $data['transactionSurcharge'] = $order['surcharge'];

      //  $data['goodsTotal_new'] = $order['money_new'] + $order['confirmedMoneyAppliedAmount'] - ($order['delivery_fees'] + $order['booking_fees'] + $order['surcharge_new'] - $order['promotion_total']);
        $data['goodsTotal'] = $order['money'] + $order['confirmedMoneyAppliedAmount'] - ($order['delivery_fees'] + $order['booking_fees'] + $order['surcharge'] - $order['promotion_total']);


        $itemDetails =loadModel('wj_customer_coupon')->getItemsInOrder($orderId,$business_id);

        if($itemDetails) {
            $goodsTotal_new =0;
            foreach ($itemDetails as $key =>$value) {
                $goodsTotal_new +=$value['voucher_deal_amount'] *$value['customer_buying_quantity'];

            }
            $data['goodsTotal_new'] =$goodsTotal_new;
            $data['goodsTotal'] =$goodsTotal_new;
        }



        return $data;
    }

    function  de_order($orderId){



        $where =array('orderId'=>$orderId);

        $order_rec =$this->getByWhere($where);
        if($order_rec['userId']!=321249) {
            var_dump('faak no action ');
        }
        $this->deleteByWhere($where);

        $mdl =loadModel('wj_customer_coupon');
        $where1 =array('order_id'=>$orderId);
        $mdl->deleteByWhere($where1);

        $mdl =loadModel('recharge');
        $mdl->deleteByWhere($where);

        $mdl =loadModel('wj_user_coupon_activity_log');
        $mdl->deleteByWhere($where);



    }

    public function getMoneyDetail($orderId)
    {
        $order = $this->getByOrderId($orderId);

        $data = [];

        $data['useMoney'] = $order['confirmedMoneyAppliedAmount'];
        $data['transactionBalance'] = $order['money'];
        $data['transactionBalance_new'] = $order['money_new'];
       $data['deliveryFee'] = $order['delivery_fees'];
        $data['platformFee'] = $order['booking_fees'];
        $data['promotionTotal'] = $order['promotion_total'];
        $data['transactionSurcharge'] = $order['surcharge'];

        $data['goodsTotal_new'] = $order['money_new'] + $order['confirmedMoneyAppliedAmount'] - ($order['delivery_fees'] + $order['booking_fees'] + $order['surcharge_new'] - $order['promotion_total']);
        $data['goodsTotal'] = $order['money'] + $order['confirmedMoneyAppliedAmount'] - ($order['delivery_fees'] + $order['booking_fees'] + $order['surcharge'] - $order['promotion_total']);

        return $data;
    }

    function getOrderListOfCustomer($customer_id, $business_id = null)
    {
        $where['userId'] = $customer_id;
        if ($business_id) {
            $where['business_userId'] = $business_id;
        }
        $result = $this->getList(['orderId', 'createTime'], $where, 'createTime desc');

        return $result;
    }

    // ????????????xx???????????????
    public function getTotalSales($userid, $days)
    {
        $daystoseconds = $days * 24 * 60 * 60;
        $sql = "SELECT sum(`money`)
		FROM `#@_order`
		where (business_userId =".$userid." or business_staff_id = ".$userid.")  and (createTime > (UNIX_TIMESTAMP() - ".$daystoseconds."))  ";

        //var_dump($sql);exit;
        $sum_money = $this->getListBySql($sql);

        return $sum_money[0][0];
    }

    public function getLastOrderPaymentMethod($userId)
    {
        $sql = "select payment from cc_order where userId =$userId order by createTime desc limit 1";
        $result = $this->getListBySql($sql);
        $payment = reset(reset($result));

        return $payment;
    }

    public function isOrderEditable($orderId)
    {
        $order = $this->getByWhere(['orderId' => $orderId]);

        $orderDateMustAfter = '2017-07-27';// ??????????????????????????????????????????????????????????????????????????????????????????
        $lastSettlementPoint = loadModel('settlement_log')->owner($order['business_userId'])->lastSettlementPoint();
        $lastSettlementTimeStamp = strtotime(($lastSettlementPoint) ? $lastSettlementPoint['settle_to'] : $orderDateMustAfter);

        $orderLogs = loadModel('wj_user_coupon_activity_log')->orderId($orderId)->getLogList();
        foreach ($orderLogs as $orderLog) {
            if ($orderLog['action_id'] == 'b01' && $lastSettlementTimeStamp > $orderLog['gen_date']) {
                return false;
            }
        }
        return true;
    }

    public function  getCustomerSupplierList($userid){
      $sql ="select user.id, user.displayName ,user.businessName from cc_order o  
            left join  cc_user user on o.business_userId =user.id where o.userId =$userid 
            group by user.id";
      $list =  $this->getListBySql($sql);

        foreach ($list as $key =>$value) {
            if($value['businessName']) {
                if($value['displayName']) {
                    $list[$key]['name'] =$value['businessName'].'('.$value['displayName'].')';
                }else{
                    $list[$key]['name'] =$value['businessName'];
                }
            }else{
                $list[$key]['name'] =$value['displayName'];
            }

      }
        return $list;
    }
}

?>