<?php

class mdl_products extends mdl_base
{

	protected $tableName = '#@_products';

	function updateHits( $id ) {
		$id = (int)$id;
		return $this->db->query("update {$this->tableName} set hits=hits+1 where id='{$id}'");
	}

	function updateBuy( $id ) {
		$id = (int)$id;
		return $this->db->query("update {$this->tableName} set buy=buy+1 where id='{$id}'");
	}

}

?>