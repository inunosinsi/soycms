<?php
class CustomerCategoryVoiceClass{
	
	function buildNameArea(string $value=""){
		return "<input type=\"text\" class=\"form-control\" name=\"customer_category_voice_plugin[]\" value=\"" . $value . "\" />";
	}
	
	function buildTextArea(string $value=""){
		return "<textarea class=\"form-control\" name=\"customer_category_voice_text[]\">" . $value . "</textarea>";		
	}
}