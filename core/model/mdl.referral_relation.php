<?php
/**
 * 介绍人被介绍人和产品的关系
 *
 * 介绍人ID
 * 被介绍人ID
 * Type   business or user 
 * couponId
 * time
 **/

class mdl_referral_relation extends mdl_base
{	
	const BusinessRefRelation=0;
	const CustomerRefRelation=1;
	const ValidTimeLength=24*3600; // 1 day;

	protected $tableName = '#@_referral_relation';

	private $ownerId;


	/**
	 * 设置动作的 owner 用户
	 * 没有owner 其他动作无法执行
	 * @param type $id 
	 * @return type
	 */

	public function owner($id)
	{	
		//!if id is not a ref user do not asign. then all add function will return false
		$this->ownerId = $id;
		return $this;
	}


	public function addBusiness($userId)
	{
		if(!$this->ownerId)return false;

		if($this->businessExist($userId)) return false;

		$data['userId']=$userId;
		$data['refUserId']=$this->ownerId;
		$data['type']=self::BusinessRefRelation;
		$data['time']=time();

		return $this->insert($data);

	}

	public function removeBusiness($userId)
	{
		if(!$this->ownerId)return false;

		if(!$this->businessExist($userId)) return false;

		$where['userId']=$userId;
		$where['refUserId']=$this->ownerId;
		$where['type']=self::BusinessRefRelation;

		return $this->deleteByWhere($where);
	}

	public function businessExist($userId)
	{
		if(!$this->ownerId)return false;

		$where['userId']=$userId;
		$where['type']=self::BusinessRefRelation;

		return $this->getByWhere($where);
	}

	
	public function addUser($userId,$couponId)
	{	
		if(!$this->ownerId)return false;

	//	if($this->ownerId==$userId)return false;


		$data['userId']=$userId;
		$data['refUserId']=$this->ownerId;
		$data['type']=self::CustomerRefRelation;
		$data['couponId']=$couponId;
		$data['time']=time();

		return $this->insert($data);
	}

	

	public function getBusinessList()
	{
		if(!$this->ownerId)return false;

		$curent = time();

		$where['refUserId']=$this->ownerId;
		$where['type']=self::BusinessRefRelation;

		$data =$this->getList(null,$where);

		return $data;
	}

	public function getUserList($couponId)
	{
		if(!$this->ownerId)return false;

		$mdl_user=loadModel('user');

		$curent = time();

		$where['refUserId']=$this->ownerId;
		$where['type']=self::CustomerRefRelation;
		//$where[]=" $curent-time < ".self::ValidTimeLength;

		//$where[]=" userId !=0 ";//暂不显示未登录用户组

		$where['couponId']=$couponId;

		$sql = $this->getListSql('userId,couponId,type,count(id) as hits',$where);
		$sql .=" group by userId ";
		$data = $this->getListBySql($sql);

		foreach ($data as $key => $value) {

			$currentSuccessor=$this->getCouponRefUserId($value['userId'],$value['couponId'],true);
			$successorId = $currentSuccessor['refUserId'];
			$time = $currentSuccessor['time'];

			$data[$key]['currentSuccessor']=$successorId;
			$data[$key]['time']=$time;

			$data[$key]['userName']=($value['userId']==0)?'未登录用户组':$mdl_user->getUserDisplayName($value['userId']);
			$data[$key]['userAvatar']=$mdl_user->getAvatar($value['userId']);

			if($successorId==$this->ownerId)$data[$key]['totalCommission']=$this->getRefCommissionTotal($this->ownerId,$value['couponId'],$value['userId']);
		}

		return $data;
	}

	/**
	 * order by coupon 显示分享的产品
	 * @return [type] [description]
	 */
	public function getCouponList()
	{
		if(!$this->ownerId)return false;

		$sql = "SELECT couponId,count(couponId) as hits FROM cc_referral_relation WHERE refUserId =".$this->ownerId." and type = 1 GROUP by couponId order by id desc";
		$data = $this->getListBySql($sql);

		$mdl_coupons =loadModel('coupons');

		$mdl_referral_rule_application=loadModel('referral_rule_application');

		foreach ($data as $key => $value) {
			$data[$key]['referralRule']=$mdl_referral_rule_application->userHasAppliableRuleOnCoupon($this->ownerId,$value['couponId']);
			$data[$key]['couponTitle'] = $mdl_coupons->getTitle($value['couponId']);
			$data[$key]['totalCommission']=$this->getRefCommissionTotal($this->ownerId,$value['couponId']);
			$data[$key]['orderCount']=$this->getRefOrderCount($this->ownerId,$value['couponId']);
		}

		return $data;
	}

