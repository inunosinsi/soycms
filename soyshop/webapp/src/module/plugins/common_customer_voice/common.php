<?php
 
class CustomerVoiceClass{
	
	function buildNameArea(string $value=""){
		$html = array();		
		$html[] = "<input type=\"text\" class=\"form-control\" name=\"customer_voice_plugin[]\" value=\"" . $value."\" />";
		return implode("",$html);
	}
	
	function buildTextArea(string $value=""){
		$html = array();
		$html[] = "<textarea class=\"form-control\" name=\"customer_voice_text[]\">" . $value."</textarea>";
		return implode("",$html);
	}
}