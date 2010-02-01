<?php
/*
 * This class uses PDO under the hood, to talk to whichever database the developer uses
 * 
 * This basically needs to create a CRUD implementation over the top of PDO
 * 
 * http://www.phpro.org/classes/PDO-CRUD.html
 * 
 * But maybe extended a bit
 */
/*
 * Extensions to this for each data source
 * Each extension should be a singleton
 * These data sources are passed to the mapper's constructor, or used directly for more 'custom' foo (eg, specific queries, or db level stuff)
 */
$Data_instance;
class Data
{
	private static $data_instance;
	
	private $db;
	private $E;
	private $connected = false;// not used
	
	/**
	 * make this a singleton
	 * 
	 * @param unknown_type $data
	 * @return unknown_type
	 */
	//public function __construct($data=false)
	private function __construct($database)
	{
		global $db;
		$this->db = $database ? $database : $db;

		$this->E = Errors::instance();
	}
	
	public function instance($db=false)
	{
		if (!isset(self::$data_instance))
		{
            $c = __CLASS__;
            self::$data_instance = new $c($db);
        }

        return self::$data_instance;
	}
	
	/**
	 * Implements the actual database connection (creates PDO)
	 * Not needed in FCC because a database connection exists anyway
	 * This will be done lazily
	 * @return unknown_type
	 */
	private function connect()
	{
		isset($this->data->connection->username);
		isset($this->data->connection->password);
		if (!$this->db instanceof PDO)
		{
			$this->db = new PDO($this->data->connection->dsn, $this->data->connection->username, $this->data->connection->password);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$this->connected = true;
		}
	}
	
	private function conditions($conditions)
	{
		if(is_array($conditions) && isset($conditions[0]) && is_array($conditions[0]))
		{
			// build conditions string from array - otherwise we'll assume it's a string for performance's sake
			$query_conditions = array();
			foreach($conditions as $condition)
			{
				array_push($query_conditions, $this->arrayToCondition($condition));
			}
			$conditions = implode(' AND ', $query_conditions);
		}
		elseif(is_array($conditions))
		{
			$conditions = $this->arrayToCondition($conditions);
		}
		return $conditions;
	}
	
	private function arrayToCondition($conditionArray)
	{
		if(3 === count($conditionArray))
		{
			$condition = '`' . $conditionArray[0] . '` ' . $conditionArray[1] . " '" . mysql_real_escape_string($conditionArray[2], $this->db) . "'";
		}
		elseif(2 === count($conditionArray))
		{
			$condition = '`' . $conditionArray[0] . "` = '" . mysql_real_escape_string($conditionArray[1], $this->db) . "'";
		}
		elseif(0 === count($conditionArray))
		{
			return "true";
		}
		return $condition;
	}
	
	/*
	 *	The condition format will be suitable for APIs as well (make the conditions work as the parameters)
	 *	Conditions as array:
	 *	array(
	 *		array(
	 *			"fieldname",
	 *			"value",
	 *			"operator"// defaults to =
	 *		),
	 *		array(
	 *			"id",
	 *			7,
	 *			"<"
	 *		),
	 *		array(
	 *			"active",
	 *			1
	 *		)
	 *	)
	 *	or string
	 *	"`id` < 7 and `active` = 1"
	 *
	 *	fieldnames must be string or array
	 *
	 *	TODO change db->quote for proper, secure, faster db->prepare
	 */
	public function select($tablename, $fieldnames="*", $conditions=false, $order=false, $desc=false, $count=0, $page=1)
	{
		// lazily connect
//		if(!$this->connected)
//		{
//			$this->connect();
//		}
		$page -= 1;
		$start = (--$page * $count);
		if(is_array($fieldnames))
		{
			$fieldnames = implode(", ", $fieldnames);
		}
		
		$conditions = $this->conditions($conditions);
		
		if($order)
		{
			$order = ' ORDER BY ' . mysql_real_escape_string($order, $this->db);
			if($desc)
			{
				$order .= ' DESC';
			}
		}
		else
		{
			$order = '';
		}
		
		if($count)
		{
			if($page)
			{
				$limit = " LIMIT $start , $count;";
			}
			else
			{
				$limit = " LIMIT $count;";
			}
		}
		else
		{
			$limit = ";";
		}
		
		$sql = "SELECT $fieldnames FROM `$tablename` WHERE $conditions$order$limit";

//		echo $sql;

		mysql_query($sql, $this->db);		
		$rslt = mysql_query($sql, $this->db);
		$data = array();
		while($row = mysql_fetch_assoc($rslt))
		{
			$data[] = $row;
		}
		if(false === $rslt)
		{
			$this->E->throwErr(2, mysql_error($this->db));
		}
		else
		{
			return $data;
		}
	}
	
