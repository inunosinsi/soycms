<?php
class CustomerCategoryVoiceClass{
	
	function buildNameArea(string $value=""){
		return "<input type=\"text\" name=\"customer_category_voice_plugin[]\" value=\"" . $value . "\" />";
	}
	
	function buildTextArea(string $value=""){
		return "<textarea name=\"customer_category_voice_text[]\">" . $value . "</textarea>";		
	}
}