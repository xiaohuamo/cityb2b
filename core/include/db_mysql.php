<?php

class db
{

	private $dbhost;
	private $dbuser;
	private $dbpass;
	private $dbname;
	private $dbchar;
	private $link;
	private $result;
	private $pre;

	function __construct ( $dbnum = 1 )
	{
		global $CONFIG;

		$this->dbhost	= $CONFIG['DB_HOST'.($dbnum > 1 ? $dbnum : '')];
		$this->dbuser	= $CONFIG['DB_USER'.($dbnum > 1 ? $dbnum : '')];
		$this->dbpass	= $CONFIG['DB_PASS'.($dbnum > 1 ? $dbnum : '')];
		$this->dbname	= $CONFIG['DB_NAME'.($dbnum > 1 ? $dbnum : '')];
		$this->dbchar	= $CONFIG['DB_CHAR'.($dbnum > 1 ? $dbnum : '')];
		$this->pre		= $CONFIG['DB_PRE'.($dbnum > 1 ? $dbnum : '')];

		$this->connect();
	}

	function connect ()
	{
		@$this->link = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass, true) or die ("Connect Failed!");
		mysql_select_db ($this->dbname, $this->link) or die("连接数据库-> <font style='color:#F00'>" . $this->dbname . "</font> <-失败!");
		mysql_query("set names $this->dbchar", $this->link);
	}

	function query ($s)
	{
		
		$re = mysql_query (str_replace('#@_', $this->pre, $s), $this->link);

		if(!re) exit('SQL语句错误');
		$this->result = $re;

		return $re;
	}

	function execute ($s)
	{
		mysql_query (str_replace('#@_', $this->pre, $s), $this->link);
		$affected_rows = $this->affected_rows();
		if ($affected_rows > 0) return $affected_rows;
		else
		{
			if (self::error()) return false;
			else return true;
		}
	}

	function getSelectSql ($array = null, $tableName = '', $condition = null, $order = '', $limit = '')
	{
		$i = 0;
		$valueStr	= '';
		$whereStr	= '';

		if (empty($tableName))
		{
			return false;
		}
		if (is_array($array))
		{
			foreach ($array as $key=>$value)
			{
				$i++;
				$valueStr	.= ($i > 1 ? ', ' : '').'`'.$value.'`';
			}
		}
		else
		{
			$valueStr = $array;
		}
		$i = 0;
		if (is_array($condition))
		{
			foreach ($condition as $key=>$value)
			{
				$i++;
				$whereStr	.= ($i > 1 ? ' and ' : '').(empty($key) || is_numeric($key) ? $value : '`'.$key.'`'.'=\''.$value.'\'');
			}
		}
		else
		{
			if (!empty($condition)) $whereStr = $condition;
		}

		$sql = "select ".(empty($valueStr) ? '*' : $valueStr)." from $tableName".(empty($whereStr) ? '' : ' where '.$whereStr).(empty($order) ? '' : ' order by '.$order).(empty($limit) ? '' : ' limit '.$limit);
		//if($tableName =='#@_factory2c_list'){
		//var_dump($sql);
	//	}
		return $sql;
	}

	function select ($array = null, $tableName = '', $condition = null, $order = '', $limit = '')
	{
		if ($sql = $this->getSelectSql($array, $tableName, $condition, $order, $limit)) 
			return $this->query($sql);
	}

	function selectOne ($array = null, $tableName = '', $condition = null, $order = '')
	{
		$re	= $this->select($array, $tableName, $condition, $order, '1');
		if ($ro = $this->fetch_array($re))
		{
			return $ro;
		}
		return null;
	}

	function getSelectMultipleSql ($array = array(), $tableName = array(), $on = array(), $condition = null, $order = '', $limit = '')
	{
		$aliasPre	= 't';
		$valueStr	= '';
		$tableStr	= '';
		$whereStr	= '';
		$i			= 0;
		foreach ($array as $key=>$value)
		{
			if (!is_array($value)) $value = preg_split('/,[ ]?/', $value);
			foreach ($value as $subkey=>$sub)
			{
				$valueStr .= ($i > 0 ? ', ' : '')."{$aliasPre}{$key}.".((empty($sub) || $sub == '*') ? '*' : '`'.$sub.'`');
				if (!is_numeric($subkey)) $valueStr .= " as $subkey";
				$i++;
			}
		}
		foreach ($tableName as $key=>$value)
		{
			if ($key > 0)
			{
				$tableStr .= " left join $value $aliasPre{$key}";
				if (is_array($on)) $tmp = explode('=', $on[$key - 1]);
				else $tmp = explode('=', $on);
				foreach ($tmp as $sk=>$s) $tmp[$sk] = explode('#', $s);
				$tableStr .= " on $aliasPre".($tmp[0][0]).".".$tmp[0][1]."=$aliasPre{$tmp[1][0]}.".$tmp[1][1];
			}
			else
			{
				$tableStr .= "$value $aliasPre{$key}";
			}
		}
		$i = 0;
		if (is_array($condition))
		{
			foreach ($condition as $key=>$value)
			{
				$i++;
				$whereStr	.= ($i > 1 ? ' and ' : '');
				if (empty($key) || is_numeric($key))
				{
					$whereStr .= $value;
				}
				else
				{
					$tmp = explode('#', $key);
					$whereStr .= "$aliasPre".$tmp[0].".`".$tmp[1]."`='$value'";
				}
			}
		}
		else
		{
			if (!empty($condition))
			{
				$tmp = explode('=', $condition);
				$tmp[0] = explode('#', $tmp[0]);
				$whereStr = "$aliasPre".$tmp[0][0].".`".$tmp[0][1]."`=".$tmp[1];
			}
		}

		$sql = "select $valueStr from $tableStr".(empty($whereStr) ? '' : ' where '.$whereStr).(empty($order) ? '' : ' order by '.$order).(empty($limit) ? '' : ' limit '.$limit);
		return $sql;
	}

	function selectMultiple ($array = array(), $tableName = array(), $on = array(), $condition = null, $order = '', $limit = '')
	{
		if ($sql = $this->getSelectMultipleSql($array, $tableName, $on, $condition, $order, $limit)) return $this->query($sql);
	}

	function getInsertSql ($array = null, $tableName = '')
	{
		$keyStr		= '';
		$valueStr	= '';
		$i			= 0;

		if (empty($tableName))
		{
			return false;
		}
		if (is_array($array))
		{
			foreach ($array as $key=>$value)
			{
				$i++;
				$keyStr 	.= ($i > 1 ? ',' : '').'`'.$key.'`';
				$valueStr	.= ($i > 1 ? ',' : '').'\''.$value.'\'';
				//$valueStr	.= ($i > 1 ? ',' : '').'\''.(!get_magic_quotes_gpc() ? addslashes($value):$value).'\'';
			}

			$sql = "insert into $tableName ($keyStr) values ($valueStr)";
			return $sql;
		}
		else
		{
			return false;
		}
	}

	function insert ($array = null, $tableName = '')
	{
		if ($sql = $this->getInsertSql($array, $tableName)) return $this->execute($sql);
	}

	function getUpdateSql ($array = null, $tableName = '', $condition = null)
	{
		$i = 0;
		$valueStr	= '';
		$whereStr	= '';

		if (empty($tableName))
		{
			return false;
		}
		if (!is_array($array))
		{
			return false;
		}
		foreach ($array as $key=>$value)
		{
			$i++;
			$valueStr	.= ($i > 1 ? ', ' : '').'`'.$key.'`'.'=\''.$value.'\'';
			//$valueStr	.= ($i > 1 ? ', ' : '').'`'.$key.'`'.'=\''.(!get_magic_quotes_gpc() ? addslashes($value):$value).'\'';
		}
		$i = 0;
		if (is_array($condition))
		{
			foreach ($condition as $key=>$value)
			{
				$i++;
				$whereStr	.= ($i > 1 ? ' and ' : '').(empty($key) || is_numeric($key) ? $value : '`'.$key.'`'."='".$value."'");
			}
		}
		else
		{
			if (!empty($condition)) $whereStr = $condition;
		}

		$sql = "update $tableName set $valueStr".(empty($whereStr) ? '' : ' where '.$whereStr);
		//var_dump($sql);exit;
		return $sql;
	}

	function update ($array = null, $tableName = '', $condition = null)
	{
		if ($sql = $this->getUpdateSql($array, $tableName, $condition)) 
			
			return $this->execute($sql);
	}

	function getDeleteSql ($tableName = '', $condition = null)
	{
		$i = 0;
		$whereStr	= '';

		if (empty($tableName))
		{
			return false;
		}
		if (is_array($condition))
		{
			foreach ($condition as $key=>$value)
			{
				$i++;
				$whereStr	.= ($i > 1 ? ' and ' : '').(empty($key) || is_numeric($key) ? $value : $key."='".$value."'");
			}
		}
		else
		{
			if (!empty($condition)) $whereStr = $condition;
		}

		$sql = "delete from $tableName".(empty($whereStr) ? '' : ' where '.$whereStr);
		//var_dump($sql);exit;
		return $sql;
	}

	public function delete ($tableName = '', $condition = null)
	{
		if ($sql = $this->getDeleteSql($tableName, $condition)) return $this->execute($sql);
	}

	public function fetch_array ($re = null)
	{
		if (!$re)
			return mysql_fetch_array($this->result);
		else
			return mysql_fetch_array($re);
	}

	public function toArray ($re = null)
	{
		if ($re != null) $this->result = $re;
		$arr = array();
		while ($ro = $this->fetch_array()) $arr[] = $ro;
		return $arr;
	}

	function insert_id ()
	{
		return mysql_insert_id($this->link);
	}

	function cnt ($re = null)
	{
		if ($re != null) $this->result = $re;
		return $this->result == null?0:mysql_num_rows($this->result);
	}

	private function affected_rows ()
	{
		return mysql_affected_rows($this->link);
	}

	public function getMax ($tb, $cell, $condition)
	{
		$where = '';
		$i = 0;
		if (is_array($condition))
		{
			foreach ($condition as $key=>$value)
			{
				$i++;
				$where	.= ($i > 1 ? ' and ' : '').(empty($key) || is_numeric($key) ? $value : $key.'=\''.$value.'\'');
			}
		}
		else
		{
			if (!empty($condition)) $where = $condition;
		}

		$sql	= "select max($cell) as cnt from $tb";
		if ($where != '') $sql .= " where $where";
		$re		= $this->query($sql);
		$ro		= $this->fetch_array($re);
		return (int)$ro['cnt'];
	}

	public function getSum ($tb, $cell, $condition)
	{
		$where = '';
		$i = 0;
		if (is_array($condition))
		{
			foreach ($condition as $key=>$value)
			{
				$i++;
				$where	.= ($i > 1 ? ' and ' : '').(empty($key) || is_numeric($key) ? $value : $key.'=\''.$value.'\'');
			}
		}
		else
		{
			if (!empty($condition)) $where = $condition;
		}

		$sql	= "select sum($cell) as cnt from $tb";
		if ($where != '') $sql .= " where $where";
		
		$re		= $this->query($sql);
		$ro		= $this->fetch_array($re);
		return (int)$ro['cnt'];
	}

	public function getOne ($tb, $cell, $condition)
	{
		$where = '';
		$i = 0;
		if (is_array($condition))
		{
			foreach ($condition as $key=>$value)
			{
				$i++;
				$where	.= ($i > 1 ? ' and ' : '').(empty($key) || is_numeric($key) ? $value : $key.'=\''.$value.'\'');
			}
		}
		else
		{
			if (!empty($condition)) $where = $condition;
		}

		$sql	= "select $cell as cell from $tb";
		if ($where != '') $sql .= " where $where";
		$sql	.= " limit 1";
		$re		= $this->query($sql);
		if ($ro = $this->fetch_array($re))
		{
			return $ro['cell'];
		}
		else
		{
			return null;
		}
	}

	public function getCount ($tb, $condition)
	{
		$where = '';
		$i = 0;
		if (is_array($condition))
		{
			foreach ($condition as $key=>$value)
			{
				$i++;
				$where	.= ($i > 1 ? ' and ' : '').(empty($key) || is_numeric($key) ? $value : $key.'=\''.$value.'\'');
			}
		}
		else
		{
			if (!empty($condition)) $where = $condition;
		}

		$sql	= "select count(*) as cnt from $tb";
		if ($where != '') $sql .= " where $where";
		
		$re		= $this->query($sql);
		if ($ro = $this->fetch_array($re))
		{
			return $ro['cnt'];
		}
		else
		{
			return 0;
		}
	}

	public function getCountBySql ($sql)
	{
		$re = $this->query($sql);
		if ($ro = $this->fetch_array($re))
			return $ro['cnt'];
		else
			return null;
	}

	public function begin() {
		return $this->query('BEGIN');
	}

	public function rollback() {
		return $this->query('ROLLBACK');
	}

	public function commit() {
		return $this->query('COMMIT');
	}

	public function error ()
	{
		return mysql_error($this->link);
	}

	public function errno ()
	{
		return mysql_errno($this->link);
	}

	public function free ()
	{
		@mysql_free_result($this->result);
	}

	public function close()
	{
		mysql_close($this->link);
	}
	
	public function __destruct ()
	{
		if ( !empty($this->result) ) $this->free();
		mysql_close($this->link);
	}

}

?>