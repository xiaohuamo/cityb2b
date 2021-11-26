<?php
/**
 * 用户申请成为介绍人的信息
 *
 * @package default
 * @author 
 **/

class mdl_referrals extends mdl_base
{	
	/**
	 * 介绍人分成时
	 * Ubonus的抽成C = （DEFAULT_PLATFORM_COMMISSION_BASE + DEFAULT_PLATFORM_COMMISSION_BASE） * qty 
	 *
	 * 用户介绍人抽成为 c1 = C * Commission_Rate_User
	 * 商家介绍人抽成为 c2 = (C - c1) * Commission_Rate_Business
	 */
	
	const UBONUS_COMMISSION_SPLIT_RATIO_USERREF = 1.0;
	const UBONUS_COMMISSION_SPLIT_RATIO_BUSINESSREF = 0.1;

	const Default_Commission_Rate_User=0.2;     //MAX 1 
	const Default_Commission_Rate_Business=0.1;    //MAX 1 

	protected $tableName = '#@_referrals';

	public function join($id,$info=null)
	{	
		if($this->getByWhere(array('userId'=>$id))) return true;//already exist

		$ref = array(
				'userId' => $id,
				'type' => 0,
				'createTime' => time(),
				'isApproved' => 0,
				'auditTime' => time(),
				'businessRefRate'=>self::Default_Commission_Rate_Business,
				'customerRefRate'=>self::Default_Commission_Rate_User
		);

		if($info){
			$ref['name'] 		= $info['name'];
			$ref['firstName'] 	= $info['firstName'];
			$ref['lastName'] 	= $info['lastName'];
			$ref['email'] 		= $info['email'];
			$ref['phone'] 		= $info['phone'];
		}

		return $this->insert( $ref );
	}

	public function quit()
	{
		
	}

	public function setApprove($userId)
	{	
		if(!$userId)throw new Exception("Error Processing Request", 1);
		
		$data['isApproved']=1;
		$where['userId']=$userId;
		return $this->updateByWhere($data,$where);
	}

	public function setUnapprove($userId)
	{	
		if(!$userId)throw new Exception("Error Processing Request", 1);
		
		$data['isApproved']=0;
		$where['userId']=$userId;
		$data = $this->updateByWhere($data,$where);
	}

	public function isApproved($userId)
	{	
		$where['userId']=$userId;
		$data = $this->getByWhere($where);

		if($data && $data['isApproved']==1){
			return true;
		}else{
  			return false;
		}
	}


}// END class 

?>