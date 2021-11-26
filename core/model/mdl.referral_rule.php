<?php
/**
 * 给予介绍人特殊优惠提成率的规则
 *
 **/

class mdl_referral_rule extends mdl_base
{	

	protected $tableName = '#@_referral_rule';

	private $id;
	/**
	 * 基于的产品ID
	 */
	private $coupon_id;

	/**
	 * 分享的特殊优惠提成率
	 */
	private $special_rate;

	/**
	 * 提成的方式
	 */
	private $type;

	const TYPE_PERCENT='percent';
	const TYPE_FIXED_AMOUNT_PERITEM='fixed';

	/**
	 * 使用码
	 */
	private $apply_code;

	/**
	 * 状态
	 */
	private $status;

	const STATUS_ENABLE = 0;   //可用
	const STATUS_DISABLE = 1;  //禁用
	const STATUS_LOCK = 2;     //锁定不再允许新用户使用该rule


	private $create_user_id;
	
	private $gen_date;


	public function setId($id)
	{
		$this->id=$id;
		return $this;
	}

	public function setCoupon($coupon_id)
	{
		$this->coupon_id=$coupon_id;
		return $this;
	}

	public function setOwner($owner)
	{
		$this->create_user_id=$owner;
		return $this;
	}

	public function setRate($rate,$type=null)
	{
		$this->special_rate=$rate;
		$this->type = ($type==null)?self::TYPE_PERCENT:$type;
		return $this;
	}


	public function create()
	{
		//default value;
		$this->apply_code=$this->_generateCode();
		$this->status= self::STATUS_ENABLE;
		$this->gen_date= time();

		$data['create_user_id']=$this->create_user_id;
		$data['coupon_id']=$this->coupon_id;
		$data['special_rate']=$this->special_rate;
		$data['type']=$this->type;

		$data['apply_code']=$this->apply_code;
		$data['status']=$this->status;
		$data['gen_date']=$this->gen_date;

		$this->id = $this->insert($data);

		$data['id']=$this->id;

		if($this->id){
			return $data;
		}else{
			return false;
		}
		
	}


	// Must be unique
	private function _generateCode()
	{
		$code = '';

		do{

			$code = $this->createRnd(); // 6 digit number

		}while ( $this->_isCodeExist($code));

		return $code;
	}



	private function _isCodeExist($code)
	{	
		if(!$code)return false;

		$where['apply_code']=$code;
		$data = $this->getByWhere($where);

		if($data){
			return true;
		}else{
			return false;
		}
	}

	public function getByCode($code)
	{
		if(!$code)return false;

		$where['apply_code']=$code;
		$data = $this->getByWhere($where);

		return $data;
	}

	public function updateRuleStatus($status)
	{	
		$status_list=[self::STATUS_ENABLE,self::STATUS_DISABLE,self::STATUS_LOCK];

		if(!in_array($status, $status_list))throw new Exception("Invalid Referral Rule Status ", 1);
		
		$this->status = $status;

		$data['status']= $status;
		
		$where;

		if($this->id){

			$where['id']=$this->id;

		}elseif($this->apply_code){

			$where['apply_code']=$this->apply_code;

		}else{
			throw new Exception("Missing Update Id", 1);
		}

		return $this->updateByWhere($data,$where);
	}


	/**
	 * [applyRule description]
	 * @param  [type] $code    [description]
	 * @param  [type] $user_id [description]
	 * @return [type]          [description]
	 */
	public function applyRule($code,$user_id)
	{
		$rule = $this->verifyCode($code);
		if($rule){
			$rule_id = $rule['id'];
			$this->_saveApplication($rule_id,$user_id);
			return true;
		}else{
			return false;
		}
	}

	public function verifyCode($code)
	{
		$where['apply_code']=$code;
		$where['status']=self::STATUS_ENABLE;

		return $this->getByWhere($where);
	}

	private function _saveApplication($rule_id,$user_id)
	{
		$mdl_referral_rule_application = loadModel('referral_rule_application');

		if(!$mdl_referral_rule_application->exist($rule_id,$user_id))
			$mdl_referral_rule_application->save($rule_id,$user_id);
	}

	public function hasApplication($rule_id,$user_id)
	{
		return loadModel('referral_rule_application')->exist($rule_id,$user_id);
	}

	public function getRuleDesc($ruleData)
	{
		//desc
		$coupon = loadModel('coupons')->get($ruleData['coupon_id']);
		$ctitle=$coupon['title'];
		$crata=$coupon['platform_commission_rate'];
		$cbase=$coupon['platform_commission_base'];

		$desc='';
		if($ruleData['type']==self::TYPE_PERCENT){
			$desc=" 分享产品交易后每个获得 ";

			if($cbase>0){
				$desc.="$". $cbase*$ruleData['special_rate']." + ";
			}

			$desc.=($coupon['platform_commission_rate']*$ruleData['special_rate']*100)."% ";

			$desc.="产品售价的提成";



		}elseif($ruleData['type']==self::TYPE_FIXED_AMOUNT_PERITEM){
			$desc=" 分享产品交易后每个获得最高 $" . $ruleData['special_rate']."的提成";

		}
		return $desc;

	}

	public function createRnd ($length = 6)
	{
		$rnd = '';
		while (strlen($rnd) < $length)
		{
			$rnd .= rand();
		}
		if (strlen($rnd) > $length) $rnd = left($rnd, $length);
		return $rnd;
	}

	public function getListOfUser($userId)
	{
		$sql = "select c.title,rr.* from cc_referral_rule as rr left join cc_coupons as c on rr.coupon_id = c.id where rr.create_user_id=".$userId ." order by rr.id desc";
		
		$list = $this->getListBySql($sql);

		return $list;
	}

	public function getStatistics($id,$userId=0)
	{	
		$rule = $this->get($id);
		$rule['id'];
		$rule['coupon_id'];

		$result=array();

		$mdl_referral_relation=loadModel('referral_relation');

		/**
		 * pageView
		 */
		$sql = " Select count(id) as pageView from cc_referral_relation where type = '".mdl_referral_relation::CustomerRefRelation."' and couponId =".$rule['coupon_id'];

		if($userId)$sql .= " and refUserId = ".$userId;

		$data = $this->getListBySql($sql);

		$result['pageView']=$data[0][0];


		/**
		 * pageUser
		 */
		$sql = " Select count( DISTINCT userId) as pageUser from cc_referral_relation where type ='".mdl_referral_relation::CustomerRefRelation."' and userId !=0 and couponId =".$rule['coupon_id'];

		if($userId)$sql .= " and refUserId = ".$userId;

		$data = $this->getListBySql($sql);

		$result['pageUser']=$data[0][0];


		/**
		 * orderCount
		 */
		$result['orderCount'] = $mdl_referral_relation->getAppliedRuleOrderCount($userId,$rule['id']);


		/**
		 * totalSales
		 */
		$sql = " select cc.customer_buying_quantity,cc.voucher_deal_amount from cc_wj_customer_coupon as cc  left join cc_recharge as r on cc.order_id = r.orderId where cc.bonus_id = ".$rule['coupon_id']." and r.special_rule_id=".$rule['id'];

		if($userId)$sql .= " and r.userId=".$userId;
		$data = $this->getListBySql($sql);
		$totalSales = 0;
		foreach ($data as $key => $value) {	
			$totalSales += $value['customer_buying_quantity']*$value['voucher_deal_amount'];
		}
		$result['totalSales']=$totalSales;

		/**
		 * totalCommission
		 */
		$result['totalCommission'] =  $mdl_referral_relation->getAppliedRuleCommissionTotal($userId,$rule['id']);

		return $result;
	}


}

?>