<?php
/**
 * This class will handle printing form elements
 * It's purpose is to DRY the 'controller' code
 * @author adamf
 *
 */
class FormHelper
{
	private $form;
	private $echo;
	
	public function __construct($form, $echo=true)
	{
		$this->form = $form;
		$this->echo = $echo;
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
		$html = array();
		if(count($attrs))
		{
			foreach($attrs as $attr => $value)
			{
				$html[] = "$attr='$value' ";
			}
		}
		return implode('', $html);
	}
	
	private function output($html)
	{
		$html = implode("", $html);
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	/**
	 * I suspect, I'll ened to have (for the JS validation integration):
	 * <div class='error'><span whatevs>error</span></div>
	 * <div class='error'></div>
	 * change the error markup depending if the error is present
	 * This function will handle this if it is necessary
	 * 
	 * @param unknown_type $error
	 * @return unknown_type
	 */
	private function error($error, $type='')
	{
		$errorHTML = $error ? "<div class='error $type'><img src='img/user_prefs_form_error.png' alt='error' />$error</div>" : "<div class='error text'></div>";
		return $errorHTML;
	}
	
	public function formTop($name, $method='post', $action='', $attrs=array())
	{
		$html = Array();
		$html[] = "<form name='$name' method='$method' action='' class='userprefsform'>";
		return $this->output($html);
	}
	
	public function formBottom()
	{
		$html = Array();
		$html[] = "</form>";
		return $this->output($html);
	}
	
	public function fieldTop($class='field')
	{
		$html = Array();
		$html[] = "<div class='$class'>";
		return $this->output($html);
	}
	
	public function fieldBottom()
	{
		$html = Array();
		$html[] = "</div>";
		return $this->output($html);
	}
	
	public function sectionHeading($text)
	{
		$html = Array();
		$html[] = "<h2>$text</h2>";
		return $this->output($html);
	}
	
	public function message($text, $error=true)
	{
		if($text)
		{
			$html = Array();
			$html[] = "<p";
			if($error)
			{
				$html[] = " class='errorfeedback'";
			}
			$html[] = ">$text</p>\n";
			return $this->output($html);
		}
		else
		{
			return "";
		}
	}
	
	public function text($name, $label, $attrs=array(), $additional=array(), $value='', $error='')
	{
		$error = $error ? $error : $this->form->validationError($name);
		$value = $value ? $value : $this->form->get($name);
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop();
		$html[] = $this->error($error, 'text');
		$html[] = "<label class='text' for='$name'>$label</label><input type='text' id='$name' name='$name' value='$value' $attrs/>";
		$html[] = $this->fieldBottom();
		return $this->output($html);
	}
	
	public function email($name, $label, $attrs=array(), $additional=array(), $value='', $error='')
	{
		$error = $error ? $error : $this->form->validationError($name);
		$value = $value ? $value : $this->form->get($name);
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop();
		$html[] = $this->error($error, 'text email');
		$html[] = "<label class='text email' for='$name'>$label</label><input type='email' id='$name' name='$name' value='$value' $attrs/>";
		$html[] = $this->fieldBottom();
		return $this->output($html);
	}
	
	public function submit($name, $value='', $attrs=array(), $additional=array(), $error='')
	{
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop("submit");
		$html[] = $this->error($error, 'submit');
		$html[] = "<input type='submit' class='submit' id='$name' name='$name' value='$value' $attrs/>";
		$html[] = $this->fieldBottom();
		return $this->output($html);
	}
	
	public function textarea($name, $label, $attrs=array("rows"=>5), $additional=array(), $value='', $error='')
	{
		$error = $error ? $error : $this->form->validationError($name);
		$value = $value ? $value : $this->form->get($name);
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop();
		$html[] = $this->error($error, 'textarea');
		$html[] = "<label class='textarea' for='$name'>$label</label><br /><textarea id='$name' name='$name' $attrs>$value</textarea>";
		$html[] = $this->fieldBottom();
		return $this->output($html);
	}
	
	public function checkbox($name, $label, $optionValue, $attrs=array(), $additional=array(), $value='', $error='')
	{
		$error = $error ? $error : $this->form->validationError($name);
		$value = $value ? $value : $this->form->get($name);
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop();
		$html[] = $this->error($error, 'checkbox');

		$checked = ($value == $optionValue) ? "checked='checked'" : "";
		$html[] = "<input type='checkbox' name='{$name}' id='{$name}' value='$optionValue' $checked/>";
		$html[] = "<label class='checkbox single' for='{$name}'>$label</label>";
		
		$html[] = $this->fieldBottom();
		return $this->output($html);
	}
	
	public function select($name, $label, $options, $attrs=array(), $additional=array(), $value='', $error='')
	{
		$error = $error ? $error : $this->form->validationError($name);
		$value = $value ? $value : $this->form->get($name);
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop();
		$html[] = $this->error($error, 'select');
		$html[] = "<label class='select' for='$name'>$label</label><select id='$name' name='$name'$attrs>" . $this->options($options, $value) . "</select>";
		$html[] = $this->fieldBottom();
		return $this->output($html);
	}
	
	private function options($options, $value)
	{
		if(is_string($options))
		{
			$options = explode(",", $options);
		}
		$html = array();
		$options_assoc = array_values($options) === $options ? false : true;
		foreach($options as $option => $label)
		{
			$selected = '';
			if(false === $options_assoc)
			{
				$option = $label;
			}
			if($value == $option)
			{
				$selected = " selected='selected'";
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