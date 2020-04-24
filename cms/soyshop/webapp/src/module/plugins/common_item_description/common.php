<?php
/*
 * Created on 2011/04/26
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
class ItemDescriptionClass{
	
	function buildNameArea($value=null){
		
		$html = array();
		
		$html[] = "<input type=\"text\" name=\"item_description_plugin[]\" value=\"" . $value."\" />";
		
		return implode("",$html);
	}
	
	function buildColumnArea($value=null){
		
		$html = array();
		
		$html[] = "<input type=\"text\" name=\"item_description_column[]\" value=\"" . $value."\" />";
		
		return implode("",$html);
	}
	
	function buildTextArea($value=null){
		
		$html = array();
		
		$html[] = "<textarea name=\"item_description_html[]\">" . $value."</textarea>";
		
		return implode("",$html);
	}
	
	function buildCheckBox($name,$column,$flag=false){
		
		$html = array();
		
		$html[] = "<input type=\"checkbox\" name=\"item_descrption_column[" . $column."]\" id=\"column_" . $column."\" value=\"1\" ";
		if($flag){
			$html[] = "checked=\"checked\" ";
		}
		$html[] = "/>";
		$html[] = "<label for=\"column_" . $column."\"> : " . $name."</label><br />";
		
		return implode("",$html);
	}
	
}

?>