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
    public function getStatementList($factoryId, $search = null) {

     $sql = "select * from cc_statement_list s where factory_id = $factoryId";
        //var_dump ($sql);exit;


        if($search) {
            $sql .= " AND (s.id ='%$search%'
                     OR s.customer_id like '%$search%'
                     OR s.customer_business_name like '%$search%'
					  OR s.customer_legal_name like '%$search%'
					 )";
        }
   // var_dump($sql);exit;
        return $this->getListBySql($sql);
    }

   /*获得需要处理statement的客户列表*/

   public function getNeedToProcessStatementCustomerList($factory_id){
       $sql ="select s.customer_id from  cc_statement s  where s.factory_id = $factory_id and is_settled =0  group by customer_id order by customer_id" ;
       $list = $this->getListBySql($sql);
      // var_dump($sql); exit;
       return $list;
   }


   /* 获得当前供应商，当前客户的当前 年度星期 是否已经生成你了statement  */
   public function  getIfcurrentYearWeekIsProcessForCustomer($factoryId,$customer_id,$yearWeek){
       $where =array (
           'factory_id'=>$factoryId,
           'customer_id'=>$customer_id,
           'yearweek'=>$yearWeek
          );
       $rec = $this->getByWhere($where);
       if($rec) {return 1;}
       return 0;

   }


}

?>