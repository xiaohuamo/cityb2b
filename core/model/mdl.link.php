<?php

class mdl_link extends mdl_base
{

	protected $tableName = '#@_link';

	public function get ($id)
	{
		return $this->db->selectOne(null, $this->tableName, "id='$id'");
	}

	public function getList ($column = null, $where = null, $order = null, $cnt = 10)
	{
		return $this->db->toArray($this->db->select($column, $this->tableName, $where, $order, "0, $cnt"));
	}

	public function getListSql ($column = null, $where = null, $order = null)
	{
		return $this->db->getSelectSql($column, $this->tableName, $where, $order);
	}

	public function getListBySql ($sql)
	{
		return $this->db->toArray($this->db->query($sql));
	}

	public function add ($data)
	{
		$this->db->insert($data, $this->tableName);
		return $this->db->insert_id();
	}

	public function update ($data, $id)
	{
		return $this->db->update($data, $this->tableName, "id='$id'");
	}

	public function delete ($id)
	{
		return $this->db->delete($this->tableName, "id='$id'");
	}

	public function getOrdinalForInsert ()
	{
		if ($ro = $this->db->selectOne('ordinal', $this->tableName, null, 'ordinal desc')) return $ro['ordinal'] + 10;
		else return 10;
	}

}

?>