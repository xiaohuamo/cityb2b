<?php

class mdl_authrise_manage_other_business_account extends mdl_base
{

	protected $tableName = '#@_authrise_manage_other_business_account';

}



class Authorise_Center {
	
	//获得客户商家列表
	public static function getCustmerLists($AuthriseBusiness = null)
	{	
		$where = $AuthriseBusiness ? ['authorise_business_id' => $dispCenterId] : []; 
		$data = loadModel('authrise_manage_other_business_account')->getList([],$where);
		return array_column($data, 'customer_id');
	}
	
	//获得客户商家列表 带名字
	public static function getCustmerListsWithBusinessName($authriseBusiness = null)
	{	
		
		
	
		
		$sql = "select a.customer_id,b.displayName  from cc_authrise_manage_other_business_account a left join cc_user b on a.customer_id =b.id  where authorise_business_id =".$authriseBusiness;
    
		$data = loadModel('authrise_manage_other_business_account')->getListBySql($sql);
		
	    $sql = "select id as customer_id,displayName  from cc_user where id =".$authriseBusiness;
    	
		$data0  = loadModel('user')->getListBySql($sql);
	    $sql = "select a.business_id as customer_id,b.displayName  from cc_dispatching_centre_customer_list a left join cc_user b on a.business_id =b.id  where dispatching_centre_id =".$authriseBusiness;
    	
		$data00  = loadModel('user')->getListBySql($sql);

	   
	
		$sql = "select a.suppliers_id as customer_id,b.displayName  from cc_freshfood_disp_centre_suppliers a left join cc_user b on a.suppliers_id =b.id  where a.business_id =".$authriseBusiness;
    
		$data1  = loadModel('freshfood_disp_centre_suppliers')->getListBySql($sql);
		
		
		$sql = "select  customer_id,b.displayName  from 	cc_factory2c_list a left join cc_user b on a.customer_id =b.id  where a.factroy_id =".$authriseBusiness;
    
		$data2  = loadModel('factory2c_list')->getListBySql($sql);
		
		$sql = "select  customer_id,b.displayName  from 	cc_factory_2blist a left join cc_user b on a.customer_id =b.id  where a.factroy_id =".$authriseBusiness;
    
		$data21  = loadModel('factory_2blist')->getListBySql($sql);
		//var_dump($sql);exit;
		
		$data3 =array_merge($data,$data0,$data00,$data1,$data2,$data21);
		
		$data4 = array_unique($data3, SORT_REGULAR);
		//	var_dump($data);exit;
		return $data4;
	}
	
	//获取某个客户是否为授权客户
	
	public static function getIsCustomerIdIsAuthorised($authriseBusiness = null,$customerId = null )
	{	
		
		$where = array (
		 'authorise_business_id' =>$authriseBusiness,
		 'customer_id'=>$customerId
		);
		$data = loadModel('authrise_manage_other_business_account')->getByWhere($where);
		if($data){

			return 1;
		 
		} 
		
		// 如果该商家是 统配店里的商家，依然是被授权的
		$where = array (
		 'business_id' =>$authriseBusiness,
		 'suppliers_id'=>$customerId
		);
		$data = loadModel('freshfood_disp_centre_suppliers')->getByWhere($where);
		if($data){

			return 1;
		 
		} 
		if ($authriseBusiness ==$customerId ) {
				return 1;
			
		}
		return 0;
		
		
	
	}
	
	
	
	
	
	   
}
?>