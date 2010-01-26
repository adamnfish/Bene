<?php
/**
 * This class will handle printing form elements
 * It's purpose is to DRY the 'controller' code
 * @author adamf
 *
 */
class FormHelper
{
	private $formname;
	
	public function __construct($formname)
	{
		$this->formname = $formname;
	}
	
	/**
	 * I'd rather not use this one - just call the function you need, people
	 * @param unknown_type $name
	 * @param unknown_type $type
	 * @param unknown_type $value
	 * @param unknown_type $error
	 * @return unknown_type
	 */
	public function field($name, $type, $value, $error)
	{
		
	}
	
	private function attrsToHTML($attrs)
	{
		
	}
	
	public function formTop($name, $method='post', $attrs=array())
	{
		return "<form name='$name' method='$method'>";
	}
	
	public function formBottom()
	{
		return "</form>";
	}
	
	public function textInput($name, $label, $value='', $attrs=array(), $additional=array())
	{
		$attrs = $this->attrsToHTML($attrs);
		return "<label for='$name'>$label</label><input type='text' id='$name' name='$name' value='$value' $attrs/>";
	}
	
	public function select($name, $label, $options, $value='', $attrs=array(), $additional=array())
	{
		$attrs = $this->attrsToHTML($attrs);
		return "<label for='$name'>$label</label><select id='$name' name='$name'$attrs>" . $this->options($options, $value) . "</select>";
	}
	
	public function options($options, $value)
	{
		$html = array();
		foreach($options as $label => $option)
		{
			$selected = '';
			if(!isset($label))
			{
				$label = $option;
			}
			if($value === $option)
			{
				$selecte = " selected='selected'";
			}
			$html[] = "<option value='$option'$selected>$label</option>";
		}
		return implode("", $html);
	}
	
	// TODO ... this function
	public function jsValidation($rules)
	{
		$rules_source = json_encode($rules); // JSON_FORCE_OBJECT
		$messages = Validator::getErrorMessages(true);
		$messages_source = json_encode($messages);
		
		// need to build the rules and messagse in parallel so the format matches
		
		$js = <<<JS_SOURCE
jQuery(#{$this->formname}).validate({rules: $rules_json});
JS_SOURCE;
		return $js;
	}
}