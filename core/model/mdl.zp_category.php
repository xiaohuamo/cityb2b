<?php

class mdl_zp_category extends mdl_base
{

	protected $tableName = '#@_zp_category';

	protected $dbnum = 3;
	
	public function getParents( $id ) {
		$cat = $this->get( (int)$id );
		if ( !$cat ) return null;
		if ( !$cat['map'] ) return array( $cat );

		return $this->getList( null, array( "id in (".str_replace( '-', ',', $cat['map'] ).")" ), 'map asc' );
	}

	public function getChild ( $pid = 0 ) {
		static $catArr = array();

		$where = array();
		$where['parentId'] = (int)$pid;
		if ( $this->lang ) $where['lang'] = $this->getLang();

		$re = $this->db->select( '*', $this->tableName, $where, 'sortnum asc' );
		while ( $ro = $this->db->fetch_array( $re ) ) {
			$catArr[]	= array(
				'id'					=> $ro['id'],
				'sortnum'				=> $ro['sortnum'],
				'parentId'				=> $ro['parentId'],
				'name'					=> $ro['name'],
				'map'					=> $ro['map'],
				'level'					=> count( explode( '-', $ro['map'] ) ),
				'hasSon'				=> $this->getCount( array( 'parentId' => $ro['id'] ) ) > 0 ? 1 : 0,
			);
			$this->getChild( $ro['id'] );
		}

		if ( is_array( $catArr ) && count( $catArr ) ) return $catArr;
		else return null;
	}

}

?>