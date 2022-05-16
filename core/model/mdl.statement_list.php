<?php

class mdl_statement_list extends mdl_base
{




    /**
     * 基于订单动作 记录
     *
     * 功能完成后可以 存储statement 数据
     */
    protected $tableName = '#@_statement_list';



 /* 获得当前查询条件的 statement list */
    public function getStatementList($factoryId,$customer_id,$startTime =0 ,$endTime=0, $search = null) {

     $sql = "select s.* ,u.phone,u.email from cc_statement_list s left join cc_user u on s.customer_id =u.id where factory_id = $factoryId";
        //var_dump ($sql);exit;

      if($customer_id){
          $sql .= " and customer_id =$customer_id";
      }

        if($search) {
            $sql .= " AND (s.id ='%$search%'
                     OR s.customer_id like '%$search%'
                     OR s.customer_business_name like '%$search%'
					  OR s.customer_legal_name like '%$search%'
					 )";
        }
        if($startTime) {

            $startTime = strtotime($startTime." 00:00:00");
            $sql .= " and gen_date >=$startTime ";

        }

        if($endTime) {

            $endTime = strtotime($endTime." 23:59:59");

            $sql .= " and gen_date <= $endTime ";

        }

        $sql .= " order by id desc ";

   // var_dump($sql);exit;
        return $this->getListBySql($sql);
    }

   /*获得需要处理statement的客户列表*/

   public function getNeedToProcessStatementCustomerList($factory_id){
       $sql ="select s.customer_id from  cc_statement s  where s.factory_id = $factory_id and (process_status =0 or process_status=-1)  group by customer_id order by customer_id" ;
       $list = $this->getListBySql($sql);
      // var_dump($list); exit;
       return $list;
   }

   public function  getCustomerOpeningBalance($factoryId,$customerId){
     //必须为正式的statement ，临时的statement 不包括。
       $sql ="SELECT * FROM `cc_statement_list` WHERE  factory_id=$factoryId and customer_id=$customerId and statementType =1 order by id desc ";
       $list =$this->getListBySql($sql);
       if($list){
           $openBalance = $list[0]['close_balance_amount'];
       }else{
           $openBalance = 0.00;
       }
       return $openBalance;

  }





   /* 获得当前供应商，当前客户的当前 年度星期 是否已经生成你了statement  */
   public function  getIfcurrentYearWeekIsProcessForCustomer($factoryId,$customer_id,$yearWeek){
       $where =array (
           'factory_id'=>$factoryId,
           'customer_id'=>$customer_id,
           'yearweek'=>$yearWeek
          );
       $rec = $this->getByWhere($where);
       if($rec) {return $rec['id'];}
       return 0;

   }

   public function deleteCurrentYearWeekIsProcessForCustomer($factoryId,$customer_id,$yearWeek) {
       $where =array (
           'factory_id'=>$factoryId,
           'customer_id'=>$customer_id,
           'yearweek'=>$yearWeek
       );
       $this->deleteByWhere($where);
       return 1;
   }


}

?>