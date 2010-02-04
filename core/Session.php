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
				return $this->escalate($user[0], $remember);
			}
			return false;
		}
	}
	
	private function escalate($user, $remember=false)
	{
		session_regenerate_id();
		$this->set('userId', $user->getUserId());
		$this->set('user', $user);
		$this->set('loggedIn', true);
		if($remember)
		{
			$this->rememberLogin($user);
		}
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
	
	private function validateSession()
	{
		if($this->get('userId') && $this->get('loggedIn'))
		{
			$user = new Users();
			$this->set('user', $user->find($this->get('userId')));
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
		if(isset($_COOKIE[USERPREFSSESSION_USER]) && isset($_COOKIE[USERPREFSSESSION_KEY]))
		{
			$user_id = $_COOKIE[USERPREFSSESSION_USER];
			$session_key = $_COOKIE[USERPREFSSESSION_KEY];
			
			$users = new Users();
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
	 * @return unknown_type
	 */
	private function rememberLogin($user)
	{
		return
			setcookie(USERPREFSSESSION_USER, $user->getUserId(), time()+60*60*24*30, "/", true, true)
				&&
			setcookie(USERPREFSSESSION_KEY, $user->getCreated(), time()+60*60*24*30, "/", true, true);
	}
	
	/**
	 * Removes the 'remember' session cookie if it exists
	 * @return Vool
	 */
	private function unrememberLogin()
	{
		if(isset($_COOKIE[USERPREFSSESSION_USER]))
		{
			setcookie(USERPREFSSESSION_USER, "", time() - 3600);
			unset($_COOKIE[USERPREFSSESSION_USER]);
		}
		if(isset($_COOKIE[USERPREFSSESSION_KEY]))
		{
			setcookie(USERPREFSSESSION_KEY, "", time() - 3600);
			unset($_COOKIE[USERPREFSSESSION_KEY]);
		}
		return true;
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