<?php

class mdl_ga extends mdl_base
{

	protected $tableName = '#@_google_analytics';

	function get() {
		return $this->db->selectOne( null, $this->tableName );
	}

	function update( $data ) {
		return $this->db->update( $data, $this->tableName ) ;
	}

}

?>