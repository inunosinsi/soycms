<?php

class CustomAliasHiddenFormComponent {

	public static function buildForm(int $entryId){
		$alias = ($entryId > 0) ? CustomAliasUtil::getAliasById($entryId) : "";
		if(!strlen($alias)) return "";
		
		$html = array();
		$html[] = "<input type=\"hidden\" name=\"alias\" value=\"" . htmlspecialchars((string)$alias, ENT_QUOTES, "UTF-8") . "\">";
		return implode("\n", $html);
	}
}