	/**
	 * A refer X to B.
	 * 
	 * @param int $baseUserId  A's Id
	 * @param int $couponId  X's Id
	 * @param int $refUserId  B's Id
	 * @return totalCommission
	 */
	public function getRefCommissionTotal($baseUserId,$couponId=0,$refUserId=0)
	{
		$sql = "select sum(money) as totalCommission from cc_recharge where payment ='CUSTOMER_REF_COMMISSION' and userId = $baseUserId ";

		if($couponId)$sql .=" and coupon_id = $couponId";

		if($refUserId)$sql .=" and business_userId = ".$refUserId;

		$data= $this->getListBySql($sql);

		if(!$data[0][0])return 0;

		return $data[0][0];
	}

	/**
	 * @return orderCount
	 */
	public function getRefOrderCount($baseUserId,$couponId=0,$refUserId=0)
	{
		$sql = "select count(orderId) as orderCount from cc_recharge where payment ='CUSTOMER_REF_COMMISSION' and userId = $baseUserId ";

		if($couponId)$sql .=" and coupon_id = $couponId";

		if($refUserId)$sql .=" and business_userId = ".$refUserId;

		$data= $this->getListBySql($sql);

		if(!$data[0][0])return 0;

		return $data[0][0];
	}

	
	public function getAppliedRuleCommissionTotal($baseUserId,$ruleId)
	{
		$sql = "select sum(money) as totalCommission from cc_recharge where payment ='CUSTOMER_REF_COMMISSION' ";

		if($baseUserId)$sql .=" and userId = $baseUserId";

		if($ruleId){
			$sql.=" and special_rule_id = $ruleId";
		}else{
			$sql.=" and special_rule_id != 0";
		}

		$data= $this->getListBySql($sql);

		if(!$data[0][0])return 0;

		return $data[0][0];
	}

	public function getAppliedRuleOrderCount($baseUserId,$ruleId)
	{
		$sql = "select count(orderId) as orderCount from cc_recharge where payment ='CUSTOMER_REF_COMMISSION' ";

		if($baseUserId)$sql .=" and userId = $baseUserId";
		
		if($ruleId){
			$sql.=" and special_rule_id = $ruleId";
		}else{
			$sql.=" and special_rule_id != 0";
		}

		$data= $this->getListBySql($sql);

		if(!$data[0][0])return 0;

		return $data[0][0];
	}

	/**
	 * if A refer X to B
	 * when B buy X
	 * B need to find A
	 * 
	 * @param int $userId  B's Id
	 * @param int $couponId  X's Id
	 * @return A's Id
	 */
	public function getCouponRefUserId($userId,$couponId,$flag=false)
	{
		$curent = time();

		$where['userId']=$userId;
		$where['couponId']=$couponId;
		$where['type']=self::CustomerRefRelation;
		$where[]=" $curent-time < ".self::ValidTimeLength;
		
		$result= $this->getList(null,$where,"time desc",1);

		$refRelation = reset($result); //first element

		if($refRelation){

			//if(!loadModel('referrals')->isApproved($refRelation['refUserId'])) return false;

			if($flag)
				return $refRelation;
			else
				return $refRelation['refUserId'];
		}else{
			return false;
		}

	}

	/**
	 * Business Relation is one to many
	 * each business could only have one referer
	 * @param type $userId 
	 * @return type
	 */
	public function getBusinessRefUserId($userId)
	{
		$where['userId']=$userId;
		$where['type']=self::BusinessRefRelation;
		
		$result= $this->getList('refUserId',$where,"time DESC",1);

		$refRelation = reset($result); //first element

		if($refRelation){

			if(!loadModel('referrals')->isApproved($refRelation['refUserId'])) return false;

			return $refRelation['refUserId'];
		}else{
			return false;
		}
	}


	public function payUserRefCommission($amount,$to,$orderId,$desc,$couponId,$userId,$special_rule_id)
	{
		$mdl_recharge=loadModel('recharge');

		$data['userId']=$to;
		$data['money']=$amount;
		$data['orderId']=$orderId;
		$data['coupon_name']=$desc;
		$data['coupon_id']=$couponId;

		$data['business_userId']=$userId; //产品购买者的ID
		$data['special_rule_id']=$special_rule_id;  //是否使用了媒体码

		$data['payment']=BalanceProcess::TYPE_SYS_CUSTOMER_REF_COMMISSION;

		$data['createTime']=time();
		$data['createIp']=ip();

		$data['status']=0;//init state padding

		return $mdl_recharge->insert($data);

	}

	public function payBusinessRefCommission($amount,$to,$orderId,$desc,$couponId,$userId)
	{
		$mdl_recharge=loadModel('recharge');

		$data['userId']=$to;
		$data['money']=$amount;
		$data['orderId']=$orderId;
		$data['coupon_name']=$desc;
		$data['coupon_id']=$couponId;

		$data['business_userId']=$userId; //产品发布者的ID

		$data['payment']=BalanceProcess::TYPE_SYS_BUSINESS_REF_COMMISSION;

		$data['createTime']=time();
		$data['createIp']=ip();

		$data['status']=0;//init state padding

		return $mdl_recharge->insert($data);

	}

}

?>