<?php

class mdl_wj_pc_weixin_login_temp_info extends mdl_base
{

	protected $tableName = '#@_wj_pc_weixin_login_temp_info';

	//create an empty placeholder and return id as key
	public function init()
	{
			$data=array(
				'userId'=>0,
				'createTime'=>time()	
			);

			$key=$this->insert($data);

			return $key;
	}

	//update the placeholder with a userID and login page listener will do the rest
	public function notify($key,$userId)
	{
		$data =array(
			'userId'=>$userId
		);

		return $this->update($data,$key);
	}

	//
	public function getUserId($key)
	{
		$data = $this->get($key);
		return $data['userId'];
	}
}

?>