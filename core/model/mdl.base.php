<?php

abstract class mdl_base {

	protected $lang = false;
	protected $tableName = null;
	protected $dbnum = 1; //用哪个数据库连接

	public function mdl_base () {
		$dbname = 'db'.( $this->dbnum > 1 ? $this->dbnum : '' );
		$this->db = & $GLOBALS['system']->$dbname;
		if ( ! $this->db ) {
			$GLOBALS['system']->$dbname = $this->db = new db( $this->dbnum );
		}
	}

	public function get( $id ) {
		$where = "id='$id'";
		
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		
		return $this->db->selectOne( null, $this->tableName, $where );
	}

	public function getByWhere( $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		
		return $this->db->selectOne( null, $this->tableName, $where );
	}

	public function getList( $column = null, $where = null, $order = null, $cnt = null ) {
		$cnt = (int)$cnt;
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->toArray( $this->db->select( $column, $this->tableName, $where, $order, $cnt > 0 ? "0, $cnt" : '' ) );
	}

	public function getListSql ( $column = null, $where = null, $order = null ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			if ( isset( $where ) ) {
				if ( is_array( $where ) ) {
					$where[] = "lang='".$this->getLang()."'";
				}
				else {
					$where .= " and lang='".$this->getLang()."'";
				}
			}
			else {
				$where = "lang='".$this->getLang()."'";
			}
		}
		return $this->db->getSelectSql( $column, $this->tableName, $where, $order );
	}

	public function getListBySql ( $sql ) {
		return $this->db->toArray( $this->db->query( $sql ) );
	}

	public function insert ( $data ) {

		if ( ! isset( $data['lang'] ) && $this->lang ) {
			$data['lang'] = $this->getLang();
		}

		$this->db->insert( $data, $this->tableName );
		return $this->db->insert_id();
	}


	public function update( $data, $id ) {
		if ( ! isset( $data['lang'] ) && $this->lang ) {
			$data['lang'] = $this->getLang();
		}
		$where = "id='$id'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		
		return $this->db->update( $data, $this->tableName, $where );
	}

	public function updateByWhere( $data, $where ) {
		if ( ! isset( $data['lang'] ) && $this->lang ) {
			$data['lang'] = $this->getLang();
		}
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		
		return $this->db->update( $data, $this->tableName, $where );
	}

	public function updateFieldByStep( $fieldName, $step, $id ) {
		$step = (int)$step;
		return $this->db->execute("update ".$this->tableName." set $fieldName=".( $step >= 0 ? "$fieldName+$step" : "$fieldName-$step" )." where id=$id");
	}

	public function delete ( $id ) {
		$where = "id='$id'";
		if ( $this->lang ) {
			$where .= " and lang='".$this->getLang()."'";
		}
		return $this->db->delete( $this->tableName, $where );
	}

	public function deleteByWhere( $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		
		return $this->db->delete( $this->tableName, $where );
	}

	public function getMax( $field, $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->getMax( $this->tableName, $field, $where );
	}

	public function getSum( $field, $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->getSum( $this->tableName, $field, $where );
	}

	public function getOne( $field, $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->getOne( $this->tableName, $field, $where );
	}

	public function getCount( $where ) {
		if ( ! isset( $where['lang'] ) && $this->lang ) {
			$where['lang'] = $this->getLang();
		}
		return $this->db->getCount( $this->tableName, $where );
	}

	public function begin() {
		return $this->db->begin();
	}

	public function rollback() {
		return $this->db->rollback();
	}

	public function commit() {
		return $this->db->commit();
	}

	public function error() {
		return $this->db->error();
	}

	public function errno() {
		return $this->db->errno();
	}

	protected function getLang() {
		if ( $GLOBALS['gbl_con'] == 'admin' ) {
			//$lang = trim( $_SESSION['admin_lang'] );
			if ( isset( $GLOBALS['admin_lang'] ) ) {
				$lang = $GLOBALS['admin_lang'];
			}
			else {
				$lang = trim( $_COOKIE['admin_lang'] );
			}
		}
		else {
			//$lang = trim( $_SESSION['lang'] );
			if ( isset( $GLOBALS['lang'] ) ) {
				$lang = $GLOBALS['lang'];
			}
			else {
				$lang = trim( $_COOKIE['lang'] );
			}
		}
		return $lang;
	}


	public function getOrCounditionSqlStr($key,$value_array)
	{
		foreach ($value_array as $k => $value) {
			$value_array[$k] = $key."='".$value."'";
		}

		return "(".join(' or ', $value_array).")";
	}


}

?>