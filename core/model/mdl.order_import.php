<?php

class mdl_order_import extends mdl_base
{

	protected $tableName = '#@_order_import';
	public function getByOrderId($orderid){
		$where = "orderId='$orderid'";
		
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		
		return $this->db->selectOne( null, $this->tableName, $where );

	}
}

?>