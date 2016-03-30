<?php
/*
 * Created on 2011/04/26
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class CustomerVoiceClass{
	
	function buildNameArea($value=null){
		
		$html = array();
		
		$html[] = "<input type=\"text\" name=\"customer_voice_plugin[]\" value=\"" . $value."\" />";
		
		return implode("",$html);
	}
	
	function buildTextArea($value=null){
		
		$html = array();
		
		$html[] = "<textarea name=\"customer_voice_text[]\">" . $value."</textarea>";
		
		return implode("",$html);
	}
	
}

?>