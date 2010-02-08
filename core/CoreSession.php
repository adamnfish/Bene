<?php
/**
 * Handles user sessions
 * @author adamf
 *
 */
abstract class CoreSession
{
	private $loggedin = false;
	protected $sessionvar_name = "BENESESSION";
	protected $usercookie_name = "BENESESSION_USER";
	protected $keycookie_name = "BENESESSION_KEY";

	protected $user_classname = "Users";
	protected $userid_fn = "getUserId";
	protected $userkey_fn = "getCreated";
	
	public $user;
	
	public function __construct()
	{
		session_start();
		if(false === isset($_SESSION[$this->sessionvar_name]))
		{
			$_SESSION[$this->sessionvar_name] = array();
		}
		$this->validateSession();
	}
	
	/**
	 * writes data to the session
	 * and any other tidying up as necessary
	public function __destruct()
	{
		Utils::dump($this->data, "destruct");
		$_SESSION[$this->sessionvar_name] = $this->data;
	}
	 */
	
	/**
	 * This is part of the application logic and needs to be implemented in your project
	 * It should return the user in the format you want saved to the session
	 * @param String $username
	 * @param String $password
	 * @return User
	 */
	abstract protected function verifyLogin($username, $password);
	
	/**
	 * Escalates privileges and sets login information to the session
	 * @return unknown_type
	 */
	public function login($username, $password, $remember=false)
	{
		/*
		$users = new Users();
		$user = $users->select(array(array("email", $username), array("password", md5(USERPREFSSALT . $password))));
		*/
		$user = $this->verifyLogin($username, $password);
		
		if($user) // it works
		{
			return $this->escalate($user, $remember);
		}
		else
		{
			return false;
		}
	}
	
	private function escalate($user, $remember=false)
	{
		session_regenerate_id();
		$this->set('userId', $user->{$this->userid_fn}());
		$this->set('user', $user);
		$this->set('loggedIn', true);
		$this->user = $user;
		if($remember)
		{
			$this->rememberLogin($user);
		}
		return true;
	}
	
	public function logout()
	{
		$_SESSION = array();
//		$this->data = array();
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
	
	private function validateSession()
	{
		if($this->get('userId') && $this->get('loggedIn'))
		{
			$this->user = new $this->user_classname();
			$this->set('user', $this->user->find($this->get('userId')));
			return true;
		}
		else
		{
			// attempts to recover a saved login from a cookie
			return $this->recallLogin();
		}
	}
	
	/**
	 * Recover a 'remember me' login
	 * When the user selects 'remember me', we set a cookie that preserves their session on that computer
	 * @return Bool
	 */
	private function recallLogin()
	{
		if(isset($_COOKIE[$this->usercookie_name]) && isset($_COOKIE[$this->keycookie_name]))
		{
			$user_id = $_COOKIE[$this->usercookie_name];
			$session_key = $_COOKIE[$this->keycookie_name];
			
			$users = new $this->user_classname();
			$user = $users->select(array(array("user_id", $username), array("created", $session_key)));
			
			if(is_array($user) && 1 === count($user)) // it works
			{
				return $this->escalate($user[0]);
			}
		}
		return false;
	}
	
	/**
	 * Remembers the session by setting a cookie so each time they visit, they'll be logged in automatically
	 * @return Bool success
	 */
	private function rememberLogin($user)
	{
		return
			setcookie($this->usercookie_name, $user->{$this->userid_fn}(), time()+60*60*24*30, "/", true, true)
				&&
			setcookie($this->keycookie_name, $user->{$this->getCreated}(), time()+60*60*24*30, "/", true, true);
	}
	
	/**
	 * Removes the 'remember' session cookie if it exists
	 * @return Bool
	 */
	private function unrememberLogin()
	{
		if(isset($_COOKIE[$this->usercookie_name]))
		{
			setcookie($this->usercookie_name, "", time() - 3600);
			unset($_COOKIE[$this->usercookie_name]);
		}
		if(isset($_COOKIE[$this->keycookie_name]))
		{
			setcookie($this->keycookie_name, "", time() - 3600);
			unset($_COOKIE[$this->keycookie_name]);
		}
		return true;
	}
	
	/**
	 * Is the current session logged in?
	 * @return Bool
	 */
	public function isLoggedIn()
	{
		return !!$this->get('loggedIn');
	}
	
	/**
	 * Sets a variable to the namespaced session storage
	 * @param $name
	 * @param $value
	 * @return Bool
	 */
	public function set($name, $value)
	{
		$_SESSION[$this->sessionvar_name][$name] = $value;
		return true;
	}
	
	/**
	 * Gets a variable from the namespaced session storage
	 * @param $name
	 * @return Mixed
	 */
	public function get($name)
	{
		return isset($_SESSION[$this->sessionvar_name][$name]) ? $_SESSION[$this->sessionvar_name][$name] : null;
	}
	
	/**
	 * Deletes a variable from the namespaced session storage
	 * @param $name
	 * @return Bool
	 */
	public function delete($name)
	{
		if(isset($_SESSION[$this->sessionvar_name][$name]))
		{
			unset($_SESSION[$this->sessionvar_name][$name]);
		}
		return true;
	}
}