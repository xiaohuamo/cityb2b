<?php

class mdl_statement extends mdl_base
{



    /**
     * 基于订单动作 记录
     *
     * 功能完成后可以 存储statement 数据
     */
    protected $tableName = '#@_statement';


    // get statement details for certain statement
    public function  getStatementDetails($statement_ids){

        $sql ="select s.* ,c.code_desc_en from cc_statement s left join cc_statement_code c on c.code =s.type_code  where  s.id in ( $statement_ids)";
        $statement_details = $this->getListBySql($sql);
        return $statement_details;
       // var_dump($statement_details); exit;
    }


    public function  getStatementDetailsById($id,$custom){

        if(!$custom) {

            $sql ="select s.* ,c.code_desc_en from cc_statement s left join cc_statement_code c on c.code =s.type_code  where  s.type_code !='5001' and s.statement_id =$id order by s.id 
         ";
            $statement_details = $this->getListBySql($sql);
            return $statement_details;
            // var_dump($statement_details); exit;
        }else{
            $rec_ids =loadModel('statement_list')->get($id);
            $ids =$rec_ids['ids'];
            $sql ="select s.* ,c.code_desc_en from cc_statement s left join cc_statement_code c on c.code =s.type_code  where  s.type_code !='5001' and s.id in ($ids)  order by s.id
         ";
            $statement_details = $this->getListBySql($sql);
            //var_dump($statement_details);exit;
            return $statement_details;
            // var_dump($statement_details); exit;
        }


    }

    // get statement details for certain statement
    public function  getStatementDetailsNotOverDue($statement_ids,$statementdate){

        $sql ="select s.* ,c.code_desc_en from cc_statement s left join cc_statement_code c on c.code =s.type_code 
        where  s.id in ( $statement_ids) and s.overdue_date >=$statementdate";
        $statement_details = $this->getListBySql($sql);

       // var_dump($sql); exit;
        return $statement_details;
    }

    // get statement details for certain statement
    public function  getStatementDetailsOverDue($statement_ids,$statementdate){

        $sql ="select s.* ,c.code_desc_en from cc_statement s left join cc_statement_code c on c.code =s.type_code  where  s.id in ( $statement_ids) and $statementdate > s.overdue_date";
        $statement_details = $this->getListBySql($sql);
        return $statement_details;
        // var_dump($statement_details); exit;
    }



    //生成statement所需数据
    public function getStatementTempData($factoryId,$customer_id,$login_user,$openBalance,$closeBalance,$startTime,$endTime) {
        // 获得notoverdue date 的汇总
        $current_time =time();

        // 获得当前未过期的欠款总额 （ 付款时间是当前时间之后，且
        $sql ="select ifnull(sum(debit_amount),0.00) as sum_debit
                from cc_statement
                where factory_id =$factoryId and customer_id=$customer_id  and is_settled =0 and  overdue_date>$current_time ";

        $sql_data = "select *
                from cc_statement
                where factory_id =$factoryId and customer_id=$customer_id and type_code !='5001'   ";

        if($startTime) {

            $startTime = strtotime($startTime." 00:00:00");
            $sql .= " and gen_date >=$startTime ";
            $sql_data .= " and gen_date >=$startTime ";
        }

        if($endTime) {

            $endTime = strtotime($endTime." 23:59:59");

            $sql .= " and gen_date <= $endTime ";
            $sql_data .= " and gen_date <= $endTime order by id ";
        }

        $not_overdue_sum_rec  = $this->getListBySql($sql);

        $statements_rec = $this->getListBySql($sql_data);
      // var_dump($statements_rec);exit;

        foreach ($statements_rec as $key=>$value){
            if($key==0){
                $ids =$value['id'];
            }else{
                $ids .=','. $value['id'];
            }

        }
        //var_dump($ids);exit;

        if($not_overdue_sum_rec){
            $total_not_overdue_amount =   $not_overdue_sum_rec[0]['sum_debit'];
        }else{
            $total_not_overdue_amount =  0.00;
        }

        $total_overdue_amount =$closeBalance-$total_not_overdue_amount;

        if($total_overdue_amount<0) {
            $total_not_overdue_amount =$closeBalance;
            $total_overdue_amount=0.00;
        }

        // var_dump($total_overdue_amount);exit;




        $factoryrec =loadModel('user')->get($factoryId);
        $factoryabnRec =loadModel('wj_abn_application')->getByWhere(array('userId'=>$factoryId));
        $customerrec=loadModel('user')->get($customer_id);
        $customerabnRec =loadModel('wj_abn_application')->getByWhere(array('userId'=>$customer_id));



        // var_dump($statementIds);exit;
        if ($factoryrec['tel'] ) {
            $phone =$factoryrec['tel'];
            if($factoryrec['phone']) {
                $phone .= ' (' .$factoryrec['phone'].')';
            }
        }else{
            $phone =$factoryrec['phone'];
        }

        $data =array();

        $data['factory_id']=$factoryId;
        $data['customer_id']=$customer_id;
        $data['gen_date']=time();
        $data['create_user']=$login_user;
        $data['statementPDFpath']='0';
        $data['not_due_amount']=$total_not_overdue_amount;
        $data['overdue_amount']=$total_overdue_amount;
        $data['factory_name']=$factoryabnRec['untity_name'];
        $data['factory_ABN']=$factoryabnRec['ABNorACN'];
        $data['factory_mail_address']=$factoryrec['googleMap'];
        $data['factory_phone']=$phone;
        $data['factory_email']=$factoryrec['email'];
        $data['customer_business_name']=$customerabnRec['business_name'];
        $data['customer_contact_name']=$customerrec['nickname'];
        $data['customer_address']=$customerrec['googleMap'];
        $data['customer_legal_name']=$customerabnRec['untity_name'];
        $data['open_balance_amount']=$openBalance;
        $data['close_balance_amount']=$closeBalance;
        $data['statementType']=2;
        $data['ids']=$ids;

        if($startTime){
            $data['startTime']=$startTime ;
        }

        if($endTime){
            $data['endTime']=$endTime ;
        }



        return $data;

    }


