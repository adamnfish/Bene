<?php
/**
 * static Utilities class
 * 
 * contains utility methods, helper functions
 * 
 * @author adamnfish
 *
 */
class Utils
{
	/**
	 * var_print
	 * 
	 * Debugging function
	 * dumps a variable in a pre tag with an optional label
	 * escape_xml swaps < > for htmlentities so that the xml can be displayed in a web page
	 * 
	 * @param $var mixed
	 * @param $label String
	 * @param $escape_xml Bool
	 */
    public function dump($var, $label='', $escape_xml=false){
        static $color = 0;
        $hex1 = $color % 5 ? "00" : "55";
        $hex2 = $color % 2 ? "00" : "55";
        $hex3 = $color % 3 ? "00" : "55";
        if(!$color)
        {
            echo "<style type='text/css'>pre.var_print {font-family:verdana sans-serif;font-size:10pt;}</style>";
        }
        $color++;

        $stack = debug_backtrace();
        echo "<pre class='var_print' style='color:#" . $hex1 . $hex2 . $hex3 . ";'>\n";
        if($label){
            echo "$label:\n";
        }
        echo "Line " . $stack[0]['line'] . "\n";
        echo "File " . $stack[0]['file'] . "\n";
        if($escape_xml)
        {
            echo "\n</pre>\n";
            xml_print($var);
        }
        else
        {
            var_dump($var);
            echo "\n</pre>\n";
        }
    }
    
    public function var_print($var, $label='', $escape_xml=false)
    {
    	return Utils::dump($var, $label, $escape_xml);
    }
    
    public function filter_empty($value)
    {
    	return $value !== "";
    }
    
    public function camelcase($string, $firstUpper=false)
    {
    	$string = str_replace("_", " ", $string);
    	$string = ucwords($string);
    	$string = str_replace(" ", "", $string);
    	if($firstUpper)
    	{
    		return $string;
    	}
    	else
    	{
//    		return lcfirst($string);
    		return strtolower(substr($string,0,1)).substr($string,1);
    	}
    }
    
    /**
     * Takes a float and returns a 2dp string of that number
     * @param $float
     * @return unknown_type
     */
    public function floatToMoney($float, $force_dp=false)
    {
    	if(0 == $float)
    	{
    		return "0";
    	}
    	$negative = $float < 0 ? "-" : "";
    	$float = round($float, 2);
    	if(round($float) == $float)
    	{
    		return $float . ($force_dp ? ".00" : "");
    	}
    	else
    	{
    		$float = (string)$float;
    		$parts = explode(".", $float);
    		return $parts[0] . '.' . str_pad($parts[1], 2, "0");
    	}
    }
}

?>
