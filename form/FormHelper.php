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
	private function error($error)
	{
		
	}
	
	public function formTop($name, $method='post', $action='', $attrs=array())
	{
		$html = "<form name='$name' method='$method' action='' class='userprefsform'>";
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	public function formBottom()
	{
		$html = "</form>";
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	public function fieldTop($class='field')
	{
		$html = "<div class='$class'>";
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	public function fieldBottom()
	{
		$html = "</div>";
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	public function sectionHeading($text)
	{
		$html = "<h2>$text</h2>";
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	public function text($name, $label, $attrs=array(), $additional=array(), $value='', $error='')
	{
		$error = $error ? $error : $this->form->validationError($name);
		$value = $value ? $value : $this->form->get($name);
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop();
		$html[] = "<div class='error text'>$error</div>";
		$html[] = "<label class='text' for='$name'>$label</label><input type='text' id='$name' name='$name' value='$value' $attrs/>";
		$html[] = $this->fieldBottom();
		$html = implode("", $html);
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	public function submit($name, $value='', $attrs=array(), $additional=array(), $error='')
	{
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop("submit");
		$html[] = "<div class='error'>$error</div>";
		$html[] = "<input type='submit' class='submit' id='$name' name='$name' value='$value' $attrs/>";
		$html[] = $this->fieldBottom();
		$html = implode("", $html);
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	public function textarea($name, $label, $attrs=array("rows"=>5), $additional=array(), $value='', $error='')
	{
		$error = $error ? $error : $this->form->validationError($name);
		$value = $value ? $value : $this->form->get($name);
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop();
		$html[] = "<div class='error textarea'>$error</div>";
		$html[] = "<label class='textarea' for='$name'>$label</label><br /><textarea id='$name' name='$name' $attrs>$value</textarea>";
		$html[] = $this->fieldBottom();
		$html = implode("", $html);
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	public function checkbox($name, $label, $options, $attrs=array(), $additional=array(), $value='', $error='')
	{
		$error = $error ? $error : $this->form->validationError($name);
		$value = $value ? $value : $this->form->get($name);
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop();
		$html[] = "<div class='error checkbox'>$error</div>";
		$html[] = "<label class='checkboxgroup'>$label</label>";
		foreach($options as $optionLabel => $option)
		{
			$checked = ($value == $option) ? "checked='checked'" : "";
			$html[] = "<label class='checkbox' for='{$name}_{$option}'>$optionLabel</label>";
			$html[] = "<input type='checkbox' name='{$name}_{$option}' id='{$name}_{$option}' value='$option' $checked/>";
		}
		$html[] = $this->fieldBottom();
		$html = implode("", $html);
		if($this->echo)
		{
			echo $html;
		}
		return $html;
	}
	
	public function select($name, $label, $options, $attrs=array(), $additional=array(), $value='', $error='')
	{
		$error = $error ? $error : $this->form->validationError($name);
		$value = $value ? $value : $this->form->get($name);
		$attrs = $this->attrsToHTML($attrs);
		$html = array();
		$html[] = $this->fieldTop();
		$html[] = "<div class='error select'>$error</div>";
		$html[] = "<label class='select' for='$name'>$label</label><select id='$name' name='$name'$attrs>" . $this->options($options, $value) . "</select>";
		$html[] = $this->fieldBottom();
		$html = implode("", $html);
		if($this->echo)
		{
			echo $html;
		}
		return $html;
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