    public function getStatementCustomerList($factory_id,$customerType){
        $sql ="select s.customer_id from  cc_statement s left join cc_user_factory u on s.factory_id =u.factory_id and s.customer_id =u.user_id  where s.factory_id = $factory_id ";

        if($customerType=='custom') {
          $sql .= "and u.custom_statement =1 ";

            }

        $sql .= " group by customer_id order by customer_id" ;
        $list = $this->getListBySql($sql);
       //  var_dump($customerType); exit;
        return $list;
    }


    //生成statement所需数据
    public function getStatementData($factoryId,$customer_id,$login_user,$openBalance,$closeBalance) {
       // 获得notoverdue date 的汇总
        $current_time =time();

        // 获得当前未过期的欠款总额 （ 付款时间是当前时间之后，且
        $sql ="select ifnull(sum(debit_amount),0.00) as sum_debit
                from cc_statement
                where factory_id =$factoryId and customer_id=$customer_id  and is_settled =0 and  overdue_date>$current_time";
//var_dump($sql);
        $not_overdue_sum_rec  = $this->getListBySql($sql);

        if($not_overdue_sum_rec){
            $total_not_overdue_amount =   $not_overdue_sum_rec[0]['sum_debit'];
        }else{
            $total_not_overdue_amount =  0.00;
        }

        $total_overdue_amount =$closeBalance-$total_not_overdue_amount;

        if($total_overdue_amount<0) {
            $total_not_overdue_amount =$closeBalance;
            $total_overdue_amount=0.00;
        }
     /*    if($customer_id ==331339343) {
             var_dump('toal is '.$total_overdue_amount);
             var_dump(' not over due amount  is '.$total_not_overdue_amount);
             var_dump('close balance is :' .$closeBalance);
             var_dump($not_overdue_sum_rec);


         } */
      // var_dump($total_overdue_amount);exit;




        $factoryrec =loadModel('user')->get($factoryId);
        $factoryabnRec =loadModel('wj_abn_application')->getByWhere(array('userId'=>$factoryId));
        $customerrec=loadModel('user')->get($customer_id);
        $customerabnRec =loadModel('wj_abn_application')->getByWhere(array('userId'=>$customer_id));
        if(!$customerabnRec) {
            $customer_business_name = $customerabnRec['business_name'];
            $customer_legel_name = $customerabnRec['untity_name'];
        }else{
            $customer_rec =loadModel('user')->get($customer_id);
            $customer_business_name = $customer_rec['displayName'];
            $customer_legel_name = $customer_rec['displayName'];
        }



       // var_dump($statementIds);exit;
       if ($factoryrec['tel'] ) {
           $phone =$factoryrec['tel'];
           if($factoryrec['phone']) {
               $phone .= ' (' .$factoryrec['phone'].')';
           }
       }else{
           $phone =$factoryrec['phone'];
       }

        $data =array();

        $data['factory_id']=$factoryId;
        $data['customer_id']=$customer_id;
        $data['gen_date']=time();
        $data['create_user']=$login_user;
        $data['statementPDFpath']='0';
        $data['not_due_amount']=$total_not_overdue_amount;
        $data['overdue_amount']=$total_overdue_amount;
        $data['factory_name']=$factoryabnRec['untity_name'];
        $data['factory_ABN']=$factoryabnRec['ABNorACN'];
        $data['factory_mail_address']=$factoryrec['googleMap'];
        $data['factory_phone']=$phone;
        $data['factory_email']=$factoryrec['email'];
        $data['customer_business_name']=$customer_business_name;
        $data['customer_contact_name']=$customerrec['nickname'];
        $data['customer_address']=$customerrec['googleMap'];
        $data['customer_legal_name']=$customer_legel_name;
        $data['open_balance_amount']=$openBalance;
        $data['close_balance_amount']=$closeBalance;
        $data['statementType']=1;



//var_dump($data);exit;
        return $data;

    }

