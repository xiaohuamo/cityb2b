<?php

class mdl_voting_item extends mdl_base
{
	const CLN_ID ='id';
	const CLN_GROUPID='group_id';
	const CLN_SORTID='sort_id';
	const CLN_CREATEUSERID='create_user_id';
	const CLN_PIC='pic';
	const CLN_TITLE='title';
	const CLN_DESCRIPTION='description';
	const CLN_DETAIL='detail';
	const CLN_VOTECOUNT='vote_count';
	const CLN_STATUS='status';
	const CLN_COUPONID='couponid';
	const CLN_VIDEO='video';

	const STATUS_OPEN=1;
	const STATUS_CLOSE=0;

	protected $tableName = '#@_voting_item';
	
	function createItem($data,$createUserId,$status=self::STATUS_CLOSE){
		$data[self::CLN_CREATEUSERID]=$createUserId;
		$data[self::CLN_STATUS]=$status;
		$data[self::CLN_VOTECOUNT]=100;

		return $this->insert($data);
	}

	function updateItem($data,$id){
		return $this->update($data,$id);
	}

	function getItem($id){
		return $this->get($id);
	}

	function getItemsByGroup($groupId){
		$where[self::CLN_GROUPID]=$groupId;

		$data=$this->getList(null,$where,self::CLN_SORTID,null);

		return $data;
	}
	function getVotingItemList($groupId,$userId=null){
		
		// SELECT *, FIND_IN_SET( vote_count, (
		// SELECT GROUP_CONCAT( vote_count
		// ORDER BY vote_count DESC ) 
		// FROM cc_voting_item where group_id=1)
		// ) AS rank
		// FROM cc_voting_item where group_id=1 order by sort_id

		$sql = "SELECT *,(vote_count + zhibo_count) as totalcount, FIND_IN_SET (".self::CLN_VOTECOUNT.",( SELECT GROUP_CONCAT(".self::CLN_VOTECOUNT." ORDER BY ".self::CLN_VOTECOUNT. " DESC )";
		$sql.= " FROM ".$this->tableName." where ".self::CLN_GROUPID."=".$groupId."  )) AS rank ";
		$sql.= "FROM ".$this->tableName." where ".self::CLN_GROUPID."=".$groupId."  ORDER BY totalcount DESC";
	//	var_dump($sql);exit;
		$data = $this->getListBySql($sql);

		if($userId){
			$mdl_voting_count = loadModel('voting_count');
			foreach ($data as $key => $value) {
				$w[mdl_voting_count::CLN_USERID]=$userId;
				$w[mdl_voting_count::CLN_ITEMID]=$value['id'];
				$w[mdl_voting_count::CLN_VOTED]=true;
				$data[$key]['voted']=$mdl_voting_count->entryExist($w);
				if($value['vote_count']==0)$data[$key]['rank']='-';
			}
		}

		return $data;
	}
	function itemOpen($id){

	}
	function itemClose($id){

	}
	function itemDelete($id){
		return $this->delete($id);
	}

	function updateCount($id,$num=0){
		if($num==0)return false;
		if($num>0)$sign=" + ";
		if($num<0)$sign=" - ";
		$sql ="UPDATE ".$this->tableName." SET ".self::CLN_VOTECOUNT." = ".self::CLN_VOTECOUNT.$sign.abs($num)." where id = ".$id;
		return $this->db->query($sql);
	}


}

?>