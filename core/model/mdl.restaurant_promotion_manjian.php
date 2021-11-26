 <?php
 


class mdl_restaurant_promotion_manjian extends mdl_base
{

	protected $tableName = '#@_restaurant_promotion_manjian';

		
	//获得某个餐馆的通用折扣率
    public function getRestaurantPromotionManjian($id)
  	{
  		$sql ="select * from ".$this->tableName." where restaurant_id=".$id;
		
		//echo $sql ;exit;
		$restaurant_promotion_manjian  = $this->getListBySql($sql);
		
		if($restaurant_promotion_manjian) {
		   $restaurant_promotion_manjian_rates =$restaurant_promotion_manjian[0][discount]/100;
	   }else{
		   $restaurant_promotion_manjian_rates=0;
	   }
				
		return $restaurant_promotion_manjian_rates;
	
				
  	}

}

?>