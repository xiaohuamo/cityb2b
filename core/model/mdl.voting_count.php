<?php

class mdl_voting_count extends mdl_base
{
	const CLN_ID='id';
	const CLN_USERID='user_id';
	const CLN_ITEMID='item_id';
	const CLN_VOTED='voted';

	protected $tableName = '#@_voting_count';
	
	function vote($itemId,$userId){
		$where[self::CLN_USERID]=$userId;
		$where[self::CLN_ITEMID]=$itemId;

		if($this->entryExist($where)){
			//update
			$data[self::CLN_VOTED]=true;
			return $this->updateByWhere($data,$where);
		}else{
			//create\
			$where[self::CLN_VOTED]=true;
			return $this->insert($where);
		}
	}	

	function withdrawVote($itemId,$userId){
		$where[self::CLN_USERID]=$userId;
		$where[self::CLN_ITEMID]=$itemId;
		$data[self::CLN_VOTED]=flase;
		return $this->updateByWhere($data,$where);
	}

	function entryExist($where){

		$result=$this->getByWhere($where);

		if($result){
			return true;
		}else{
			return false;
		}
	}
	
}

?>