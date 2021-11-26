 <?php
class mdl_dispatching_centre_customer_list extends mdl_base
{
	protected $tableName = '#@_dispatching_centre_customer_list';

	public function getDispatchingCentreCustomerList($centreId) {
        return $this->getList('*', ['dispatching_centre_id' => $centreId], 'sort');
    }
	public function getIfBusinessIsExportDataSource($businessId) {
		
       $where = [];

	
		$where[] = 'business_id = '.$businessId;
		$column = ['data_source'];

		$dateList = loadModel('dispatching_centre_customer_list')->getList($column, $where);
		//var_dump($dateList[0]['data_source']);exit;
		if($dateList) {
			if($dateList[0]['data_source'] >1 ) { //export datasource all bigger than 1
				return 1;
				
			}
			
		}

		return 0; //为本地数据源 或没有查找到该商家，视为本地数据源
    }
	
	 //根据给定的用户编号，确认该用户在物流管理pannel上显示的可用配送日期列表
   public function getAvaliableDateOfThisDispatchingCentre($businessId) {
		
       //获取cc_order可以配送的日期
		$sql_cc_order_avaliabe_date ='SELECT DISTINCT logistic_delivery_date  from cc_order left join cc_wj_customer_coupon b on cc_order.orderId =b.order_Id where logistic_delivery_date >'.(time()-3600*24*7). ' and ';
        $sql_cc_order_avaliabe_date .= '  ( business_userId = '.$businessId ;
		$sql_cc_order_avaliabe_date .= '  or  b.business_id = '.$businessId ;
		$sql_cc_order_avaliabe_date .= '  or b.business_id = (select distinct  business_id  from cc_freshfood_disp_centre_suppliers where suppliers_id ='.$businessId.') ';
        $sql_cc_order_avaliabe_date .='  	or b.business_id in (select business_id  from cc_dispatching_centre_customer_list where dispatching_centre_id ='.$businessId.')';
		$sql_cc_order_avaliabe_date .='  	or b.business_id in (select customer_id  from cc_factory2c_list where factroy_id ='.$businessId.')';
		$sql_cc_order_avaliabe_date .='  	or b.business_id in (select customer_id  from cc_factory_2blist where factroy_id ='.$businessId.'))';
	
        $sql_cc_order_import_avaliabe_date ='SELECT DISTINCT logistic_delivery_date  from cc_order_import where logistic_delivery_date >'.(time()-3600*24*7). ' and ( business_userId = '.$businessId.' or business_userId in (select business_id  from cc_dispatching_centre_customer_list where dispatching_centre_id ='.$businessId.'))';
        $sql_union = 'select DISTINCT  logistic_delivery_date from (select * from( ('. $sql_cc_order_avaliabe_date.') union ('.$sql_cc_order_import_avaliabe_date.')) as d ) as c';
	  // var_dump($sql_union);exit;
		$dateOptions =loadModel('dispatching_centre_customer_list')->getListBySql($sql_union);
		//var_dump($dateOptions);exit;
		return $dateOptions; //为本地数据源 或没有查找到该商家，视为本地数据源
    }
	
