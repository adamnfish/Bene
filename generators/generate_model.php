<?php
/**
 * Quick thrown together page for generating FCC User Prefs model classes
 * 
 * No.
 * I'd rather generate it from the database, really
 */

require_once("../core/Data.php");
require_once("../core/Error.php");
require_once("../core/Errors.php");
require_once("../core/Object.php");
require_once("../core/Validator.php");
require_once("../core/Form.php");
require_once("../core/Test.php");

$db = mysql_connect("127.0.0.1", "root", "");
mysql_select_db("fcc_prefs_dev", $db);
		
class GenerateModels
{
	private $data;
	private $E;
	
	public function __construct()
	{
		$this->E = Errors::instance(3);
		
		$this->db = Data::instance();
	}
	
	public function autoGenerate($dir, $included_tables=false, $excluded_tables=false, $stubs=false)
	{
		if(is_string($included_tables))
		{
			$included_tables = array($included_tables);
		}
		if(is_string($excleded_tables))
		{
			$excleded_tables = array($excleded_tables);
		}
		$tables = $this->getTables();
		$done = array();
		foreach($tables as $table)
		{
			if(is_array($excluded_tables))
			{
				if(in_array($table, $excluded_tables))
				{
					continue;
				}
			}
			if(is_array($included_tables))
			{
				if(false === in_array($table, $included_tables))
				{
					continue;
				}
			}
			$info = $this->getTableInfo($table);
			$model = $this->generateModel($table, $info);
			$path = $dir . "/" . $this->modelName($table) . "_core.php";
			$this->writeFile($path, $model);
			$done[] = $path;
			if($stubs)
			{
				$stub_name = $this->modelName($table);
				$stub_source = $this->modelHead($stub_name, $stub_name . "_core")
				. $this->modelFoot();
				$stub_path = dirname($dir);
				$stub_file = $stub_path . "/" . $this->modelName($stub_name) . ".php";;
				$this->writeFile($stub_file, $stub_source);
			}
		}
		echo "<p>Written model files:\n\n</p>";
		foreach($done as $modelFile)
		{
			echo "$modelFile<br />\n";
		}
	}
	
    private function camelcase($string, $firstUpper=false)
    {
        $string = str_replace("_", " ", $string);
        $string = ucwords($string);
        $string = str_replace(" ", "", $string);
        if($firstUpper)
        {
            return $string;
        }
        else
        {
            return strtolower(substr($string,0,1)).substr($string,1);
        }
    }
	
	private function getTables()
	{
		$sql = "SHOW TABLES;";
		$rslt = $this->db->query($sql);
		$tables = array();
		while($data = mysql_fetch_row($rslt))
		{
			$tables[] = $data[0];
		}
		return $tables;
	}
	
	public function getTableInfo($name)
	{
		$sql = "DESCRIBE `$name`";
		$rslt = $this->db->query($sql);
		$cols = array();
		while($data = mysql_fetch_assoc($rslt))
		{
			$cols[] = $data;
		}
		return $cols;
	}
	
	public function modelName($tablename)
	{
		return $this->camelcase($tablename, true);
	}
	
	public function writeFile($path, $source)
	{
		$fh = fopen($path, "w");
		fwrite($fh, $source);
		fclose($fh);
	}
	