    public function getFirstUnselltedDateOfCustomer($factoryId,$customer_id) {
        $sql ="select * from cc_statement where factory_id = $factoryId and customer_id =$customer_id and is_settled =0 order by id limit 1 " ;
        $rec = $this->getListBySql($sql);

        if($rec){
           // var_dump( $rec);exit;
            $firstDateofUnsettled = date("Y-m-d",$rec[0]['gen_date']);
            return $firstDateofUnsettled;
        }else{
            return 0 ;
        }

}

    public  function changeStatementData($factory_id,$customer_id,$status){

        $where =array(
            'factory_id'=>$factory_id,
            'customer_id'=>$customer_id,
            'statement_id'=>0
        );
        $data =array(
            'process_status'=>$status
        );

        $this->updateByWhere($data,$where);

   }

    public function  getCustomerCloseingBalanceAndData($factoryId,$customerId){
        //必须为正式的statement ，临时的statement 不包括。




        $sql ="select * from cc_statement where factory_id =$factoryId and customer_id =$customerId and statement_id =0 and process_status =-1 order by id desc limit 1 ";

        $rec =$this->getListBySql($sql);

        if($rec){
            return $rec[0]['balance_due'];
        }else{
            return 0.00;

        }



    }
    public function  getCustomerCloseingBalanceAll($factoryId,$customerId){
        //必须为正式的statement ，临时的statement 不包括。




        $sql ="select * from cc_statement where factory_id =$factoryId and customer_id =$customerId and statement_id =0  order by id desc limit 1 ";

        $rec =$this->getListBySql($sql);
//var_dump($rec);exit;
        if($rec){
            return $rec[0]['balance_due'];
        }else{
            $sql ="select * from cc_statement where factory_id =$factoryId and customer_id =$customerId  order by id desc limit 1";
            $rec1 =$this->getListBySql($sql);
            if($rec1){
                return $rec1[0]['balance_due'];
            }
            return 0.00;

        }



    }



    //检查再statment中是否存在退货记录

    //检查当前的order的退货项是否已经settle

    public function isExistOfReturnRecOnStatement($ref_id){

        $where =array(
            'customer_ref_id'=>$ref_id,
            'type_code'=>2002
        );
        $rec =$this->getByWhere($where);
        if($rec){
            return 1;
        }else{
            return 0;
        }


    }

   // 更新客户credit或退货的statment记录
    public function insertOrUpdateCreditItem($data){
        //检查当前数据是否为新数据

       $where =array(
           'factory_id'=>$data['factory_id'],
           'customer_id'=>$data['customer_id'],
           'type_code'=>$data['type_code'],
           'customer_ref_id'=>$data['customer_ref_id']
       );

    $rec =$this->getByWhere($where);

    if($rec){
      // var_dump($rec);exit;
        $dataUpdate =array(
            'create_user'=>$data['create_user'],
            'gen_date'=>time(),
            'credit_amount'=>$data['credit_amount'],
            'balance_due'=>$data['balance_due'],
         );
       // var_dump($dataUpdate);exit;
        $this->update($dataUpdate,$rec['id']);

    }else{

        $this->insert($data);

    }

    }

