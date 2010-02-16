<?php
/**
 * abstract class components
 * @author adamnfish
 *
 */
abstract class CoreController
{	
	protected $errors;
	protected $project;
	protected $method;
	protected $E;
	public $request_uri;
	public $get;
	public $post;
	
	// array of componenets - this would autoload each component?
	// I Don't like this
	// if you need it in the whole controller, put it in executeBefore
	// otherwise load it as and when
	// protected $components = array();
	public $index = "index";
	public $notFound = "error_404";
	/**
	 * list of controller methods that aren't 'browsable'
	 * ie. these methods should not be run by the Loader - they are internal methods
	 * 
	 * @var $hidden array
	 */
	public $_hidden = array(
		"__construct",
		"init",
		"executeBefore",
		"executeAfter",
		"runControllerMethod",
		"redirect",
		"createInternalLink",
		"setTemplate"
	);

	/**
	 * components
	 * array of components to include in this controller instance?
	 * or add loadComponent method, maybes a beter plan
	 * 
	 * @var $components array
	 */
		
	public function __construct($request_uri=false)
	{
		$this->request_uri = $request_uri;
		if(false === $request_uri)
		{
			$this->request_uri = $_SERVER['REQUEST_URI'];
		}
		$this->get = $_GET;
		$this->post = $_POST;
	}
	
	/**
	 * __get
	 * 
	 * This allows us to lazy load some controller properties for perfoamce's sake
	 * I'm thinking of Smarty here really, because you may just want to output eg. JSON/XML
	 * in which case, loading up Smarty is a waste of cycles
	 * @param $name
	 * @return property / Smarty template
	 */
	public function __get($name)
	{
		if('template' === $name || 't' === $name)
		{
			$this->t = $this->project->template();
			$this->t->assign('webroot', $this->project->webroot);
			$this->t->assign('css', $this->project->cssPath);
			$this->t->assign('js', $this->project->javascriptPath);
			$this->t->assign('images', $this->project->imagesPath);
			$this->t->assign('media', $this->project->mediaPath);
			$this->t->assign('title', $this->project->fullName);
			$this->t->assign('projectName', $this->project->fullName);
			$this->t->assign('projectAuthor', $this->project->author);
			return $this->t;
		}
		else if('tpl' === $name)
		{
			$matches = array();
			preg_match('/(.*)Controller$/', get_class($this), $matches);
			$name = strtolower(substr($matches[1], 0, 1)) . substr($matches[1], 1);
			$defaultTemplatePath = $name . $this->project->ds . $this->method . "." . $this->project->tpl_extension;
			$this->tpl = $defaultTemplatePath;
			return $this->tpl;
		}
		else
		{
			return $this->$name;
		}
	}
	
	public function error_404()
	{
		header('HTTP/1.0 404 Not Found');
	}
	
	public function init($method, &$project, &$errors)
	{
		$this->project	= $project;
		$this->E		= $errors;
		$this->method	= $method;
	}
	
	public function executeBefore()
	{
		
	}
	
	public function executeAfter()
	{
	}
	
	public function showErrors()
	{
		echo $this->E->printErrors();
	}
	
	/**
	 * A 'pseudo-redirect'
	 * runs anpother controller method at this url (without redirect)
	 * An obvious example of this would be 404 pages
	 * 
	 * @param $controller
	 * @param $method
	 * @param $arguments
	 * @return unknown_type
	 */
	protected function runControllerMethod($controller, $method, $arguments)
	{
		$this->project->loader->load($controller, $method, $arguments);
	}
	
	// utility methods
	// these should be in the project, rather than in the controller?
	
	/**
	 * Redirects the user to the supplied url (use sparingly!) 
	 * @param $url
	 */
	public function redirect($url)
	{
		if(0 === strpos($url, '/'))
		{
			$url = $this->project->webroot . $url;
		}
		header('Location: ' . $url);
	}
	
	/**
	 * calls the 'project' template handler
	 * 
	 * no longer necessary - this is accessed via a __get overload (lazily)
	 * 
	 * @param $template
	 * @return smarty template
	 */
	public function setTemplate($template)
	{
	}
	
	/**
	 * Generates a link to the provided path (typically $controller, $method, $arg)
	 * 
	 * @param $controller
	 * @param $method
	 * @param $arg1
	 * @param $arg2
	 * @param $arg3
	 * @param $arg4
	 * @param $arg5
	 * @param $arg6
	 * @return String $link
	 */
	public function createInternalLink($controller, $method, $arg1=false, $arg2=false, $arg3=false, $arg4=false, $arg5=false, $arg6=false)
	{
		$path_components = func_get_args();
		return $this->project->webroot . "/" . implode("/", $path_components); 
	}
	
	public function loadComponent($component)
	{
		if(file_exists($this->project->componentsPath . $this->project->ds . $component . ".php"))
		{
			require($this->project->componentsPath . $this->project->ds . $component . ".php");
			return new $component($this->project, $this->E);
		}
		else if(file_exists($this->project->coreComponentsPath . $this->project->ds . $component . ".php"))
		{
			require($this->project->coreComponentsPath . $this->project->ds . $component . ".php");
			return new $component($this->project, $this->E);
		}
		else
		{
			return false;
		}
	}
}
?>