	/**
	 * convenience methods
	 * might be better to put these into the subclasses?
	 * 
	 * @param $table
	 * @param $id
	 * @param $id_fieldname
	 * @return unknown_type
	 */
	public function find($tablename, $id, $id_fieldname='id')
	{
		// lazy connect happens in select
//		return $this->select($table, '*', array($id_fieldname, $id));
		
		$sql = "SELECT * FROM `$tablename` WHERE `$id_fieldname` = '" . mysql_real_escape_string($id, $this->db) . "';";

		//		echo $sql;
		$rslt = mysql_query($sql, $this->db);
		if(false === $rslt)
		{
			$this->E->throwErr(2, mysql_error($this->db));
		}
		else
		{
			return mysql_fetch_assoc($rslt);
		}
		
	}
	
	/**
	 * Finds all the records in a table
	 * This method has pagination controls
	 * Pagination is a zero-based index
	 * 
	 * @param String $tablename
	 * @param Int $count
	 * @param Int $page
	 * @return unknown_type
	 */
	public function findAll($tablename, $count=0, $page=1)
	{
		// lazy connect happens in select
		$page -= 1;
		$start = ($page * $count);
		
		$sql = array();
		$sql[] = "SELECT * FROM `$tablename`";
		if($count)
		{
			if($page)
			{
				$sql[] = " LIMIT $start , $count;";
			}
			else
			{
				$sql[] = " LIMIT $count;";
			}
		}
		else
		{
			$sql[] = ";";
		}
		
//		echo implode($sql, "");
		$rslt = mysql_query(implode($sql, ""), $this->db);
		$data = array();
		while($row = mysql_fetch_assoc($rslt))
		{
			$data[] = $row;
		}
		if(false === $rslt)
		{
			$this->E->throwErr(2, mysql_error($this->db));
		}
		else
		{
			return $data;
		}
	}
	
	/**
	 * This one is just for databases
	 * Will be handy for when the developer wants to write custom queries for advanced
	 * features or performance optimisations
	 * 
	 * @param $sql
	 * @return unknown_type
	 */
	public function query($sql)
	{
		// lazily connect
//		if(!$this->connected)
//		{
//			$this->connect();
//		}
		
		// put code here to try and sniff to try and get the type of query
		// select based, insert based, etc
		// or even something like create database!
		// determine what to return accordingly
		// because otherwise, this isn't dramatically useful
		// $this->db->query($sql);
		$rslt = mysql_query($sql, $this->db);
		if(false === $rslt)
		{
			// TODO make this more useful, eg using Errors class
			$this->E->throwErr(2, mysql_error($this->db));
		}
		else
		{
			// TODO
			// try and detect the type of $rslt
			return $rslt;
		}
	}
	
	public function update($tablename, $data, $conditions, $limit=0)
	{
		// lazily connect
//		if(!$this->connected)
//		{
//			$this->connect();
//		}
		$sql = "UPDATE `$tablename` SET";
		$i = 0;
		foreach($data as $field => $value)
		{
			if($i)
			{
				$sql .= ",";
			}
			$i++;
			$sql .= " `$field` = '" . mysql_real_escape_string($value, $this->db) . "'";
		}
		$conditions = $this->conditions($conditions);
		$limit = $limit ? " LIMIT $limit;" : ";";
		$sql .= " WHERE $conditions$limit";
		
//		echo $sql;
		$rslt = mysql_query($sql, $this->db);
		if(false === $rslt)
		{
			$this->E->throwErr(2, mysql_error($this->db));
			return false;
		}
		return mysql_affected_rows($this->db);
	}
	
	public function insert($tablename, $data)
	{
		// lazily connect
//		if(!$this->connected)
//		{
//			$this->connect();
//		}
		$fieldnames = array_keys($data);
		$values = array_values($data);
		for($i = 0; $i < count($values); $i++)
		{
			$values[$i] = mysql_real_escape_string($values[$i], $this->db);
		}
		$sql = "INSERT INTO `$tablename` (`" . implode("`, `", $fieldnames) . "`) VALUES ('" . implode("', '", $values) . "');";
//		echo $sql;
		$rslt = mysql_query($sql, $this->db);
		if(false === $rslt)
		{
			$this->E->throwErr(2, mysql_error($this->db), 'mysql error', 0, 2);
			return false;
		}
		return mysql_insert_id($this->db);
		// should return the id
	}
	
	public function delete($tablename, $conditions, $limit=0)
	{
		// lazily connect
//		if(!$this->connected)
//		{
//			$this->connect();
//		}
		$conditions = $this->conditions($conditions);
		$limit = $limit ? " LIMIT $limit;" : ";";
		$sql = "DELETE FROM `$tablename` WHERE $conditions$limit";
		
//		echo $sql;
		$rslt = mysql_query($sql, $this->db);
		if(false === $rslt)
		{
			$this->E->throwErr(2, mysql_error($this->db), 'mysql error', 0, 2);
			return false;
		}
		return mysql_affected_rows($this->db);
	}
}

?>