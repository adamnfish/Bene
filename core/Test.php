<?php
abstract class Test
{
	protected $failures = array();
	protected $passCount = 0;
	protected $failCount = 0;
	
	public function run()
	{
		$this->printTestHead();
		$methods = get_class_methods($this);
		$this->beforeTests();
		foreach($methods as $method)
		{
			if(0 === strpos($method, "test_"))
			{
				$this->printMethodProgress($method);
				$this->setup();
				$this->$method();
				$this->teardown();
			}
		}
		$this->afterTests();
		$this->printSummary();
		$this->printTestFoot();
	}
	
	protected function setup()
	{
		
	}
	
	protected function teardown()
	{
		
	}
	
	protected function beforeTests()
	{
		
	}
	
	protected function afterTests()
	{
		
	}
	
	protected function printTestHead()
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head>
<title>FCC User Prefs Unit Tests</title>
<style>
html, body {
	font-family: arial;
	margin: 0;
	padding: 5px;
}
h1 {
	margin-top: 0px;
	padding: 10px 5px;
	font-size: 18pt;
	background-color: #666;
	border: solid 2px #000;
	color: #f7f7f7;
}
h3 {
	padding: 5px;
}
h3.fail {
	background-color: #d99;
	border: solid 2px #966;
}
h3.pass {
	background-color: #9d9;
	border: solid 2px #696;
}
h3.halfpass {
	background-color: #89d;
	border: solid 2px #469;
}
p.fail {
	padding: 3px;
	background-color: #fcc;
	border: solid 1px #eaa;
	margin: 5px;
}
span {
	font-size: 80%;
}
h4 {
	margin: 3px;
	padding: 3px 2px;
	font-size: 10pt;
	background-color: #f7f7f7;
	border: solid 2px #888;
}
</style>
</head>
<body>
<?php
		echo "<h1>" . get_class($this) . "</h1>\n";
	}
	
	protected function printTestFoot()
	{
?>
</body>
</html>
<?php	
	}
	
	protected function printMethodProgress($method)
	{
		echo "<h4>$method</h4>";
	}
	
	protected function printSummary()
	{
		echo "<h3 class='" . ($this->failCount ? "fail" : "pass") . "'>{$this->failCount} assertions failed</h3>";
		foreach($this->failures as $failure)
		{
			if($failure['line'])
			{
				echo "<p class='fail'>{$failure['message']}<br />
<span class='info'>Expected {$failure['expecting']}, received {$failure['received']}</span><br/>
<span class='info'>Line {$failure['line']} of {$failure['file']}</span></p>";
			}
			else
			{
				echo "<p class='fail'>{$failure['message']}</p>";
			}	
		}
		echo "<h3 class='" . ($this->failCount ? "halfpass" : "pass") . "'>{$this->passCount} assertions passed</h3>";
	}
	
	/**
	 * TODO get the line number of the assertion as well from a stack trace
	 * @param String $message
	 * @param Bool $blocking
	 * @return unknown_type
	 */
	protected function failedAssertion($message, $expecting=false, $received=false, $blocking=false)
	{
		$this->failCount++;
		$stack = debug_backtrace();
		$line = $stack[1]['line'];
		$file = $stack[1]['file'];
		$this->failures[] = array(
			'message' => $message,
			'line' => $line,
			'file' => $file,
			'expecting' => var_export($expecting, true),
			'received' => var_export($received, true)
		);
		if($blocking)
		{
			$this->failures[] = array(
				'message' => "Test was terminated by failing assertion with 'blocking' set to true"
			);
			$this->printSummary();
			die();
		}
	}
	
	/*
	 * Assertion types
	 */
	
	/**
	 * Checks the paramater is 'truthy;
	 * @param Mixed $statement
	 * @param String $message
	 * @param Bool $blocking
	 * @return Bool
	 */
	protected function assert($statement, $message, $blocking=false)
	{
		if(false == $statement)
		{
			$this->failedAssertion($message, false, $statement, $blocking);
			return false;
		}
		else
		{
			$this->passCount++;
			return true;
		}
	}
	
	/**
	 * Checks the parameter is the provided number, or an array of length 'provided number'
	 * @param Mixed $statement
	 * @param Int $count
	 * @param String $message
	 * @param Bool $blocking
	 * @return Bool
	 */
	protected function assertCount($statement, $count, $message, $blocking=false)
	{
		if(is_array($statement))
		{
			$statement = count($statement);
		}
		if($count != $statement)
		{
			$this->failedAssertion($message, $count, $statement, $blocking);
			return false;
		}
		else
		{
			$passCount++;
			return true;
		}
	}
	
	/**
	 * Asserts the two provided params are equal
	 * This assertion type allows for them to be loosely equivalent
	 * @param Mixed $statement1
	 * @param Mixed $statament2
	 * @param String $message
	 * @param Bool $blocking
	 * @return Bool
	 */
	protected function assertEqual($statement1, $statament2, $message, $blocking=false)
	{
		if($statement1 != $statament2)
		{
			$this->failedAssertion($message, $statement2, $statement1, $blocking);
			return false;
		}
		else
		{
			$this->passCount++;
			return true;
		}
	}
	
	/**
	 * Asserts the two provided params are exactly equal
	 * This assertion type does not allow them to be loosely equivalent - no type coercion
	 * @param Mixed $statement1
	 * @param Mixed $statament2
	 * @param String $message
	 * @param Bool $blocking
	 * @return Bool
	 */
	protected function assertExactlyEqual($statement1, $statament2, $message, $blocking=false)
	{
		if($statement1 !== $statament2)
		{
			$this->failedAssertion($message, $statement2, $statement1, $blocking);
			return false;
		}
		else
		{
			$this->passCount++;
			return true;
		}
	}
	
	/**
	 * Asserts the provided param is exactly false
	 * This assertion type does not allow them to be loosely equivalent - no type coercion
	 * @param Mixed $statement
	 * @param String $message
	 * @param Bool $blocking
	 * @return Bool
	 */
	protected function assertFalse($statement, $message, $blocking=false)
	{
		if(false !== $statement)
		{
			$this->failedAssertion($message, false, $statement, $blocking);
			return false;
		}
		else
		{
			$this->passCount++;
			return true;
		}
	}
	
	/**
	 * Asserts the provided param is exactly true
	 * This assertion type does not allow them to be loosely equivalent - no type coercion
	 * @param Mixed $statement
	 * @param String $message
	 * @param Bool $blocking
	 * @return Bool
	 */
	protected function assertTrue($statement, $message, $blocking=false)
	{
		if(true !== $statement)
		{
			$this->failedAssertion($message, true, $statement, $blocking);
			return false;
		}
		else
		{
			$this->passCount++;
			return true;
		}
	}
	
	/**
	 * Asserts the provided param is exactly null
	 * This assertion type does not allow them to be loosely equivalent - no type coercion
	 * @param Mixed $statement
	 * @param String $message
	 * @param Bool $blocking
	 * @return Bool
	 */
	protected function assertNull($statement, $message, $blocking=false)
	{
		if(null !== $statement)
		{
			$this->failedAssertion($message, null, $statement, $blocking);
			return false;
		}
		else
		{
			$this->passCount++;
			return true;
		}
	}
}