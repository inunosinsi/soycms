<?php

class SelectedEntriesCustomFieldForm {

	public static function buildForm(int $entryId, string $itemName, array $labelIds){
		$html = array();

		//ラベルと連動の設定
		if(count($labelIds)){
			$classProps = array();
			foreach($labelIds as $labelId){
				$classProps[] = "toggled_by_label_" . $labelId;
			}

			$html[] = "<div class=\"" . implode(" ", $classProps) . "\" style=\"display:none;\">";
		}else{
			$html[] = "<div>";
		}
		$html[] = "<div class=\"form-group\">\n";
		$html[] = "<label>" . $itemName . "</label><br>\n";
		$html[] = "<label>";
		if($entryId > 0 && SelectedEntriesBlockUtil::isCheck($entryId)){
			$html[] = "<input type=\"checkbox\" name=\"" . SelectedEntriesBlockUtil::FIELD_ID . "\" value=\"1\" checked=\"checked\">";
		}else{
			$html[] = "<input type=\"checkbox\" name=\"" . SelectedEntriesBlockUtil::FIELD_ID . "\" value=\"1\">";
		}

		$html[] = " 一覧に加える</label>\n";
		$html[] = "</div>";
		$html[] = "</div>";
		return implode("", $html);
	}
}
