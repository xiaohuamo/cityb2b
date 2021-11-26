<?php

class mdl_wj_busi_pay_setting_application extends mdl_base
{

	protected $tableName = '#@_wj_busi_pay_setting_application';

	public function isPaymentSelfManage($businessId)
	{	
		$isPaymentSelfManage = false;

		$where['userId']=$businessId;
		$where['isApproved']=1;
		$business_user=$this->getByWhere($where);

		if($business_user!=null){
			$isPaymentSelfManage=true;
		}

		return $isPaymentSelfManage;
	}

	public function getBusinessPaypalEmail($businessId)
	{
		$where['userId']=$businessId;
		$business_user=$this->getByWhere($where);
		return  $business_user['paypal_email'];
	}


}

?>