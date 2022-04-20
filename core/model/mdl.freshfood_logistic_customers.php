<?php

class mdl_freshfood_logistic_customers extends mdl_base
{

	protected $tableName = '#@_freshfood_logistic_customers';


  //根据给定的用户编号，确认该用户在物流管理pannel上显示的可用配送日期列表
   public function getAvaliableDateOfThisLogisiticCompany($businessId) {
		
       //获取cc_order可以配送的日期
	   $sql_cc_order_avaliabe_date ='SELECT DISTINCT logistic_delivery_date  from cc_order where logistic_delivery_date >'.(time()-3600*24*30). ' and ( business_userId = '.$businessId.' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$businessId.'))';
       $sql_cc_order_import_avaliabe_date ='SELECT DISTINCT logistic_delivery_date  from cc_order_import where logistic_delivery_date >'.(time()-3600*24*30). ' and ( business_userId = '.$businessId.' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$businessId.'))';
     
	   $sql_union = 'select DISTINCT  logistic_delivery_date from (select * from( ('. $sql_cc_order_avaliabe_date.') union ('.$sql_cc_order_import_avaliabe_date.')) as d ) as c';
	   // var_dump($sql_union);exit;
		$dateOptions =loadModel('freshfood_logistic_customers')->getListBySql($sql_union);
		//var_dump($dateOptions);exit;
		return $dateOptions; //为本地数据源 或没有查找到该商家，视为本地数据源
    }
	
	//根据给定的用户编号，确认该用户在物流管理pannel上显示的可用配送日期列表
   public function getDriversOfAvaliableDateOfThisLogisiticCompany($businessId,$delivery_date) {
		
       //获取cc_order可以配送的日期
	   $delivery_day =strtotime($delivery_date);
	   
	   if ($delivery_day) {
		     $sql_cc_order_avaliabe_drivers_date ='SELECT DISTINCT logistic_truck_No  from cc_order where logistic_delivery_date ='. $delivery_day .' and ( business_userId = '.$businessId.' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$businessId.'))';
     	   }else{
			   
		     $sql_cc_order_avaliabe_drivers_date ='SELECT DISTINCT logistic_truck_No  from cc_order where logistic_delivery_date  >'.(time()-3600*24*7). ' and ( business_userId = '.$businessId.' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$businessId.'))';
       }
	  //var_dump($sql_cc_order_avaliabe_drivers_date);exit;
	   
	   if ($delivery_day) {
		     $sql_cc_order_import_avaliabe_drivers_date ='SELECT DISTINCT logistic_truck_No  from cc_order_import where logistic_delivery_date ='. $delivery_day .' and ( business_userId = '.$businessId.' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$businessId.'))';
     	   }else{
			   
		     $sql_cc_order_import_avaliabe_drivers_date ='SELECT DISTINCT logistic_truck_No  from cc_order_import where logistic_delivery_date  >'.(time()-3600*24*7). ' and ( business_userId = '.$businessId.' or business_userId in (select cc_logistic_customers_id  from cc_freshfood_logistic_customers where cc_logistic_business_id ='.$businessId.'))';
       }
	   
	   
	   
	   $sql_union = 'select DISTINCT  logistic_truck_No from (select * from( ('. $sql_cc_order_avaliabe_drivers_date.') union ('.$sql_cc_order_import_avaliabe_drivers_date.')) as d ) as c';
	    //var_dump($sql_union);exit;
		$dateOptions =loadModel('freshfood_logistic_customers')->getListBySql($sql_union);
		//var_dump($dateOptions);exit;
		return $dateOptions; //为本地数据源 或没有查找到该商家，视为本地数据源
    }
}

?>