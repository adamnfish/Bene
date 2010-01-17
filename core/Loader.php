<?php
class Loader
{
	protected $project;
	protected $errors;
	
	public function __construct(&$project, &$errors)
	{
		$this->project = $project;
		$this->errors = $errors;
	}
	
	public function autoLoad()
	{
		// use path info to autoload
		$pathinfo = $_SERVER['PATH_INFO'];
		if('/' !== $this->project->webroot)
		{
			$corrected = explode($this->project->webroot, $pathinfo);
			$pathinfo = $corrected[1];
		}
		// check the project mappings first
		$params = $this->checkMappings($pathinfo);
		//fall back to the default mapping
		if(false === $params)
		{
			$params = $this->defaultMapping($pathinfo);
		}
		$this->route($params[0], $params[1], $params[2]);
	}
	
	// url mappings loosely inspired hereby - http://www.railsrocket.com/articles/routing-in-rails
	private function checkMappings($pathinfo)
	{
		// I have notes for this somewhere - where?!
		// in Bene.php
		
		$mappings = $this->project->urlMappings;
		foreach($mappings as $pattern => $mapping)
		{
			$mapping_parts = explode('/', $pattern);
			if('/' === $pathinfo[0])
			{
				$pathinfo = substr($pathinfo, 1);
			}
			if('/' === $pathinfo[strlen($pathinfo) - 1])
			{
				$pathinfo = substr($pathinfo, 0, -1);
			}
			$request_parts = explode('/', preg_replace('@//+@', '/', $pathinfo));
			
			$mapping_count = count($mapping_parts);
			if(count($request_parts) === $mapping_count)
			{
				$match = true;
				$args = array();
				for($i = 0; $i < $mapping_count; $i++)
				{
					$arg_number = array();
					// this is an argument, so as long as it exists, we're good
					if(preg_match('/^[$]([0-9])+$/', $mapping_parts[$i], $arg_number))
					{
						if(isset($request_parts[$i]))
						{
							$args[$arg_number[1] - 1] = $request_parts[$i];
						}
						else
						{
							$match = false;
							break;
						}
					}
					else
					{
						// check they match
						if($mapping_parts[$i] !== $request_parts[$i])
						{
							$match = false;
							break;
						}
					}
				}
				if(true === $match)
				{
					// get the arguments
					$mapArgsCount = count($mapping) - 2;
					if(0 < $mapArgsCount)
					{
						// add hard coded args
						foreach($mapping as $key => $value)
						{
							if('controller' !== $key && 'method' !== $key)
							{
								$i = (int)str_replace('$', '', $key) - 1;
								$args[$i] = $value;
							}
						}
					}
					return array($mapping['controller'], $mapping['method'], $args);
				}
			}
			else
			{
				continue;
			}
		}
		
		return false;
	}
	
	private function defaultMapping($pathinfo)
	{
		$pathinfo = explode('/', $pathinfo);
		if('' === $pathinfo[0])
		{
			array_shift($pathinfo);
		}
		$controller = array_shift($pathinfo);
		$method = array_shift($pathinfo);
		if('' === $pathinfo[count($pathinfo) - 1])
		{
			array_pop($pathinfo);
		}
		return array($controller, $method, $pathinfo);
	}
	
	private function getController($controller_name)
	{
		if($controller_name)
		{
			$controller_name = strtoupper(substr($controller_name, 0, 1)) . substr($controller_name, 1) . 'Controller';
			$filename = $this->project->controllersPath . $this->project->ds . $controller_name . '.php';
			if(file_exists($filename))
			{
				require_once($filename);
				if(class_exists($controller_name))
				{
					return new $controller_name($_SERVER['REQUEST_URI']);
				}
			}
		}
		return false;
	}
	
	private function notFound()
	{
		$this->errors->throwErr(1, 'not found');
		require_once($this->project->beneControllersPath . $this->project->ds . 'ErrorController.php');
		$controller_name = 'ErrorController';
		$controller = new ErrorController($_SERVER['REQUEST_URI']);
		$method = 'error_404';
		$this->load($controller, $method);
	}
	
	private function route($controller_name, $method, $args)
	{
		$controller = $this->getController($controller_name);
		if(false === $controller)
		{
			$controller = $this->getController($this->project->index);
		}
		
		if(false === $controller)
		{
			$this->notFound();
		}
		else
		{
			if(!$method && 0 === count($args) && method_exists($controller, $controller->index) && !in_array($method, $controller->_hidden))
			{
				$this->load($controller, $controller->index);
			}
			else if(method_exists($controller, $method) && !in_array($method, $controller->_hidden))
			{
				// use reflection to sort arguments :)
				$controllerReflection = new ReflectionClass(get_class($controller));
				$methodReflection = $controllerReflection->getMethod($method);
				if(
					count($args) >= $methodReflection->getNumberOfRequiredParameters()
						&&
					count($args) <= $methodReflection->getNumberOfParameters()
				){
					$this->load($controller, $method, $args);
				}
				else
				{
					$this->notFound();
				}
			}
			else
			{
				$this->errors->throwErr(1, 'controller method not found', 'Couldn\'t find controller requested controller method or an index method as fallback');
				$this->notFound();
			}
			
		}
	}
	
	private function load($controller, $method, $args=array())
	{
		$controller->init($method, $this->project, $this->errors);
		$controller->executeBefore();
		$controller->{$method}($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9], $args[10], $args[11], $args[12], $args[13], $args[14], $args[15], $args[16], $args[17]);
		$controller->showErrors();
		$controller->executeAfter();
	}
}
?>
