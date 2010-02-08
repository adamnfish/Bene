<?php
/**
 * Class ObjectGenerator
 */

// TODO aadd an isValid function to Objects that checks for eg types, nullness, lengths

class ObjectGenerator extends Generator
{
	private $jdos;
	private $dataSource;
	private $object;
	private $int_types = array(
		"int",
		"integer",
		"tinyint",
		"smallint",
		"mediumint",
		"largeint"
	);
	private $float_types = array(
		"float",
		"dec",
		"decimal",
		"double precision",
		"double"
	);
	private $string_types = array(
		"char",
		"varchar",
		"text",
		"tinytext",
		"mediumtext",
		"longtext"
	);
	
	private function readJDOS($dataSource, $object)
	{
		$this->dataSource = $dataSource;
		return $this->project->jdos[$dataSource]['schema'][$object];
	}
	
	public function generate_OLD($dataSource, $object)
	{
		$jdos = $this->readJDOS($dataSource, $object);
		ob_start();
		echo $this->classHeader($object, 'Object');
		// run through the jdos definition for the object and generate the class accordingly
		foreach($jdos as $fieldname => $field)
		{
			echo $this->classVar($fieldname);
		}
		foreach($jdos as $fieldname => $field)
		{
			echo $this->getter($fieldname, $field);
		}
		foreach($jdos as $fieldname => $field)
		{
			echo $this->setter($fieldname, $field);
		}
		echo $this->classFooter();
		$source = ob_get_contents();
		ob_end_clean();
		$this->source = $source;
		return $this->source;
	}
	
	public function generateAll($dataSource, $stubs=false)
	{
		foreach($this->project->jdos[$dataSource]['schema'] as $tablename => $table_info)
		{
			$this->generate($dataSource, $tablename, $stubs);
			$this->write();
		}
	}
	
