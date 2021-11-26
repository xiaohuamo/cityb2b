<?php

class mdl_pickhis extends mdl_base
{

	protected $tableName = '#@_pickhis';

	public function getList ($pid)
	{
		//echo $this->db->getSelectMultipleSql(array(null, array('userName' => 'name')), array($this->tableName, '#@_user'), "0#admin_id=1#id", "0#pid='$pid'", 't0.pick_time desc', '10');exit;
		return $this->db->toArray($this->db->selectMultiple(array(null, array('userName' => 'name')), array($this->tableName, '#@_user'), array("0#admin_id=1#id"), "0#pid='$pid'", 't0.pick_time desc', '10'));
	}

	public function add ($data)
	{
		return $this->db->insert($data, $this->tableName);
	}

	public function delete ($pid)
	{
		return $this->db->delete($this->tableName, array('pid' => $pid));
	}

}

?>