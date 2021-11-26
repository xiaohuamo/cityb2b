<?php

class mdl_infopic extends mdl_base
{

	protected $tableName = '#@_infopic';

	public function getList( $info_id ) {
		return $this->db->toArray( $this->db->select( null, $this->tableName, "infoId='$info_id'" ) );
	}

	public function deleteByInfoId( $info_id ) {
		return $this->db->delete( $this->tableName, "infoId='$info_id'" );
	}

}

?>