<?php

class mdl_subscribeclass extends mdl_base
{

	protected $tableName = '#@_subscribeclass';

	public function getList( $where, $order ) {
		return $this->db->toArray( $this->db->select( null, $this->tableName, $where, $order ) );
	}

	public function add ($data)
	{
		$this->db->insert($data, $this->tableName);
		return $this->db->insert_id();
	}

}

?>