    //生成客户付款插入的数据
public function  getCustomerPaymentData($login_user,$factory_user,$payment_amount,$code){

   $balance_amount = $this->getBalanceAmountOfCustomer($factory_user['factory_id'],$factory_user['user_id']);



   if($code) {
       $paycode =$code;
   }else{
       $paycode =2001;
   }

   $statement_code_rec =loadModel('statement_code')->getByWhere(array('code'=>$paycode));
   if($statement_code_rec['isCredit']){
       $creditAmount = $payment_amount;
       $debitAmount =0.00;
       $balance_due = $balance_amount -$payment_amount;
   }else{
       $creditAmount = 0.00;
       $debitAmount =$payment_amount;
       $balance_due = $balance_amount + $payment_amount;
   }

    $data=array();
    $data['create_user'] = $login_user;
    $data['gen_date']=time();
    $data['invoice_number']='0';
    $data['type_code']=$paycode;
    $data['factory_id']=$factory_user['factory_id'];
    $data['customer_id']=$factory_user['user_id'];
    $data['customer_ref_id']='0';
    $data['debit_amount']=$debitAmount;
    $data['credit_amount']=$creditAmount;
    $data['balance_due']=$balance_due;
    $data['is_settled']=0;
    $data['overdue_date']=0;

    return $data;
}

