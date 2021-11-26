<?php

class PromotionCode{
	/**
	 * 折扣码折扣方式：固定金额
	 */
	const TYPE_FIXEDAMOUNT='fixed';
	/**
	 * 折扣码折扣方式：百分比
	 */
	const TYPE_PERCENTAGE ='percent';

	/**
	 * 使用条件：无条件
	 */
	const CONDITION_NONE='none';
	/**
	 * 使用条件：达到购买数量
	 */
	const CONDITION_QTY ='conditionQty';
	/**
	 * 使用条件：达到最低消费
	 */
	const CONDITION_MINSPEND ='conditionMinspend';

	/**
	 * 折扣码过期：永久有效
	 */
	const EXPIRETYPE_UNLIMITED='unlimited';
	/**
	 * 折扣码过期：总数过期
	 */
	const EXPIRETYPE_FIXEDQTY ='fixedQty';
	/**
	 * 折扣码过期：N天内过期
	 */
	const EXPIRETYPE_EXPIREINDAYS ='expireInDays';

	const RANDOM_CODE='#randomCode#';

	private $type;
	private $value;
	private $condition;
	private $conditionValue;
	private $description;
	private $expireType;
	private $expireValue;
	private $code;

	private $userId; //createUserId
	/**
	 *  对全部商家可用 Global Code//仅限Ubonus系统内部发布
	 */
	const APPLY_TO_ALL_USERID=-1;  

	private $couponId;
	/**
	 * 商家范围内适用于全部产品，购物车总额
	 */
	const APPLY_TO_ALL_COUPONID=-1;

	private $appliedTimes;
	private $isExpired;

	private $genTime;

	private $singleUsePerUser;

	function __construct($data=null){
		if($data){
			$this->description 		=$data['promotion_des'];
			$this->userId 			=$data['user_id'];
			$this->couponId 		=$data['coupon_id'];
			$this->genTime 			=$data['gen_time'];
			$this->isExpired      	=$data['is_expired'];
			$this->type 			=$data['type'];
			$this->value 			=$data['value'];
			$this->condition 		=$data['apply_condition'];
			$this->conditionValue 	=$data['apply_condition_value'];
			$this->expireType 		=$data['expire_type'];
			$this->expireValue 		=$data['expire_value'];
			$this->appliedTimes 	=intval($data['applied_times']);
			$this->code 			=$data['promotion_code'];
			$this->singleUsePerUser =$data['single_use_per_user'];
		}else{
		   $this->genTime=time();
		   $this->condition=self::CONDITION_NONE;
		   $this->expireType=self::EXPIRETYPE_UNLIMITED;
		   $this->isExpired=0;
		   $this->appliedTimes=0;
		   $this->singleUsePerUser=0;
		}

	}   

	public function setUserId($userId){
		$this->userId=$userId;
	}

	public function setCouponId($couponId){
		$this->couponId=$couponId;
	}

	public function setDescription($des){
		$this->description=$des;
	}
	public function getDescription(){
		return $this->description;
	}

	public function setType($type,$value){
		$this->type = $type;
		$this->value= $value;
	}
	public function setCondition($condition,$conditionValue){
		$this->condition=$condition;
		$this->conditionValue 	=$conditionValue;
	}

	public function setExpireType($expireType,$expireValue=null){
		$this->expireType 	=$expireType;
		$this->expireValue = $expireValue;
	}

	public function setCode($code){
		if($code ==self::RANDOM_CODE){
			$codes=loadModel('wj_promotion_code')->getAllPromotionCodes();
			do {
				$generatedcode = $this->createRndStr();
			} while ( $this->isCodeAlreadyExist($generatedcode,$codes));

			$this->code= $generatedcode;

			return true;
		}else{
			if($this->isCodeAvailable($code)){
				$this->code = $code;
				return true;
			}else{
				return false;
			};
		}
	}

	public function getCode(){
		return $this->code;
	}

	private function isCodeAlreadyExist($code,$codes){
		$code = strtolower($code);
		
		foreach ($codes as $item) {
			if(strtolower($item['promotion_code']) ==$code)return true;
		}
		return false;
	}

	public function isCodeAvailable($code){
		$codes=loadModel('wj_promotion_code')->getAllPromotionCodes();
		return !$this->isCodeAlreadyExist($code,$codes);
	}

