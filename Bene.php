<?php
/**
 * abstract Class Bene
 * This class works as a hybrid of the config and the instantiation
 * 
 * Bene is a class to represent the framework, everything else makes this work
 * Bene should be over-riden to create a project
 * 
 * @author adamnfish
 *
 */
abstract class Bene
{
	private $requireSource;
	
	protected $errors;

	public $loader;
	
	// config directives
	public $ds = '/';
	public $name = '';
	public $fullName = '';
	public $index = 'index';
	public $admin = '_admin';
	public $tpl_extension = 'tpl';
	public $tpl_debugging = false;
	public $tpl_caching = true;
	public $tpl_cacheTime = 120;
	public $tpl_leftDelimiter = '${';
	public $tpl_rightDelimiter = '}';
	
	// or read mappings from a JSON file
	// turn that json file into an optimsed .php file, with all the strings exploded etc
	// cache this process, so a given url is mapped to a controller / action automatically eventually
	public $urlMappings = array();
	/* example
	 * 
	 *	array(
	 *		'user/$1' => array(
	 *			'controller' => 'user'
	 *			'method' => 'view'
	 *		),
	 *		'blogs/$1' = array(
	 *			'controller' => 'blogs'
	 *			'method' => 'findById'
	 *		),
	 *		'blogs/$1/$2' = array(
	 *			'controller' => 'blogs'
	 *			'method' => 'viewByDate'
	 * 		),
	 * 		'$controller/$method/[$1/[$2/[$3/ ... ]]]' is the default
	 *	)
	 */
	
	// the calculation of all this stuff can be cached as well
	
	// bene paths
	public $benePath;
	public $beneControllersPath;
	public $corePath;
	public $librariesPath;
	
	// project paths
	public $projectRoot;
	public $controllersPath;
	public $modelsPath;
	public $mappersPath;
	public $componentsPath;
	public $projectCorePath;
	
	// web paths
	public $webrootPath;
	
	// web paths
	public $webroot = false;
	
	
	protected function __construct($projectRoot, $webrootPath)
	{
		$this->setWebPaths($webrootPath);
		$this->setBenePaths();
		$this->setProjectPaths($projectRoot);
		
		require_once($this->corePath . $this->ds . 'Require.php');
		
		$this->requireSource = new RequireSource($this);
		
		$this->errors = new Errors();
		$this->disableMagicQuotes();
		
		$this->loader = new Loader($this, $this->errors);
		
		// create mapper(s) here as well?
	}
	
	public function start()
	{
		// consistency checks
		if($this->checkConsistency())
		{
			$this->loader->autoLoad();
		}
		else
		{
			$this->errors->throwErr(1, 'Project has not been correctly configured');
		}
	}
	
	private function checkConsistency()
	{
		if(!$this->projectRoot)
		{
			return false;
		}
		if(!$this->benePath)
		{
			return false;
		}
		return true;
	}
	
	// these paths (and the project ones) can all be worked out at generation time - doesn't need to be calculated for every request!
	// actually, for portability, they should be worked out
	private function setBenePaths()
	{
		$this->benePath = dirname(__file__);
		$this->corePath = $this->benePath . $this->ds . 'core';
		$this->librariesPath = $this->benePath . $this->ds . 'libraries';
		$this->generatorsPath = $this->benePath . $this->ds . 'generators';
		$this->beneControllersPath = $this->benePath . $this->ds . 'controllers';
		if(false === $this->webroot)
		{
			$webroot = explode('/index.php', $_SERVER['SCRIPT_NAME']);
			$this->webroot = $webroot[0] ? $webroot[0] : '/'; 
		}
	}
	
	/*
	 * Config methods 
	 */
	protected function setProjectPaths($project_root)
	{
		$this->projectRoot		= $project_root;
		$this->binPath			= $project_root . $this->ds . 'bin';
		$this->controllersPath	= $this->projectRoot . $this->ds . 'controllers';
		$this->modelsPath		= $this->projectRoot . $this->ds . 'models';
		$this->mappersPath		= $this->projectRoot . $this->ds . 'mappers';
		$this->formsPath		= $this->projectRoot . $this->ds . 'forms';
		$this->componentsPath	= $this->projectRoot . $this->ds . 'components';
		$this->templatePlugins	= $this->componentsPath . $this->ds . 'templatePlugins';
		$this->projectCorePath	= $this->projectRoot . $this->ds . 'core';
		$this->templatePath		= $this->projectRoot . $this->ds . 'templates';
		$this->tpl_CompilePath	= $this->binPath . $this->ds . 'tpl_compile';
		$this->tpl_CachePath	= $this->binPath . $this->ds . 'tpl_cache';
		// ... etc
	}
	
	protected function setWebPaths($webrootPath)
	{
		$this->webrootPath = $webrootPath;
		// eg _img, _js if that becomes necessary
	}
	
	public function template()
	{
		// require smarty
		require_once($this->librariesPath . $this->ds . "smarty/libs/Smarty.class.php");

		$t = new Smarty;
		$t->setTemplateDir($this->templatePath);
		$t->setCompileDir($this->tpl_CompilePath);
		$t->left_delimiter = $this->tpl_leftDelimiter;
		$t->right_delimiter = $this->tpl_rightDelimiter;
		$t->debugging = $this->tpl_debugging;
		if($this->tpl_cache)
		{
			$t->caching = true;
			$t->cache_lifetime = $this->tpl_cacheTime;
			$t->setCacheDir($this->tpl_CachePath);
		}
		
		return $t;
	}
	
	/**
	 * From php.net
	 * This function undoes magic-quotes if it is enabled
	 * It is a *much more sensible* idea to disable magic quotes in the server configuration
	 * @return unknown_type
	 */
	private function disableMagicQuotes()
	{
		if (get_magic_quotes_gpc()) {
			$this->errors->throwErr(3, 'Magic quotes is enabled. It has been automatically disabled, but for performance reasons, it should be unset in the server configuration.');
		    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
		    while (list($key, $val) = each($process)) {
		        foreach ($val as $k => $v) {
		            unset($process[$key][$k]);
		            if (is_array($v)) {
		                $process[$key][stripslashes($k)] = $v;
		                $process[] = &$process[$key][stripslashes($k)];
		            } else {
		                $process[$key][stripslashes($k)] = stripslashes($v);
		            }
		        }
		    }
		    unset($process);
		}
	}
	
	/*
	 * Loading methods - used to dynamically load specific component types
	 * 
	 * This greatly lowers the setup overhead on each Bene request at the cost
	 * of having to call on of these before a resource is available
	 * 
	 * This may now no longer be needed
	 */
	
	/**
	 * Requires the file containing this form class (assuming naming conventions are adhered to)
	 * @return unknown_type
	 */
	public function loadForm($form, $validator=false)
	{
		if(false === $validator)
		{
			// TODO point this at the project-wide validator over-ride
			// so the default validator can be easily extensible
			$validator = new Validator();
		}
		require_once($this->formsPath . $this->ds . $form . ".php");
		return new $form($validator);
	}
}

?>