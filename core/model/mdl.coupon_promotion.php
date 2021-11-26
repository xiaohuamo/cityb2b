
<?php

class mdl_coupon_promotion extends mdl_base
{
	const TYPE_72HOUR  ='promotion1';
	const TYPE_DISCOUNT='promotion2';
	const TYPE_FREE    ='promotion3';
	const TYPE_GRANDOPENING ='promotion4';

	const PROMOTION_ON =1;
	const PROMOTION_OFF=0;

	protected $tableName = '#@_coupon_promotion';
	
	
	function getPromotionList($type,$start=null,$end=null){
		$column = ['coupon_id'];
		$where[$type]=self::PROMOTION_ON;
		return $this->getList($column,$where);
	}

	function getPromotionListIdSql($type,$start=null,$end=null){
		$result =$this->getPromotionList($type,$start,$end);
		
		if($result){
			$IDs = array();
			foreach ($result as $row) {
				array_push($IDs,$row['coupon_id']);
			}

			$sql = 'id in ('.join(',',$IDs).')';

			return $sql;
		}else{
			return null;
		}


	}
}

?>