	public function isCodeExpired(){

		if($this->isExpired==true){
			return true;
		}

		if($this->expireType==self::EXPIRETYPE_EXPIREINDAYS){
			$diff = time() - intval($this->genTime);
			if($diff<0){
				throw new Exception("Generate Time is greater than now", 1);
			}else{
				if($diff>intval($this->expireValue)*3600*24 ){
					$this->isExpired= true;
				}else{
					$this->isExpired= false;
				}
			}
		}elseif($this->expireType==self::EXPIRETYPE_FIXEDQTY){
			if($this->appliedTimes>=$this->expireValue){
				$this->isExpired= true;
			}else{
				$this->isExpired= false;
			}
		}else{
			$this->isExpired= false;
		}

		return $this->isExpired;
	}

	public function applyCountAdd($count)
	{
		$this->appliedTimes +=$count;
	}

	public function setSingleUsePerUser($value)
	{
		$this->singleUsePerUser=$value;
	}
	
	public function toDBArray(){
		$data['promotion_des']=$this->description;
		$data['user_id']=$this->userId;
		$data['coupon_id']=$this->couponId;
		$data['gen_time']=$this->genTime;
		$data['is_expired']=$this->isExpired;
		$data['type']=$this->type;
		$data['value']=$this->value;
		$data['apply_condition']=$this->condition ;
		$data['apply_condition_value']=$this->conditionValue;
		$data['expire_type']=$this->expireType;
		$data['expire_value']=$this->expireValue;
		$data['applied_times']=$this->appliedTimes;
		$data['promotion_code']=$this->code;
		$data['single_use_per_user']=$this->singleUsePerUser;

		return $data;
	}

	
	private  function createRndStr ($length = 6)
	{
		$rnd = '';
		$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		while (strlen($rnd) < $length)
		{
			$rnd .= $str[rand( 0, strlen( $str ) - 1 )];
		}
		if (strlen($rnd) > $length) $rnd = left($rnd, $length);
		return $rnd;
	}

	public function applyCode($userId){

		$mdl_cart= loadModel('wj_user_temp_carts');
		if($this->couponId==self::APPLY_TO_ALL_COUPONID){
			//购物车总额打折
			if($this->condition==self::CONDITION_QTY){
				$total= $mdl_cart->getTotalMoney($userId);
				$totalQty=$mdl_cart->getTotalQty($userId);
				if($totalQty>$this->conditionValue){
					return  $this->calculateDiscountAmount($total);
				}else{
					return 0;
				}
			}elseif($this->condition==self::CONDITION_MINSPEND){
				$total= $mdl_cart->getTotalMoney($userId);
				if($total>$this->conditionValue){
					return  $this->calculateDiscountAmount($total);
				}else{
					return 0;
				}
			}else{
				$total= $mdl_cart->getTotalMoney($userId);
				return  $this->calculateDiscountAmount($total);
			}

		}else{
			$total=0;
			//单个产品打折总价打折
			$items = $mdl_cart->getAllItems($userId,$this->couponId);

			if($this->condition==self::CONDITION_QTY){
				$totalQty=0;
				foreach ($items as $item) {
					$price=$item['single_amount'];
					$qty=$item['quantity'];
					$totalQty= $totalQty+$qty;
				}
				$discount= $this->calculateDiscountAmount($price);
				if($totalQty>$this->conditionValue)$total=$totalQty*$discount;
			}elseif($this->condition==self::CONDITION_MINSPEND){
				$totalValue=0;
				foreach ($items as $item) {
					$price=$item['single_amount'];
					$qty=$item['quantity'];
					$totalValue= $totalValue+$qty*$price;
				}
				$discount= $this->calculateDiscountAmount($price);
				if($totalValue>$this->conditionValue)$total=$totalQty*$discount;
			}else{
				foreach ($items as $item) {
					$price=$item['single_amount'];
					$qty=$item['quantity'];
					$discount= $this->calculateDiscountAmount($price);
					$total +=$qty*$discount;
				}
			}
			
			return $total;
		}
	}

	function calculateDiscountAmount($original){
		$discount=0;
		if($this->type==self::TYPE_FIXEDAMOUNT){
			$discount= floatval($this->value);
		}elseif ($this->type==self::TYPE_PERCENTAGE) {
			$discount= floatval($this->value) * floatval($original)/100;
		}else{
			throw new Exception("Unknown promotion Type:".$this->type);
		}

		return $discount;
	}
}


class mdl_wj_promotion_code extends mdl_base
{

	protected $tableName = '#@_wj_promotion_code';

	public function addPromotionCode($promotionCode){
		return $this->insert($promotionCode->toDBArray());
	}

