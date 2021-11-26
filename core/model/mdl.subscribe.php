<?php

class mdl_subscribe extends mdl_base
{

	protected $tableName = '#@_subscribe';

	public function getByEmail( $email ) {
		return $this->db->selectOne( null, $this->tableName, "email='$email'" );
	}

	public function getList( $where, $order ) {
		return $this->db->toArray( $this->db->select( null, $this->tableName, $where, $order ) );
	}

	public function add ($data)
	{
		$this->db->insert($data, $this->tableName);
		return $this->db->insert_id();
	}

	public function deleteByEmail ($email)
	{
		return $this->db->delete($this->tableName, "email='$email'");
	}

}

?>