	public function generate($dataSource, $tablename, $stub=false)
	{
		// allow the object name to be different to the tablename
		if(isset($this->project->jdos[$dataSource]['objectNames'][$tablename]))
		{
			$object = $this->project->jdos[$dataSource]['objectNames'][$tablename];
		}
		else
		{
			$object = $tablename;
		}
		
		$jdos = $this->readJDOS($dataSource, $tablename);
		$this->object = $object;
		
		$properties = array();
		$fieldnames = array();
		$rules = array();
		$key;
		
		foreach($jdos as $fieldName => $field)
		{
			if(isset($field['propertyName']))
			{
				$property = $field['propertyName'];
				$fieldnames[$property] = $fieldName;
			}
			else
			{
				$property = Utils::camelcase($fieldName);
				$fieldnames[$property] = $fieldName;
			}
			$properties[] = $property;
			$unsigned = false;
			$rules[$property] = array();
			
			// unsigned?
			if($field['unsigned'])
			{
				$rules[$property]['unsigned'] = true;
				$unsigned = true;
			}
			
			if("bool" === $field['type'])
			{
				$field['type'] = "tinyint";
				$field['length'] = 1;
			}
			//rules
			if(false !== strpos($field['Type'], "int"))
			{
				$rules[$property]['digits'] = true;
				if($field['length'])
				{
					$max = pow(10, $field['length']) -1;
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
			if("float" === $field['type'])
			{
				$rules[$property]['number'] = true;
			}
			if(false !== strpos($field['type'], "char"))
			{
				if($field['length'])
				{
					$rules[$property]['maxlength'] = $field['length'];
				}
			}
			if("enum" === $field['type'])
			{
				$rules[$property]['accept'] = $field['values'];
			}
			if(($field['required'] || false === $field['null']) && (true == $field['null'] || false === isset($field['null'])))
			{
				$rules[$property]['required'] = true;
			}
			if($field['auto_increment'])
			{
				if(isset($rules[$property]['required']))
				{
					unset($rules[$property]['required']);
				}
			}
			if(isset($field['email']))
			{
				$rules[$property]['email'] = true;
			}
			if(isset($field['url']))
			{
				$rules[$property]['url'] = true;
			}
			if(isset($field['date']))
			{
				$rules[$property]['date'] = true;
			}
			if(isset($field['dateISO']))
			{
				$rules[$property]['dateISO'] = true;
			}
			if(isset($field['number']))
			{
				$rules[$property]['number'] = true;
			}
			if(isset($field['digits']))
			{
				$rules[$property]['digits'] = true;
			}
			if(isset($field['maxlength']))
			{
				$rules[$property]['maxlength'] = $field['maxlength'];
			}
			if(isset($field['minlength']))
			{
				$rules[$property]['minlength'] = $field['minlength'];
			}
			if(isset($field['rangelength']))
			{
				$rules[$property]['rangelength'] = $field['rangelength'];
			}
			if(isset($field['max']))
			{
				$rules[$property]['max'] = $field['max'];
			}
			if(isset($field['min']))
			{
				$rules[$property]['min'] = $field['maxlminength'];
			}
			if(isset($field['range']))
			{
				$rules[$property]['range'] = $field['range'];
			}
			if(isset($field['equalto']))
			{
				$rules[$property]['equalto'] = $field['equalto'];
			}
			if(isset($field['pattern']))
			{
				$rules[$property]['pattern'] = $field['pattern'];
			}
			
			// primary
			if($field['primary'])
			{
				$key = $property;
			}
		}
		
		// use the constructed info to build the source
		ob_start();

		echo $this->classHeader($object . 'Core', 'Object');
		echo $this->modelProperties($properties);
		echo $this->modelFieldnames($fieldnames);
		echo $this->modelRules($rules);
		echo $this->modelKey($key);
		echo $this->modelTablename($tablename);
		echo $this->construct($properties, $dataSource);
		echo $this->classFooter();
		
		$source = ob_get_contents();
		ob_end_clean();
		
		if($stub)
		{
			$this->generateStub($dataSource, $tablename);
		}
		
		$this->source = $source;
		return $this->source;
	}
	
	public function generateStub($dataSource, $tablename)
	{
		// allow the object name to be different to the tablename
		if(isset($this->project->jdos[$dataSource]['objectNames'][$tablename]))
		{
			$object = $this->project->jdos[$dataSource]['objectNames'][$tablename];
		}
		else
		{
			$object = $tablename;
		}
	
		$model_dir = $this->project->modelsPath . $this->project->ds . $this->dataSource;
		if(!is_dir($model_dir))
		{
			mkdir($model_dir, 0755, true);
		}
		$superclass_path = $model_dir . $this->project->ds . 'generated' . $this->project->ds . Utils::camelcase($this->object, true) . 'Core.php';
		ob_start();

		echo $this->classHeader($object, Utils::camelcase($object, true) . "Core", '', $superclass_path);
		echo $this->classFooter();
		
		$source = ob_get_contents();
		ob_end_clean();
		
		$filename = $model_dir . $this->project->ds . Utils::camelcase($this->object, true) . '.php';
		if(!file_exists($filename))
		{
			parent::write($filename, $source);
		}
		else
		{
			echo "Skipping existing file, $filename <br />\n";
		}
	}
	
	public function write($filename='', $source=false)
	{
		if('' === $filename)
		{
			$model_dir = $this->project->modelsPath . $this->project->ds . $this->dataSource;
			$generated_dir = $model_dir . $this->project->ds . 'generated';
			if(!is_dir($generated_dir))
			{
				mkdir($generated_dir, 0755, true);
			}
			$filename = $generated_dir . $this->project->ds . Utils::camelcase($this->object, true) . 'Core.php';
		}
		parent::write($filename);
	}
	
	/*
	private function setterConditions($fieldname, $field)
	{
		$conditions = array();
		// change these so they use Validator methods
		// this brings setting in line with the front-end behaviour
		if($field['length'])
		{
//			$conditions[] = " {$field['length']} <= strlen($$fieldname)";
			$conditions[] = " Validator::maxlength($$fieldname, {$field['length']})";
		}
		if(in_array($field['type'], $this->int_types))
		{
//			$conditions[] = " is_int($$fieldname)";
			$conditions[] = " Validator::digits($$fieldname)";
		}
		if(in_array($field['type'], $this->float_types))
		{
//			$conditions[] = " is_float($$fieldname)";
			$conditions[] = " Validator::number($$fieldname)";
		}
		if(isset($field['unsigned']) && true == $field['unsigned'])
		{
//			$conditions[] = " 0 <= $$fieldname";
			$conditions[] = " Validator::unsigned($$fieldname)";
		}
		if(isset($field['pattern']))
		{
			$pattern = str_replace('"', '\"', $field['pattern']);
//			$conditions[] = " preg_match('" . str_replace("'", "\'", $field['pattern']) . "', $$fieldname)";
			$conditions[] = " Validator::pattern(\"{$field['pattern']}\", $$fieldname)";
		}
		
		if(count($conditions))
		{
			$conditions = substr(implode(" &&", $conditions), 1);
		}
		else
		{
			$conditions = '';
		}
		return $conditions;
	}
	
	private function castType($field)
	{
		$cast = '';
		if(in_array($field['type'], $this->int_types))
		{
			$cast = '(int)';
		}
		if(in_array($field['type'], $this->float_types))
		{
			$cast = '(float)';
		}
		if(in_array($field['type'], $this->string_types))
		{
			$cast = '(string)';
		}
		return $cast;
	}
	*/
	
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
	
	private function construct($properties, $dataSource)
	{
		$source = array();
		$source[] = "\n\tpublic function __construct($" . implode("=null, $", $properties) . "=null)\n\t{\n\t\tparent::__construct();\n";
		$source[] = "\t\t\$this->dataSource = " . Utils::camelcase($dataSource, true) . "Data::instance();\n";
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
	
	private function getter($fieldname, $field)
	{
		$getterName = 'get' . Utils::camelcase($fieldname, true);
		$fieldname = Utils::camelcase($fieldname);
		
		$getter = <<<GETTER
		
	public function $getterName()
	{
		return \$this->$fieldname;
	}
		
GETTER;
		return $getter;
	}
	
	// this needs updating with the new $this->checkField format
	/*
	private function setter($fieldname, $field)
	{
		$setterName = 'set' . Utils::camelcase($fieldname, true);
		$fieldname = Utils::camelcase($fieldname);
		$conditions = $this->setterConditions($fieldname, $field);
		$cast = $this->castType($field);
		
		if($conditions)
		{
			$setter = <<<SETTER
		
	public function $setterName(\$$fieldname)
	{
		if($conditions)
		{
			\$this->$fieldname = $cast\$$fieldname;
			return true;
		}
		else
		{
			return false;
		}
	}
		
SETTER;
		}
		else
		{
			$setter = <<<SETTER
		
	public function $setterName(\$$fieldname)
	{
		\$this->$fieldname = $cast\$$fieldname;
		return true;
	}
		
SETTER;
		}
		return $setter;
	}
	*/
}
?>