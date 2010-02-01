<?php
// requires the files needed for shizzle
// this should include the bene core
// and use the config to auto-include files fromt he project 

class RequireSource
{
	private $project;
	
	public function __construct(&$project)
	{
		$this->project = $project;
		$this->requireBene();
		$this->requireProject();
	}
	
	private function requireBene()
	{
		// core
		require_once($this->project->corePath . $this->project->ds . 'Utils.php');
		require_once($this->project->corePath . $this->project->ds . 'Log.php');
		require_once($this->project->corePath . $this->project->ds . 'Errors.php');
		require_once($this->project->corePath . $this->project->ds . 'Error.php');
		require_once($this->project->corePath . $this->project->ds . 'Loader.php');
		require_once($this->project->corePath . $this->project->ds . 'Object.php');
		require_once($this->project->corePath . $this->project->ds . 'Data.php');
		require_once($this->project->corePath . $this->project->ds . 'Mapper.php');
		require_once($this->project->corePath . $this->project->ds . 'CoreController.php');
		
		return true;
	}
	
	private function requireLibraries()
	{
		if(file_exists($this->project->librariesPath . $this->project->ds . 'smarty/smarty.class.php'));
		// no other libratries yet!
	}
	
	private function requireProject()
	{
		$this->requireFromDir($this->project->projectCorePath, true);
		$this->requireFromDir($this->project->componentsPath, true);
		$this->requireFromDir($this->project->generatedModelsPath, true);
		$this->requireFromDir($this->project->modelsPath, true);
		$this->requireFromDir($this->project->mappersPath, true);
		$this->requireFromDir($this->project->controllersPath, true);
		
		return true;
	}
	
	private function requireFromDir($dir, $deep=true)
	{
		$contents = scandir($dir);
		foreach($contents as $file)
		{
			$path = $dir . $this->project->ds . $file;
			if('.' === $file || '..' === $file)
			{
				continue;
			}
			else if(is_file($path))
			{
				if(false !== stripos($file, '.php'))
				{
					require_once($path);
				}
			}
			else if($deep && is_dir($path))
			{
				$this->requireFromDir($path, $deep);
			}
		}
		return true;
	}
}
/*
require_once($root . "/core/Controller.php");
require_once($root . "/core/Data.php");
require_once($root . "/core/Object.php");
require_once($root . "/core/Loader.php");
require_once($root . "/core/utilities.php");
require_once($root . "/core/Mapper.php");
require_once($root . "/core/Error.php");
require_once($root . "/core/Errors.php");

require_once($root . "/data/project_db.php");

require_once($root . "/domain/Feature.php");
require_once($root . "/domain/Pub.php");

require_once($root . "/mappers/Pubs.php");
require_once($root . "/mappers/Features.php");
*/
?>