	public function generateModel($tablename, $info)
	{
		$name = $this->modelName($tablename);
		$properties = Array();
		$fieldnames = Array();
		$rules = Array();
		$key;
		foreach($info as $field)
		{
			$fieldname = $field['Field'];
			$property = $this->camelcase($fieldname);
			$properties[] = $property;
			$fieldnames[$property] = $fieldname;
			$unsigned = false;
		
			// primary
			if('PRI' === $field['Key'])
			{
				$key = $property;
				// primary shouldn't be required in the model so insertions can happen!
				if(isset($rules[$property]) && isset($rules[$property][$required]))
				{
					unset($rules[$property][$required]);
				}
			}
			
			// rules
			$rules[$property] = array();
			if(false !== strpos($field['Type'], "unsigned"))
			{
				if(!is_array($rules[$property]))
				{
					$rules[$property] = array();
				}
				$rules[$property]['unsigned'] = true;
				$unsigned = true;
			}
			if(false !== strpos($field['Type'], "int("))
			{
				if(!is_array($rules[$property]))
				{
					$rules[$property] = array();
				}
				$rules[$property]['digits'] = true;
				preg_match("/int\(([\d]+)\)/", $field['Type'], $matches);
				if(count($matches))
				{
					$max = pow(10, $matches[1]) -1;
					if($unsigned)
					{
						$min = 0;
					}
					else
					{
						$min = 0 - $max;
					}
					$rules[$property]['range'] = array($min, $max);
				}
			}
			if(false !== strpos($field['Type'], "float"))
			{
				if(!is_array($rules[$property]))
				{
					$rules[$property] = array();
				}
				$rules[$property]['number'] = true;
			}
			if(false !== strpos($field['Type'], "char"))
			{
				preg_match("/char\(([\d]+)\)/", $field['Type'], $matches);
				if(count($matches))
				{
					if(!is_array($rules[$property]))
					{
						$rules[$property] = array();
					}
					$rules[$property]['maxlength'] = $matches[1];
				}
			}
			if(0 === strpos($field['Type'], "enum("))
			{
				// $accept
				eval("\$accept = " . str_replace('enum(', 'array(', $field['Type']) . ";");
				$rules[$property]['accept'] = $accept;
			}
			
			// not null -> required
			if(false !== strpos($field['Null'], "NO") && null === $field['Default'])
			{
				if(!is_array($rules[$property]))
				{
					$rules[$property] = array();
				}
				$rules[$property]['required'] = true;
			}
			
			// auto_inc
			if(false !== strpos($field['Extra'], "auto_increment"))
			{
				if(isset($rules[$property]) && isset($rules[$property]['required']))
				{
					unset($rules[$property]['required']);
				}
			}
		}
		$source = $this->modelHead($name . "_core")
					. $this->modelProperties($properties)
					. $this->modelFieldnames($fieldnames)
					. $this->modelRules($rules)
					. $this->modelKey($key)
					. $this->modelTablename($tablename)
					. $this->modelConstruct($properties)
					. $this->modelFoot();
		return $source;
	}
	
//	private function generateModel($name, $properties, $fieldnames, $rules, $key, $tablename)
//	{
		
//	}

	private function modelHead($name, $extends='Object')
	{
		$source = <<<HEAD
<?php
class $name extends $extends
{

HEAD;
		return $source;
	}
	
	private function modelProperties($properties)
	{
		$properties_string = var_export($properties, true);
		$source = <<<PROPS
protected \$properties = $properties_string;

PROPS;
		return $source;
	}
	
	private function modelFieldnames($fieldnames)
	{
		$fieldnames_string = var_export($fieldnames, true);
		$source = <<<FIELDS
protected \$fieldnames = $fieldnames_string;

FIELDS;
		return $source;
	}
	
	private function modelRules($rules)
	{
		$rules_string = var_export($rules, true);
		$source = <<<RULES
protected \$rules = $rules_string;

RULES;
		return $source;
	}
	
	private function modelKey($key)
	{
		$source = <<<KEY
	protected \$key = '$key';

KEY;
		return $source;
	}
	
	private function modelTablename($tablename)
	{
		$source = <<<KEY
	protected \$tablename = '$tablename';

KEY;
		return $source;
	}
	
	private function modelConstruct($properties)
	{
		$source = array();
		$source[] = "\n\tpublic function __construct($" . implode("=null, $", $properties) . "=null)\n\t{\n\t\tparent::__construct();\n";
		foreach($properties as $property)
		{
			$capitalised = strtoupper(substr($property, 0, 1)) . substr($property, 1);
			$source[] = "\t\tif(null === \$$property)\n";
			$source[] = "\t\t{\n";
			$source[] = "\t\t\t\$this->data->$property = null;\n";
			$source[] = "\t\t}\n";
			$source[] = "\t\telse\n";
			$source[] = "\t\t{\n";
			$source[] = "\t\t\t\$this->set$capitalised(\$$property);\n";
			$source[] = "\t\t}\n";
		}
		$source[] = "\t}\n";
		return implode("", $source);
	}
	
	private function modelFoot()
	{
		$source = <<<FOOT
}
?>

FOOT;
		return $source;
	}
}

/*
$test_table = 'user_details';
$info = $generator->getTableInfo($test_table);
$generator->generateModel($test_table, $info);
*/
$generator = new GenerateModels();
$generator->autoGenerate(dirname(dirname(__FILE__)) . "/models/generated");
?>
