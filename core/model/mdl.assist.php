<?php

class mdl_assist extends mdl_base
{

	protected $tableName = '#@_friend_assist';

	private $id;

	private $user_id;

	/**
	 * total assist collected from friend;
	 */
	private $total;

	protected $tableName_log = 'cc_friend_assist_log';

	/**
	 *  player who receive assist
	 */
	private $base_user_id;

	/**
	 * player who assist
	 */
	private $assist_user_id;

	private $gen_date;

	public function overwriteTable($table,$table_log)
	{
		$this->tableName=$table;
		$this->tableName_log=$table_log;
	}

	public function playGame($base_user_id)
	{
		if($this->isAlreadyInGame($base_user_id))return false;

		$data['user_id']=$base_user_id;
		$data['total']=0;
		$data['gen_date']=time();

		return $this->insert($data);
	}

	public function isAlreadyInGame($base_user_id)
	{	
		$where['user_id']=$base_user_id;

		$data=$this->getByWhere($where);

		return $data;
	}


	public function assist($base_user_id,$assist_user_id)
	{
		if($this->isAlreadyAssist($base_user_id,$assist_user_id))return false;

		$data=array();
		$data['base_user_id']=$base_user_id;
		$data['assist_user_id']=$assist_user_id;
		$data['gen_date']=time();

		$id=$this->db->insert($data,$this->tableName_log);

		$this->assistCountUpdate($base_user_id);

	}

	public function isAlreadyAssist($base_user_id,$assist_user_id)
	{
		$where['base_user_id']=$base_user_id;
		$where['assist_user_id']=$assist_user_id;

		$data=$this->db->selectOne(null,$this->tableName_log, $where);

		return $data;
	}



	public function totalAssistCount($base_user_id)
	{
		$userData = $this->isAlreadyInGame($base_user_id);

		return $userData['total'];
	}

	public function assistCountUpdate($base_user_id,$count=1)
	{
		$userData = $this->isAlreadyInGame($base_user_id);


		if(!$userData)return false;

		$data['total'] = intval($userData['total'])+intval($count);

		return $this->update($data,$userData['id']);
	}


	public function leaderboard($limit=20)
	{
		
		$list =  $this->getList(null,null,'total desc , gen_date',$limit);


		$mdl_user = loadModel('user');

		foreach ($list as $key => $value) {
			$list[$key]['user_name']=$mdl_user->getUserDisplayName($value['user_id']);
		}

		return $list;

	}

	public function getRanking($base_user_id)
	{
		//get my total
		$data = $this->getByWhere(array('user_id'=>$base_user_id));
		$total = $data['total'];
		$gen_date = $data['gen_date'];


		$where1[]="total > $total";
		$count1= $this->getCount($where1);

		$where2[]="(total = $total and gen_date <$gen_date)";
		$count2= $this->getCount($where2);

		$ranking = intval($count1)+intval($count2)+1;

		return $ranking;

	}

	public function totalPlayer()
	{	
		return $this->db->getCount($this->tableName_log,true);
	}
}

?>