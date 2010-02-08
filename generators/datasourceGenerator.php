<?php

/**
 * Class datasourceGenerator
 * 
 * Generates the datasource class for a JDOS datasource
 */

class DatasourceGenerator extends Generator
{
	private $jdos;
	private $dataSource;
	
	public function generate($datasource)
	{
		$this->dataSource = $datasource;
		$data = $this->project->jdos[$datasource];
		$driver = $data['driver'];
		if("mysql" === strtolower($driver))
		{
			return $this->generateMySQL($data, $datasource);
		}
	}
	
	public function write($filename='', $src=false)
	{
		if('' === $filename)
		{
			$data_dir = $this->project->dataSourcesPath;
			$filename = $data_dir . $this->project->ds . Utils::camelcase($this->dataSource, true) . 'Data.php';
		}
		parent::write($filename);
		return $filename;
	}
	
	private function generateMySQL($jdos, $datasource)
	{
		$data = array();
		$data['username'] = $jdos['connection']['username'];
		$data['password'] = $jdos['connection']['password'];
		$data['host'] = $jdos['connection']['host'];
		$data['database'] = $jdos['connection']['database'];
		
		ob_start();
		echo $this->classHeader($datasource . "Data", 'MySQL', 'Singleton database connection instance for ' . $datasource);
		echo $this->data($data);
		echo $this->instance();
		echo $this->classFooter();
		
		$source = ob_get_contents();
		ob_end_clean();
		$this->source = $source;
		return $this->source;
	}
	
	private function data($data)
	{
		$data_string = var_export($data, true);
		$source = <<<PROPS
protected \$data = $data_string;

PROPS;
		return $source;
	}
	
	private function instance()
	{
		$source = <<<INSTANCE
	public function instance(\$db=false)
	{
		if (!isset(self::\$data_instance))
		{
		\$c = __CLASS__;
			self::\$data_instance = new \$c(\$db);
		}
		return self::\$data_instance;
	}

INSTANCE;
		return $source;
	}
}
?>