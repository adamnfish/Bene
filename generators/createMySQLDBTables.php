<?php

class CreateMySQLDBTables
{
	private $jdos;
	
	public function __construct()
	{
		
	}
	
	/**
	 * Takes a Data class (MySQL) and the jdos fragment for the same
	 * generates the database tables accordingly
	 * @param DataSource $dataSource
	 * @param JDOS Array $jdos
	 * @return Bool
	 */
	public function createTables($dataSource, $jdos)
	{
		$success = true;
		foreach($jdos["schema"] as $tablename => $fields)
		{
			$qry = $this->generateQuery($tablename, $fields);
			$worked = $dataSource->query($qry);
			$success = $success && $worked;
//			var_dump($qry);
			echo $tablename . " " . var_export(!!$worked, true) . "\n";
			if(false == $worked)
			{
				echo mysql_error() . "\n";
				echo "\n" . $qry . "\n";
			}
		}
		return $success;
	}
	
	private function generateQuery($tablename, $fields)
	{
		$qry = array();
		$qry[] = $this->queryTop($tablename);
		foreach($fields as $fieldname => $field)
		{
			$qry[] = $this->queryField($fieldname, $field);
		}
		$qry[] = $this->queryIndices($fields);
		$qry[] = $this->queryBottom();
		return implode("", $qry);
	}
	
	private function queryTop($tablename)
	{
		return "CREATE TABLE IF NOT EXISTS `$tablename` (\n";
	}
	
	private function queryBottom($auto_increment=1)
	{
		return ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=$auto_increment ;";
	}
	
	private function queryField($fieldname, $field)
	{
		$qry = "`$fieldname` ";
		switch($field['type'])
		{
			case 'int':
				$qry .= 'int(10) ';
			break;
			case 'tinyint':
				$qry .= 'tinyint(3) ';
			break;
			case 'mediumint':
				$qry .= 'mediumint(8) ';
			break;
			case 'char':
				$qry .= "char({$field['length']}) ";
			break;
			case 'varchar':
				$qry .= "varchar({$field['length']}) ";
			break;
			default:
				$qry .= "{$field['type']} ";
			break;
		}
		if($field['unsigned'])
		{
			$qry .= "unsigned ";
		}
		if(false === $field['null'] || "NOT NULL" === strtolower($field['null']) || $field['required'])
		{
			// NOT NULL
			$qry .= "NOT NULL ";
		}
		if($field['default'])
		{
			if('timestamp' === $field['type'] && "CURRENT_TIMESTAMP" === $field['default'])
			{
				$qry .= "default " . $field['default'];
			}
			else
			{
				$qry .= "default '" . mysql_real_escape_string($field['default']) . "'";
			}
		}
		if($field['auto_increment'])
		{
			$qry .= 'auto_increment';
		}
		$qry .= ",\n";
		return $qry;
	}
	
	private function queryIndices($fields)
	{
		// loop through fields, find primary
		$primary;
		$unique = array();
		$indices = array();
		$qry = array();
		foreach($fields as $fieldname => $field)
		{
			if($field['primary'])
			{
				$primary = $fieldname;
			}
			if("unique" === strtolower($field['index']))
			{
				$unique[] = $fieldname;
			}
			else if($field['index'])
			{
				$indices[] = $fieldname;
			}
		}
		$qry[] = "PRIMARY KEY (`$primary`)";
		foreach($unique as $fieldname)
		{
			$qry[] = "UNIQUE KEY `$fieldname` (`$fieldname`)";
		}
		foreach($indices as $fieldname)
		{
			$qry[] = "KEY `$fieldname` (`$fieldname`)";
		}
		return implode(",\n", $qry) . "\n";
	}
}

?>