	public function getPromotionCode($code){
		$where['promotion_code']=$code;
		$data=$this->getByWhere($where);
		return $data;
	}
	
	public function isGlobalPromotionCode($code){
		$data = $this->getPromotionCode($code);
		return ($data['user_id']==PromotionCode::APPLY_TO_ALL_USERID&&$data['coupon_id']==PromotionCode::APPLY_TO_ALL_COUPONID);
	}

	public function getPromotionCodeById($id){
		$where['id']=$id;
		$data=$this->getByWhere($where);
		return $data;
	}

	public function isGlobalPromotionCodeById($id){
		$data = $this->getPromotionCodeById($id);
		return ($data['user_id']==PromotionCode::APPLY_TO_ALL_USERID&&$data['coupon_id']==PromotionCode::APPLY_TO_ALL_COUPONID);
	}

	public function getPromotionCodeByUserId($userId){
		$where['user_id']=$userId;
		$data=$this->getList(null,$where,null,null);
		return $data;
	}

	public function getAllPromotionCodes(){
		return $this->getList(['promotion_code']);
	}

	public function disablePromotionCode($id){
		$data['is_expired'] = 1;
		$where['id']=$id ;
		return $this->updateByWhere($data,$where);
	}

	public function deletePromotionCode($id){
		return $this->delete($id);
	}

	public function isCodeBelongToUser($codeId,$userId){
		$where['user_id']=$userId;
		$where['id'] =$codeId;
		$promotionCode=$this->getByWhere($where);

		if($promotionCode){
			return true;
		}else{
			return false;
		}

	}
	public function updateAppliedCount($id,$count){
		$promotionCode = new PromotionCode($this->get($id));
		$promotionCode->applyCountAdd($count);
		$promotionCode->isCodeExpired();

		$where['id']=$id;
		$data=$promotionCode->toDBArray();
		return $this->updateByWhere($data,$where);
	}

	/*
	public function promotionExist($userId,$business_userId=null){
		$promotionExist=false;

		// 用户现有购物车里的全部产品：
		$sql =" select distinct main_coupon_id as id,businessUserId from cc_wj_user_temp_carts where userId = $userId";
		$cartsCouponId=$this->getListBySql($sql);

		if($cartsCouponId){
			//商家的 全部可用 promotion：
			$business_userId = $cartsCouponId[0]['businessUserId'];
			$sql = "select coupon_id as id from cc_wj_promotion_code where user_id = $business_userId and is_expired = 0";
			$promotionCouponId=$this->getListBySql($sql);
		}else{
			return $promotionExist;
		}

		foreach ($cartsCouponId as $crow) {
			foreach ($promotionCouponId as $prow) {		
				if($prow['id']==-1 || $prow['id']==$crow['id']){
					$promotionExist=true;
					break;
				}
			}
		}
		return $promotionExist;
	}
	*/
	
	
	
	
	
	public function getPromotionCodeList($userId,$businessUserId){
		
		$sql ="select promotion_des ,value,promotion_code   from cc_wj_promotion_code where real_user_id =$userId and  (user_id = $businessUserId or user_id=".promotionCode::APPLY_TO_ALL_USERID.")  and 	coupon_id='-1' and  is_expired=0 and  type ='fixed'  and  ((applied_times =0 && 	single_use_per_user=1 ) || single_use_per_user=0 ) and (gen_time>unix_timestamp(now())-3600*24* expire_value) limit 5 ";
	   // var_dump（$sql);exit;	
		$promotionCodeList = $this->getListBySql($sql);
		return $promotionCodeList;
	}
		
	

	public function matchedCode($userId,$businessUserId){
		$codeList=[];

		// 用户现有购物车里的全部产品：
		$sql =" select distinct main_coupon_id as id from cc_wj_user_temp_carts where userId = $userId";
		$cartsCouponId=$this->getListBySql($sql);

		if($cartsCouponId){
			//商家的 全部可用 promotion code + global code (userId = APPLY_TO_ALL_USERID )
			$sql = "select coupon_id as id,promotion_code as code from cc_wj_promotion_code where (user_id = $businessUserId or user_id=".promotionCode::APPLY_TO_ALL_USERID.") and is_expired = 0";
			$promotionCouponId=$this->getListBySql($sql);
		}else{
			return false;
		}

		foreach ($cartsCouponId as $crow) {
			foreach ($promotionCouponId as $prow) {		
				if($prow['id']==-1 || $prow['id']==$crow['id']){
					array_push($codeList, $prow['code']);
				}
			}
		}
		
		return array_unique($codeList);

	}
}

?>