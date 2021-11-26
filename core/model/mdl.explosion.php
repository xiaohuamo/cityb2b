<?php

class mdl_explosion extends mdl_base
{
	protected $tableName = '#@_explosion';
	
	
	function get_Recomend_list(){
		
			$sql ="SELECT a.*,b.title,b.pic FROM `cc_explosion` a ,cc_coupons b WHERE  b.id=a.couponid and a.pageType=1 and a.panaltype=1 order by sort   ";
		
		//echo $sql ;exit;
		$result = $this->getListBySql($sql);
		return $result;
	}
}

?>