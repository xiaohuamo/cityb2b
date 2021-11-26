 <?php


/**
  * 用餐人数
  * 1人
  * 2－3人
  * 4-5人
  * 6-8人
  * 8人以上
  */ 
 
/**
 * 是否需要预约
 * 需要预约
 * 不需要预约
 */
 
/**
 * 预约说明
 */

/**
 * 是否限时
 * 不限时
 * 限时
 */
 
/**
 * 限时说明
 */
 
/**
 * 公共假日是否可用
 * 可用
 * 不可用
 */
 
/**
 * 是否可以和其它优惠同享
 * 同享
 * 不同享
 */

/**
 * 同享说明
 */
 
/**
 * 是否有包间
 * 有
 * 没有
 */
 
/**
 * 用餐时段
 * 早餐
 * 午餐
 * 晚餐
 * 宵夜
 */
 
 
class mdl_coupons_addon extends mdl_base
{

	protected $tableName = '#@_coupons_addon';

	public function set($id,$data,$sub='m')
	{	
		$data= $this->sanitiseData($data);

		if($sub=='s')$data['sub']='s';

		$where['couponid']=$id;
		$exist = $this->getByWhere($where);

		if(!$exist){
			$data['couponid']=$id;
			//var_dump($data);exit;
			return $this->insert($data);
		}else{
			return $this->updateByWhere($data,$where);
		}
	}

	public function sanitiseData($data)
	{
		
		$accepted_field=['couponid','nickname','phone','tel','guest_limit','apportmant_required','apportmant_required_desc','time_limit','time_limit_desc','available_on_holiday','sharable','sharable_desc','private_room','meal_type'];
		$multivalue_field=['guest_limit','meal_type'];

	
	
	
			foreach ($data as $key => $value) {
			if(!in_array($key, $accepted_field)){
				unset($data[$key]);
			}

			if(in_array($key, $multivalue_field)){
				$data[$key]= join(',',$value);
			}
			
		}
		
		

     // var_dump($data);exit;

		return $data;
	}

	public function getAddonData($id,$sub='m')
	{	
		$where['couponid']=$id;
		if($sub=='s')$where['sub']='s';
		$data =$this->getByWhere($where);
		if ($data['nickname']){
			
			$data['apportmant_required_desc'] .=' 联络人:'.$data['nickname'];
			
		}
		if ($data['phone']){
			
			$data['apportmant_required_desc'] .=' 手机:'.$data['phone'];
			
		}
		if ($data['tel']){
			
			$data['apportmant_required_desc'] .=' 店内电话:'.$data['tel'];
			
		}
		
		return $data;
	}

	public function getAddonText($id,$sub='m')
	{
		$addonData = $this->getAddonData($id,$sub);

		$textList = array();

		for ($i=1; $i <sizeof($addonData) ; $i++) { 
			$value = $addonData[$i];
			if(strstr($value, '1')){
			//	$textList[]='单人餐';
			}

			if(strstr($value, '2-3')){
			//	$textList[]='2-3人';
			}

			if(strstr($value, '4-5')){
			//	$textList[]='4-5人';
			}

			if(strstr($value, '6-8')){
			//	$textList[]='6-8人';
			}

			if(strstr($value, '9')){
			//	$textList[]='9人以上';
			}

			if(strstr($value, 'apportmant_require')){
				$textList[]='需要提前预约';
			}

			if(strstr($value, 'apportmant_not_required')){
				$textList[]='不需要提前预约';
			}


			if($value=='time_limit'){
				$textList[]='限时使用';
			}

			if($value=='no_time_limit'){
				$textList[]='当前有效';
			}


			if($value=='available_on_holiday'){
				$textList[]='公共假日可用';
			}

			if($value=='not_available_on_holiday'){
				$textList[]='不可在公共假日使用';
			}


			if($value=='sharable'){
				$textList[]='可叠加使用';
			}

			if($value=='not_sharable'){
				$textList[]='不可叠加';
			}


			if(strstr($value, 'has_private_room')){
				$textList[]='有包间';
			}

			if(strstr($value, 'no_private_room')){
				$textList[]='无包间';
			}


			if(strstr($value, 'breakfast')){
				$textList[]='早餐';
			}

			if(strstr($value, 'lunch')){
				$textList[]='午餐';
			}

			if(strstr($value, 'dinner')){
				$textList[]='晚餐';
			}

			if(strstr($value, 'midnight')){
				$textList[]='宵夜';
			}
		}

		return $textList;
	}

}

?>