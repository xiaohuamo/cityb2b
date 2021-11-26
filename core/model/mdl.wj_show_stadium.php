<?php

class mdl_wj_show_stadium extends mdl_base
{

	protected $tableName = '#@_wj_show_stadium';

	public function getStadiumName($id)
	{
		$data = $this->get($id);
		return $data['name'];
	}
}

?>