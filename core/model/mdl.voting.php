<?php

class mdl_voting extends mdl_base
{	
	const CLN_ID='id';
	const CLN_TITLE='title';
	const CLN_DESCRIPTION='description';
	const CLN_CREATEUSERID='create_user_id';
	const CLN_STATUS='status';

	const STATUS_OPEN=1;
	const STATUS_CLOSE=0;

	protected $tableName = '#@_voting';
	

	function createVoting($title,$description,$createUserId,$status=self::STATUS_CLOSE){
		$data[self::CLN_TITLE]=$title;
		$data[self::CLN_DESCRIPTION]=$description;
		$data[self::CLN_CREATEUSERID]=$createUserId;
		$data[self::CLN_STATUS]=$status;

		$id = $this->insert($data);
		return $id;
	}

	function updateVoting($data,$id){
		return $this->update($data,$id);
	}

	function getVoting($id){
		return $this->get($id);
	}

	function getVotingList(){
		$where['status']=0;
		return $this->getList(null,$where);
	}

	function getVotingListByUser($userId){
		$where[self::CLN_CREATEUSERID]=$userId;
		return $this->getList(null,$where);
	}
	function votingOpen($id){

	}
	function votingClose($id){

	}
	function votingDelete($id){

	}
}

?>