    //生成客户付款插入的数据
    public function  getAdjustCustomerPaymentData($login_user,$factory_user,$payment_amount){

        $data=array();
        $data['create_user'] = $login_user;
        $data['gen_date']=time();
        $data['invoice_number']='0';
        $data['type_code']=2003;
        $data['factory_id']=$factory_user['factory_id'];
        $data['customer_id']=$factory_user['user_id'];
        $data['customer_ref_id']='0';
        $data['debit_amount']=$payment_amount;
        $data['credit_amount']=0;
        $data['is_settled']=0;
        $data['overdue_date']=0;

        return $data;
    }


function getdataofrestCreditOf($login_user,$rest_credit_amount,$factory_user){

    $balance_amount = $this->getBalanceAmountOfCustomer($factory_user['factory_id'],$factory_user['user_id']);
   //如果插入的是清算记录，则不影响 balance_due ,直接写入最后的balance即可

    $data=array();
    $data['create_user'] = $login_user;
    $data['gen_date']=time();
    $data['invoice_number']='0';
    $data['type_code']=5001;
    $data['factory_id']=$factory_user['factory_id'];
    $data['customer_id']=$factory_user['user_id'];
    $data['customer_ref_id']='0';
    $data['debit_amount']=0;
    $data['credit_amount']=$rest_credit_amount;
    $data['balance_due']=$balance_amount;
    $data['is_settled']=0;
    $data['overdue_date']=0;

    return $data;

}

//获得某个客户的交易流水
public function getStatementTranscations($factoryId, $customer_id,$search,$startTime,$endTime){

        $sql = "select s.*,c.code_desc_en  from cc_statement s left join cc_statement_code c  on s.type_code =c.code where s.factory_id =$factoryId and s.customer_id=$customer_id and code !='5001' ";

        if($startTime) {

            $startTime = strtotime($startTime." 00:00:00");
            $sql .= " and s.gen_date >=$startTime ";
        }

        if($endTime) {

            $endTime = strtotime($endTime." 23:59:59");

            $sql .= " and s.gen_date <= $endTime ";
        }


     $sql .= " order by id desc ";
       // var_dump($sql);exit;
        return $this->getListBySql($sql);
}


// get the customer last transcation balance amount ;
public function getBalanceAmountOfCustomer($factoryId,$customer_id){
       $sql ="select * from cc_statement where factory_id =$factoryId and customer_id=$customer_id order by id desc limit 1";
       $rec =$this->getListBySql($sql);
       if($rec){
           $balanceDue =$rec[0]['balance_due'];
       }else{
           $balanceDue =0.00;
       }
       return $balanceDue;

}

//更新付款明细
public function  updatePaymentsDetails($new_id,$payment_amount,$factory_user,$login_user){

    //获得该用户 over_due 明细 ，如果支付金额不足，则顺次修改，如果某一笔不足，则计算余额，生成一笔credit

    /*
    $customerOverDueTotalAmount=$this->getCustomerOverDueTotalAmount($factory_user);


   //判断支付的数额与over
    if($payment_amount =$customerOverDueTotalAmount) {
        //如果金额相同，则将所有的overdue交易clear，
        $this->updateAllOverDueTranscation($factory_user);
    }elseif($payment_amount >$customerOverDueTotalAmount){
        //如果客户付款金额

    }elseif ($payment_amount <$customerOverDueTotalAmount){

    }
   */

    /*
    就是把 这个商家这个客户的所有未支付的交易拿出来，然后 按照交易流水号排序，一个一个减 ，如果还有余额就再减下一个， 如果不够支付下一个的
    那么就 把这个余额写一个 credit  贷到下一个statement 即可。

    处理完成后 将这笔付款clear .


    首先将未settle的credit 做一个汇总， 如果没有credit 未clear 则不用入账。
    如果 有，那么就按照流水号排序 ，一个一个减即可。
    payment invocie

    220
    330
    550
    770

    credit

    20
    210
    1230

    顺序是：

    拿 20 ，减220 ， 如果20大于220，则 220 clear , 否则 220-20 , 剩200 ，20那笔 clear ,移动刀210这笔，组剑法，210大于200 ， 则 剩10刀， 220 clear ,移动
    到 330 ， 10减330 ， 10 clear ,剩余320 ， 移动到下一个1230 ， -320 ， 等与910 ， 320 那笔clear ,一定到770 ， 910-770 140 ,770 clear , shengyu 140 做一笔credit .
    140 即可  中间的 note 或 payid 需要进行记录。
    */
  // var_dump($factory_user);exit;
   $unSettled_credit_Transactions = $this->getAllUnSettledCreditTranscationOfCustomer($factory_user);
   $unpaidTranscation =$this->getAllUnPaidTranscationOfCustomer($factory_user);
   //var_dump($unSettled_credit_Transactions);
   // var_dump($unpaidTranscation);exit;


  //算出总的credit的金额

    $total_left_credit_amount =0.00;
    $total_clear_debit_amount=0.00;
    foreach ($unSettled_credit_Transactions as $key=>$value) {
        $total_left_credit_amount += $value['credit_amount'];
    }

      //依次循环每个需要清算的待付款项目
       foreach ($unpaidTranscation as $key=>$value) {
          $debit_amount = $value['debit_amount'];
          if($total_left_credit_amount >= $debit_amount) {
              // 从总的credit中扣除需要支付的transcation的金额
              $total_left_credit_amount -=$debit_amount;
             //已清算金额
              $total_clear_debit_amount +=$debit_amount;
              // 将这条debit记录置为 clear 已清算  issettled =1
               $this->update(array('is_settled'=>1),$value['id']);

         }else{
              // 退出循环，因没有credit ,不需要再处理其后的需付款项目
              break;
          }
       }

       // 所剩总的借贷金额，这个借贷金额 可能清算上面的 0-全部借贷项目， 比如credit 总额 都不够付第一笔清算，则一笔都清算不了，
    // 如果 清算所有的还有剩余，那么 标记所有的credit 项后，会重新生成一笔 充值记录 ，以表示在清算完成以后，还有credit .
    // 也可能存在，清算到某一条后，剩余资金不足， 那么，也需要将之前所有的credit 项目标记已清算， 所剩的余额形成一条记录
    // 标记为未清算，带入到下一个清算动作；


  if($total_clear_debit_amount>0)  {
      //如果进行了清算
        foreach ($unSettled_credit_Transactions as $key=>$value) {
            $current_credit_amount = $value['credit_amount'];

            if( $total_clear_debit_amount>=$current_credit_amount) {
                //从总的贷款中扣除
                $total_clear_debit_amount -= $value['credit_amount'];
                //标记当前的 credit 交易 为已清算；
                $this->update(array('is_settled'=>1),$value['id']);
            }else{
                // 表示清算剩余amount 只用了部分的该credit项目金额 ，此时也要清算该项目，但会计算剩余的credit 形成一条新的credit .
                $this->update(array('is_settled'=>1),$value['id']);
                $dataOfRestCredit = $this->getdataofrestCreditOf($login_user,$current_credit_amount-$total_clear_debit_amount,$factory_user);
                $this->insert ($dataOfRestCredit);

                break;
            }


        }

    }



}


function getAllUnSettledCreditTranscationOfCustomer($factory_user){

        $factory_id = $factory_user['factory_id'];
        $customer_id =$factory_user['user_id'];

        $sql ="select *  from cc_statement
                    where factory_id =$factory_id and customer_id=$customer_id  and is_settled =0   and credit_amount>0
                     order by id";

        return $this->getListBySql($sql);

}

    function getAllUnPaidTranscationOfCustomer($factory_user){

        $factory_id = $factory_user['factory_id'];
        $customer_id =$factory_user['user_id'];

        $sql ="select *  from cc_statement
                where factory_id =$factory_id and customer_id=$customer_id  and is_settled =0   and debit_amount>0 order by id ";

        return $this->getListBySql($sql);

    }


}

?>