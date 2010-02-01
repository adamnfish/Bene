<?php
/**
 * Class ProjectGenerator
 * 
 * creates project file (class that extends the core 'Bene' class, with all the project settings)
 */

class ProjectFileGenerator extends Generator
{
	private $beneRoot;
	private $name;
	private $fullName;
	private $projectRoot;
	
	private $properties = array();
	
	public function __construct($projectRoot, $name, $fullName='')
	{
		$this->name = $name;
		$this->fullName = $fullName ? $fullName : $name;
		$this->projectRoot = $projectRoot;
		
		$this->setProjectProperty('name', $this->name);
		$this->setProjectProperty('fullName', $this->fullName);
	}
	
	public function setProjectProperty($name, $value)
	{
		$this->properties[$name] = $value;
	}
	
	public function generate()
	{
		ob_start();
		// header
		echo $this->classHeader($this->name, 'Bene', 'This class represents the project itself, including all its settings', array(dirname(__dir__) . '/Bene.php'));
		
		foreach($this->properties as $name => $value)
		{
			echo $this->classVar($name, $value);
		}
		
		echo $this->construct();
		echo $this->config();
		echo $this->devConfig();
		echo $this->liveConfig();
		
		echo $this->classFooter();
		
		$source = ob_get_contents();
		ob_end_clean();
		$this->source = $source;
		return $this->source;
	}
	
	public function write()
	{
		$filename = $this->projectRoot . '/' . $this->name . '.php';
		return parent::write($filename);
	}
	
	private function construct()
	{
		$construct = <<<CONSTRUCT
	public function __construct(\$webrootPath)
	{
		parent::__construct(dirname(__file__), \$webrootPath);
		\$this->config();
		\$this->setProjectPaths(dirname(__file__));
	}


CONSTRUCT;
		return $construct;
	}
	
	private function config()
	{
		$config = <<<CONFIG
	/**
	 * Delegates settings to an appropriate method
	 * This should be used to invoke live/development settings as appropriate (to the same codebase)
	 * @return unknown_type
	 */
	private function config()
	{
		// add conditions to these statements
		// and add additional statements as required
		if(true) // defaults to live configuration
		{
			\$this->liveConfig();
		}
		else if(true) // add a condition for development settings
		{
			\$this->devConfig();
		}
		else
		{
			// If it fails to find appropriate configuration, it will produce an error
			\$this->errors->throwErr(1, "Project not configured", "No specific server configuration was applied to this instance. Check the config!");
		}
	}


CONFIG;
		return $config;
	}
	
	private function devConfig()
	{
		$devConfig = <<<DEVCONFIG
	/**
	 * Default development configuration settings
	 */
	private function devConfig()
	{
		\$this->errors->setDisplayLevel(3);
		\$this->errors->setLogLevel(3);
		\$this->tpl_debugging = true;
	}


DEVCONFIG;
		return $devConfig;
	}
	
	private function liveConfig()
	{
		$liveConfig = <<<LIVECONFIG
	/**
	 * Default live configuration settings
	 */	
	private function liveConfig()
	{
		\$this->errors->setDisplayLevel(0);
		\$this->errors->setLogLevel(2);
	}

LIVECONFIG;
		return $liveConfig;
	}
}
?>