 <?php
class mdl_freshfood_disp_centre_suppliers extends mdl_base
{
	protected $tableName = '#@_freshfood_disp_centre_suppliers';
}


class DispCentreSuppliers {
	

	
	
	//获取某个客户是否为授权客户
	
	public static function getIsCustomerIdIsInDispCentre($DispcenterID = null,$supplierId = null )
	{	
		
		$where = array (
		 'business_id' =>$DispcenterID,
		 'suppliers_id'=>$supplierId
		);
		$data = loadModel('freshfood_disp_centre_suppliers')->getByWhere($where);
		if($data){

			return 1;
		 
		} else{
			 	return 0;
		 }
		
	
	}
	
	
	//获取某个客户是否为授权客户
	
	public static function deleteAllScheduleTimeForCertainSuppliers($DispcenterID = null,$supplierId = null )
	{	
		
		$where = array (
		 'centre_business_id' =>$DispcenterID,
		 'business_id'=>$supplierId
		);
		loadModel('freshfood_disp_suppliers_schedule')->deleteByWhere($where);
		 return 1;
		
	
	}
	
	 public static function getSupplierNameOnCertainCentreBusinessId($DispcenterID = null,$supplierId = null )
	{	
		
		$where = array (
		 'business_id' =>$DispcenterID,
		 'suppliers_id'=>$supplierId
		);
		$data =loadModel('freshfood_disp_centre_suppliers')->getByWhere($where);
		 return $data;
		
	
	}
	
	
	   
}

?>
