<?php

class mdl_overwriteCouponStoreLink extends mdl_base
{

	public function getOverWriteLink($couponId)
	{
		//如果该产品在 链接中有出现，所有的该产品将会被引导到指定商家
		//eg  $linkArray[$couponId]=$storeId;
		
		if(array_key_exists($couponId, $linkArray)){
			return $linkArray[$couponId];
		}else{
			return false;
		}


	}
}

?>