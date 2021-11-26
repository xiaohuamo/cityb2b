<?php
/**
 * 给予介绍人特殊优惠提成率的规则
 *
 **/

class mdl_referral_rule_application extends mdl_base
{	
	protected $tableName = '#@_referral_rule_application';

	private $user_id;

	private $rule_id;

	private $gen_date;

	private $status;

	const STATUS_ENABLE = 1;  //可用
	const STATUS_DISABLE = 0; //禁用


	public function getUserListOfRule($rule_id)
	{	
		$sql = "Select ra.*,r.status as rule_status from cc_referral_rule_application as ra left join cc_referral_rule as r on ra.rule_id = r.id where ra.rule_id = $rule_id";
		return $this->getListBySql($sql);
	}


	public function getUserRuleList($user_id)
	{	

		$mdl_referral_relation = loadModel('referral_relation');

		$mdl_coupons = loadModel('coupons');

		$sql = "Select ra.*,r.status as rule_status from cc_referral_rule_application as ra left join cc_referral_rule as r on ra.rule_id = r.id where ra.user_id = $user_id order by ra.id desc";

		$data=$this->getListBySql($sql);

		foreach ($data as $key => $value) {
			$data[$key]['commissionTotal']=$mdl_referral_relation->getAppliedRuleCommissionTotal($user_id,$value['rule_id']);
			$data[$key]['orderCount']=$mdl_referral_relation->getAppliedRuleOrderCount($user_id,$value['rule_id']);
			$data[$key]['coupon']=$mdl_coupons->get($value['coupon_id']);
		}

		return $data;
	}


	/**
	 * 当用户购买时检查购买用户的推荐人是否有基于该产品的特殊推荐奖励
	 *
	 * 商家可以基于一个产品生成多个Code 用以分配给不同组的媒体用户
	 * 媒体用户使用code时运用替换策略：如果对同一产品使用多个code，最后一个code有效
	 * 如果最后一个失效或禁用，一次类推使用上一个code
	 * 
	 * @param  [type] $user_id   [description]
	 * @param  [type] $coupon_id [description]
	 * @return [type]            [description]
	 */
	public function userHasAppliableRuleOnCoupon($user_id,$coupon_id)
	{

		loadModel('referral_rule');

		$sql = "Select ra.*,r.status as rule_status from cc_referral_rule_application as ra left join cc_referral_rule as r on ra.rule_id = r.id where ra.user_id = $user_id and ra.coupon_id = $coupon_id and ra.status=".self::STATUS_ENABLE." and r.status != ".mdl_referral_rule::STATUS_DISABLE." order by ra.id desc";

		$list = $this->getListBySql($sql);

		return reset($list); //first element

	}


	public function exist($rule_id,$user_id)
	{	
		$where['rule_id']=$rule_id;
		$where['user_id']=$user_id;

		$rule = $this->getByWhere($where);

		if($rule){
			return true;
		}else{
			return false;
		}
	}

	public function save($rule_id,$user_id)
	{
		$rule = loadModel('referral_rule')->get($rule_id);

		if(!$rule)throw new Exception("Invalid Referral Rule Id", 1);

		$data['rule_id']=$rule_id;

		$data['user_id']=$user_id;

		$data['gen_date']=time();

		$data['status']=self::STATUS_ENABLE;

		//save extra data
		$data['coupon_id']=$rule['coupon_id'];

		$data['special_rate']=$rule['special_rate'];

		$data['type']=$rule['type'];

		$data['apply_code']=$rule['apply_code'];

		return $this->insert($data);
	}

	public function updateStatus($status,$id)
	{	
		$status_list=[self::STATUS_ENABLE,self::STATUS_DISABLE];
		if(!in_array($status, $status_list))throw new Exception("Invalid status of referral rule application", 1);
		
		$data['status']=$status;

		return $this->update($data,$id);
	}

}

?>