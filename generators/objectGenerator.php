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
		$this->object = $object;
		return $this->project->jdos[$dataSource]['schema'][$object];
	}
	
	public function generate($dataSource, $object)
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
	
	public function write($filename='')
	{
		if('' === $filename)
		{
			$filename = $this->project->modelsPath . $this->project->ds . Utils::camelcase($this->object, true) . '.php';
		}
		parent::write($filename);
	}
	
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
	
	private function construct()
	{
		$construct = <<<CONSTRUCT

	public function __construct()
	{
		parent::__construct();
	}

CONSTRUCT;
		return $construct;
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
}
?>