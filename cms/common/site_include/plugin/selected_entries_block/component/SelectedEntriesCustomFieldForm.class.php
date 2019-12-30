<?php

class SelectedEntriesCustomFieldForm {

	public function buildForm($entryId, $itemName){
		$html = array();
		$html[] = "<div class=\"form-group\">\n";
		$html[] = "<label>" . $itemName . "</label><br>\n";
		$html[] = "<label>";
		if(SelectedEntriesBlockUtil::isCheck($entryId)){
			$html[] = "<input type=\"checkbox\" name=\"" . SelectedEntriesBlockUtil::FIELD_ID . "\" value=\"1\" checked=\"checked\">";
		}else{
			$html[] = "<input type=\"checkbox\" name=\"" . SelectedEntriesBlockUtil::FIELD_ID . "\" value=\"1\">";
		}

		$html[] = " 一覧に加える</label>\n";
		$html[] = "</div>";
		return implode("", $html);
	}
}
