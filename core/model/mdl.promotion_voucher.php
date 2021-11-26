<?php

class mdl_promotion_voucher extends mdl_base
{	
	protected $tableName = 'promotion_voucher';

	public function openId_exist($openId)
	{	
		$where['openId']=$openId;
		return $this->getByWhere($where);
	}
}

?>