	public function getALLAvaliableCustomerListsForCurrentBusiness($business_id,$isFactory){
		
		
		$sql1 = "select * from cc_dispatching_centre_customer_list where dispatching_centre_id = $business_id order by sort ";
		$suplierList = loadModel('dispatching_centre_customer_list')->getListBySql($sql1);
		//var_dump($sql1);exit;
		if($suplierList) {
        // 如果某一个是统配店，那么拿到它的子店
		
		$newsuplierList=array();
		
		$index=0;
		 foreach ($suplierList as $key => $value) {
			 $newsuplierList[$index]=$value;
			 $index ++;
			 $where00 = array(
			  'business_id'	=>$value['business_id']		 
			 );
			  $dispacthing_busienss_list = loadModel('freshfood_disp_centre_suppliers')->getList([],$where00);
			  if(count($dispacthing_busienss_list)>0) {

				  //var_dump($dispacthing_busienss_list)	  ;exit;
				  foreach ($dispacthing_busienss_list as $key1 => $value1) {
					  $newsuplierList[$index]['dispatching_centre_id']= $value1['business_id'];
					  $newsuplierList[$index]['dispatching_name']= $value['dispatching_name'].'-'.$value1['suppliers_name'];
					  $newsuplierList[$index]['business_id']= $value1['suppliers_id'];
					  $newsuplierList[$index]['business_name']= $value['dispatching_name'].'-'.$value1['suppliers_name'];
					  $newsuplierList[$index]['data_source']= 1;
					   $newsuplierList[$index]['ref_seq_num']= 1;
					 
					  
					  $index ++;  
				  }
				  
				  
				  
				  }
				  
					 $where01 = array(
			  'factroy_id'	=>$value['business_id']		 
			 );
			  $f2c_busienss_list = loadModel('factory2c_list')->getList([],$where01);
			  if(count($f2c_busienss_list)>0) {

				  //var_dump($f2c_busienss_list)	  ;exit;
				  foreach ($f2c_busienss_list as $key1 => $value1) {
					  $newsuplierList[$index]['dispatching_centre_id']= $value1['factroy_id'];
					  $newsuplierList[$index]['dispatching_name']= $value['dispatching_name'];
					  $newsuplierList[$index]['business_id']= $value1['customer_id'];
					  $newsuplierList[$index]['business_name']= $value['dispatching_name'].'-'.$value1['simply_name'];
					  $newsuplierList[$index]['data_source']= 1;
					   $newsuplierList[$index]['ref_seq_num']= 1;
					 
					  
					  $index ++;  
				  }
				  
				  
				  
				  }  
				  
				  
				  		 $where02 = array(
			  'factroy_id'	=>$value['business_id']		 
			 );
			  $f2b_busienss_list = loadModel('factory_2blist')->getList([],$where01);
			  if(count($f2b_busienss_list)>0) {

				  //var_dump($f2c_busienss_list)	  ;exit;
				  foreach ($f2b_busienss_list as $key1 => $value1) {
					  $newsuplierList[$index]['dispatching_centre_id']= $value1['factroy_id'];
					  $newsuplierList[$index]['dispatching_name']= $value['dispatching_name'];
					  $newsuplierList[$index]['business_id']= $value1['customer_id'];
					  $newsuplierList[$index]['business_name']= $value1['simply_name'];
					  $newsuplierList[$index]['data_source']= 1;
					   $newsuplierList[$index]['ref_seq_num']= 1;
					 
					  
					  $index ++;  
				  }
				  
				  
				  
				  } 
			 
		 }
	
		
		
		
	}	
	//var_dump($newsuplierList);exit;
		
	//以下插入 如果该用户为厂家，那么将厂家主店的子店（销售channel 加入到列表种）
	$newsuplierList1 =array();
	if($isFactory) {
		$sql1 = "select id,factroy_id as dispatching_centre_id,customer_id as business_id ,'1' as data_source,'1' as ref_seq_num  from cc_factory2c_list where factroy_id =".$business_id;
		$factory_customer_list =loadModel('factory2c_list')->getListBySql($sql1);
		//var_dump($sql1);exit;
		
       foreach ($factory_customer_list as $key1 => $value1 ) {
		   $find =0;
			if($newsuplierList) {
				foreach ($newsuplierList as $key => $value) {  //对于产生的供应商列表进行遍历，如果找到该商家，不处理，如果未找到该商家则添加
				  if($value['business_id'] ==$value1['business_id']) {
					  
					  $find=1;
					  continue;
				  }
			 
			 }
			   
		   }
		   if(!$find) {
			   $arrayOfNewbusiness =array(
			     'id'=>9999,
				 'dispatching_centre_id'=>	$value1['dispatching_centre_id'],
				 'dispatching_name'=>loadModel('user')->getBusinessDisplayName($value1['dispatching_centre_id']),
				 'business_id'=>	$value1['business_id'],
				 'business_name'=>loadModel('user')->getBusinessDisplayName($value1['business_id']),
				 'createTime'=>	time(),
				 'data_source'=>1,
				 'ref_seq_num'=>1,
				 'sort'=>1
		   
			   );
			    array_push($newsuplierList, $arrayOfNewbusiness);
			   
		   }
		
          
			
			  
		
		  
		   

		  
	   }
		
		
	}
	
	
	
	 
		
		
		return 	$newsuplierList;
		
		
		
	}
	
	
}