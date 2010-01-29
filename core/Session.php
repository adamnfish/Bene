<?php
/**
 * Handles user sessions
 * @author adamf
 *
 */
class Session
{
	private $data = array();
	private $loggedin = false;
	// USERPREFSSALT
	
	public function __construct()
	{
		if(false === isset($_SESSION["userprefs_session"]))
		{
			$_SESSION["userprefs_session"] = array();
		}
		$this->data = $_SESSION["userprefs_session"];
		$this->validateSession();
	}
	
	/**
	 * writes data to the session
	 * and any other tidying up as necessary
	 */
	public function __destruct()
	{
		$_SESSION["userprefs_session"] = $this->data;
	}
	
	/**
	 * Escalates privileges and sets login information to the session
	 * @return unknown_type
	 */
	public function login($username, $password, $remember=false)
	{
		$users = new Users();
		$user = $users->select(array(array("email", $username), array("password", md5(USERPREFSSALT . $password))));
		
		if(is_array($user) && 1 === count($user)) // it works
		{
			return $this->escalate($user[0]);
		}
		else
		{
			// try logging them in with their old username if email login fails
			$user = $users->select(array(array("username", $username), array("password", md5(USERPREFSSALT . $password))));
			if(is_array($user) && 1 === count($user)) // it works
			{
				return $this->escalate($user[0]);
			}
			return false;
		}
	}
	
	public function escalate($user)
	{
		session_regenerate_id();
		$this->set('userId', $user->getUserId());
		$this->set('user', $user);
		$this->set('loggedIn', true);
		return true;
	}
	
	public function logout()
	{
		$_SESSION = array();
		$this->data = array();
		$this->login = false;
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		session_destroy();
		session_start();
		return true;
	}
	
	public function validateSession()
	{
		if($this->get('userId') && $this->get('loggedIn'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function isLoggedIn()
	{
		return !!$this->get('loggedIn');
	}
	
	public function set($name, $value)
	{
		$this->data[$name] = $value;
		return true;
	}
	
	public function get($name)
	{
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
	
	public function delete($name)
	{
		if(isset($this->data[$name]))
		{
			unset($this->data[$name]);
		}
		return true;
	}
}