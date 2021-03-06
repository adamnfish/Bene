<?php
/*
 * newProject.php
 * 
 * This will generate a new project as defined
 * It invokes all the specific generators (doesn't do any generating itself)
 */

require_once('../core/Utils.php');
require_once('../core/Generator.php');

class CreateNewProject
{
	private $project;
	
	public function __construct($numArgs, $args)
	{
		// have it self-invoke so it runs off the command line nicely
		$this->begin($numArgs, $args);
	}
	
	public function begin($numArgs, $args)
	{
		// root needs to be normailsed to an absolute path somewhere
		// TODO
		$root = $args[1];
		if(2 === $numArgs)
		{
				$name_input = $this->askName();
				if($this->isLongName($name_input))
				{
					$longName = $name_input;
					$suggestion = $this->suggestShortName($longName);
					$shortName = $this->askShortName($suggestion);
				}
				else
				{
					$shortName = $name_input;
					$suggestion = $this->suggestLongName($shortName);
					$longName = $this->askLongName($suggestion);
				}
				$this->generate($root, $shortName, $longName);
		}
		else
		{
			$this->help();
		}
	}
	
	private function generate($root, $shortName, $fullName)
	{
		if($this->confirmProject($root, $shortName, $fullName))
		{
			if($this->checkRoot($root, $shortName))
			{
				echo "Creating project root directory...\n";
				$root =  $root . '/' . $shortName;
				mkdir($root, 0775, true);
				$classname = ucwords($shortName);
	
				echo "Generating project file...\n";
				$projectFile = $this->projectFile($root, $classname, $fullName);
				require_once($root . '/' . $classname . '.php');
				
				echo "Creating directory structure...\n";
				$this->directoryStructure($root);
				
				$this->loadProjectFile($projectFile, $shortName);
				
				echo "Generating additional files...\n";
				$this->index();
				$this->htaccess();
				$this->controller();
				$this->homeController();
				$this->modelGeneratorScript();
				
				echo "Finished generating project\n";
			}
			else
			{
				$this->rootInvalid();
			}
		}
	}
	
	private function checkRoot($root, $shortName)
	{
		// check it's valid
		if(is_dir($root . '/' . $shortName))
		{
			// TODO add a 'force' flag here or something
			echo "Project root already exists!\n";
			return false;
		}
		else
		{
			
		}
		// check permissions on root dir
		// must have write access to generate project
		return true;
	}
	
	/*
	 * Generators
	 */
	
	/**
	 * Writes the project's 'project file'
	 * @return unknown_type
	 */
	private function projectFile($root, $shortName, $fullName)
	{
		require_once('projectFileGenerator.php');
		$pfGen = new ProjectFileGenerator($root, $shortName, $fullName);
		$pfGen->generate();
		$projectFile = $pfGen->write();
		return $projectFile;
	}
	
	/**
	 * Loads the created project file - this needs to be done after the dirs are created
	 * @param $path
	 * @return unknown_type
	 */
	private function loadProjectFile($projectFilePath, $projectName)
	{
		require_once($projectFilePath);
		$this->project = new $projectName();
	}
	
	/**
	 * Writes the required directorty structure
	 * @return unknown_type
	 */
	private function directoryStructure($root)
	{
		$dirs = array(
			"bin/tpl_cache",
			"bin/tpl_compile",
			"components/template_plugins",
			"controllers",
			"core",
			"dataSources",
			"models",
			"views/templates",
			"www/_resources/CSS",
			"www/_resources/JS",
			"www/_resources/images",
			"www/_resources/media",
			"utility_scripts",
		);
		foreach($dirs as $dir)
		{
			if("models" === $dir || "dataSources" === $dir)
			{
				// give these open permissions so the generators can run over them later
				mkdir($root . '/' . $dir, 0777, true);
			}
			else
			{
				mkdir($root . '/' . $dir, 0775, true);
			}
		}
	}
	
	private function index()
	{
		require_once('indexGenerator.php');
		$iGen = new IndexGenerator($this->project);
		$iGen->generate();
		$iGen->write();
	}
	
	private function htaccess()
	{
		require_once('htaccessGenerator.php');
		$htGen = new HtaccessGenerator($this->project);
		$htGen->generate();
		$htGen->write();
	}
	
	private function controller()
	{
		require_once('controllerGenerator.php');
		$htGen = new ControllerGenerator($this->project);
		$htGen->generate();
		$htGen->write();
	}
	
	private function homeController()
	{
		require_once('homeControllerGenerator.php');
		$htGen = new HomeControllerGenerator($this->project);
		$htGen->generate();
		$htGen->write();
	}
	
	private function modelGeneratorScript()
	{
		require_once('objectGeneratorGenerator.php');
		$modGen = new ObjectGeneratorGenerator($this->project);
		$modGen->generate();
		$modGen->write();
	}
	
	/*
	 * Input / Output stuff
	 */
	
	private function confirmProject($root, $shortName, $longName)
	{
		echo "I'm going to generate a new project with the following basic settings
	Full Name: $longName
	Short Name: $shortName
	path: $root/$shortName\n";
		echo "Proceed? y/n ";
		$full_name_input = trim(fgets(STDIN));
		return 'y' === $full_name_input[0];
	}
	
	private function help()
	{
		echo "Usage: php " . basename(__FILE__) . " [project root]\n";
	}
	
	private function rootInvalid()
	{
		echo "Invalid project root supplied\n";
		die();
	}
	
	private function rootPermsError()
	{
		echo "The project root does not have the correct permissions\n";
		echo "The webserver must have write access to the project\'s root if it is to generate the project!\n";
		die();
	}
	
	private function askName()
	{
		echo "Enter project name\n";
		$name_input = trim(fgets(STDIN));
		return $name_input;
	}
	
	private function isLongName($name)
	{
		if(preg_match('/[^\w]/', $name))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function suggestLongName($shortName)
	{
		$longName = str_replace('_', ' ', $shortName);
		$longName = trim(preg_replace('/([A-Z])/', ' $1', $longName));
		return $longName;
	}
	
	private function suggestShortName($longName)
	{
		$shortName = Utils::camelcase($longName, true);
		$shortName = preg_replace('/[^\w]/', '', $shortName);
		return $shortName;
	}
	
	private function askLongName($suggestion, $having_problems=false)
	{
		echo "You can also give a 'full name' for the project, or just press enter to use '$suggestion'\n";
		// if having problems give additional help

		$full_name_input = trim(fgets(STDIN));
		// check input is valid
		if('' === $full_name_input)
		{
			return $suggestion;
		}
		return $full_name_input;
	}
	
	private function askShortName($suggestion, $having_problems=false)
	{
		echo "You'll also need to provide a 'short name' for the project\n";
		echo "If you like the sound of '$suggestion' just press enter now\n";
		echo "Otherwise, please give a short name (no spaces or funny characters)\n";
		// if having problems give additional help

		$short_name_input = trim(fgets(STDIN));
		// check input is valid
		if('' === $short_name_input)
		{
			return $suggestion;
		}
		return $short_name_input;
	}
	
	private function checkFullName($fullName)
	{
		// long name should be able to be anything, really!
		return true;
	}
	
	private function checkShortName($shortName)
	{
		return !!preg_match('/[^\w]/', $name);
	}
}

new CreateNewProject($argc, $argv);

?>