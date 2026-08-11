<?php
 
class ItemDescriptionClass{
	
	function buildNameArea(string $value=""){
		$html = array();		
		$html[] = "<input type=\"text\" class=\"form-control\" name=\"item_description_plugin[]\" value=\"" . $value."\" />";
		return implode("",$html);
	}
	
	function buildColumnArea(string $value=""){
		$html = array();
		$html[] = "<input type=\"text\" class=\"form-control\" name=\"item_description_column[]\" value=\"" . $value."\" />";
		return implode("",$html);
	}
	
	function buildTextArea(string $value=""){
		$html = array();
		$html[] = "<textarea name=\"item_description_html[]\" class=\"form-control\">" . $value."</textarea>";
		return implode("",$html);
	}
	
	function buildCheckBox(string $name, string $column, bool $flag=false){
		$html = array();
		
		$html[] = "<input type=\"checkbox\" name=\"item_descrption_column[" . $column."]\" id=\"column_" . $column."\" value=\"1\" ";
		if($flag) $html[] = "checked=\"checked\" ";
		$html[] = "/>";
		$html[] = "<label for=\"column_" . $column."\"> : " . $name."</label><br />";
		
		return implode("",$html);
	}
}