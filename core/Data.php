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
	private $connected = false;
	
	/**
	 * make this a singleton
	 * 
	 * @param unknown_type $data
	 * @return unknown_type
	 */
	//public function __construct($data=false)
	private function __construct()
	{
		global $db;
		global $Data_instance;
		if($Data_instance)
		{
			return $Data_instance;
		}
		else
		{
			$Data_instance &= $this;
		}
		$this->db = $db;
	}
	
	public function instance()
	{
		if (!isset(self::$data_instance)) {
            $c = __CLASS__;
            self::$data_instance = new $c;
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
		if(is_array($conditions) && is_array($conditions[0]))
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
	public function select($tablename, $fieldnames="*", $conditions=false, $order=false, $desc=false, $count=false, $start='0')
	{
		// lazily connect
//		if(!$this->connected)
//		{
//			$this->connect();
//		}
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
			$limit = ' LIMIT ' . mysql_real_escape_string($start, $this->db) . ', ' . mysql_real_escape_string($count, $this->db);
		}
		else
		{
			$limit = '';
		}
		$sql = "SELECT $fieldnames FROM `$tablename` WHERE $conditions$order$limit";

		echo $sql;
//		mysql_query($sql, $this->db);
	}
	
	/**
	 * convenience methods
	 * might be better to put these into the subclasses?
	 * 
	 * These could be optimised, too (by not using $this->select, which has extra code to create the query from many arguments)
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
//		mysql_query($sql, $this->db);
		echo $sql;
		
	}
	
	public function findAll($tablename, $count=0, $page=0)
	{
		// lazy connect happens in select
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
		
		echo implode($sql, "");
	}
	
	/**
	 * This one is just for databases
	 * Will be handy for when the developer wants to write custom queries for advanced
	 * features or performance optimisations
	 * 
	 * @param $sql
	 * @return unknown_type
	 */
	private function query($sql)
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
		$this->db->query($sql);
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
		echo $sql;
		
//		mysql_query($sql, $this->db);
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
		$sql = "INSERT INTO `$tablename` ('" . implode("', '", $fieldnames) . "') VALUES ('" . implode("', '", $values) . "');";
		
//		mysql_query($sql, $this->db);
		echo $sql;
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
		
		echo $sql;
//		mysql_query($qry, $this->db);
	}
}

/**
 * My inspiration:
 * 
 * @author http://www.phpro.org/classes/PDO-CRUD.html
 *
 */
class crud
{

    private $db;

    /**
     *
     * Set variables
     *
     */
    public function __set($name, $value)
    {
        switch($name)
        {
            case 'username':
            $this->username = $value;
            break;

            case 'password':
            $this->password = $value;
            break;

            case 'dsn':
            $this->dsn = $value;
            break;

            default:
            throw new Exception("$name is invalid");
        }
    }

    /**
     *
     * @check variables have default value
     *
     */
    public function __isset($name)
    {
        switch($name)
        {
            case 'username':
            $this->username = null;
            break;

            case 'password':
            $this->password = null;
            break;
        }
    }

        /**
         *
         * @Connect to the database and set the error mode to Exception
         *
         * @Throws PDOException on failure
         *
         */
        public function conn()
        {
            isset($this->username);
            isset($this->password);
            if (!$this->db instanceof PDO)
            {
                $this->db = new PDO($this->dsn, $this->username, $this->password);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        }


        /***
         *
         * @select values from table
         *
         * @access public
         *
         * @param string $table The name of the table
         *
         * @param string $fieldname
         *
         * @param string $id
         *
         * @return array on success or throw PDOException on failure
         *
         */
        public function dbSelect($table, $fieldname=null, $id=null)
        {
            $this->conn();
            $sql = "SELECT * FROM `$table` WHERE `$fieldname`=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }


        /**
         *
         * @execute a raw query
         *
         * @access public
         *
         * @param string $sql
         *
         * @return array
         *
         */
        public function rawSelect($sql)
        {
            $this->conn();
            return $this->db->query($sql);
        }

        /**
         *
         * @run a raw query
         *
         * @param string The query to run
         *
         */
        public function rawQuery($sql)
        {
            $this->conn();
            $this->db->query($sql);
        }


        /**
         *
         * @Insert a value into a table
         *
         * @acces public
         *
         * @param string $table
         *
         * @param array $values
         *
         * @return int The last Insert Id on success or throw PDOexeption on failure
         *
         */
        public function dbInsert($table, $values)
        {
            $this->conn();
            /*** snarg the field names from the first array member ***/
            $fieldnames = array_keys($values[0]);
            /*** now build the query ***/
            $size = sizeof($fieldnames);
            $i = 1;
            $sql = "INSERT INTO $table";
            /*** set the field names ***/
            $fields = '( ' . implode(' ,', $fieldnames) . ' )';
            /*** set the placeholders ***/
            $bound = '(:' . implode(', :', $fieldnames) . ' )';
            /*** put the query together ***/
            $sql .= $fields.' VALUES '.$bound;

            /*** prepare and execute ***/
            $stmt = $this->db->prepare($sql);
            foreach($values as $vals)
            {
                $stmt->execute($vals);
            }
        }

        /**
         *
         * @Update a value in a table
         *
         * @access public
         *
         * @param string $table
         *
         * @param string $fieldname, The field to be updated
         *
         * @param string $value The new value
         *
         * @param string $pk The primary key
         *
         * @param string $id The id
         *
         * @throws PDOException on failure
         *
         */
        public function dbUpdate($table, $fieldname, $value, $pk, $id)
        {
            $this->conn();
            $sql = "UPDATE `$table` SET `$fieldname`='{$value}' WHERE `$pk` = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
        }


        /**
         *
         * @Delete a record from a table
         *
         * @access public
         *
         * @param string $table
         *
         * @param string $fieldname
         *
         * @param string $id
         *
         * @throws PDOexception on failure
         *
         */
        public function dbDelete($table, $fieldname, $id)
        {
            $this->conn();
            $sql = "DELETE FROM `$table` WHERE `$fieldname` = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $stmt->execute();
        }
    } /*** end of class ***/

?>