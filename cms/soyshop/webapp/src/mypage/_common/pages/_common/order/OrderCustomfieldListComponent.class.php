<?php

class OrderCustomfieldListComponent extends HTMLList {

	protected function populateItem($item, $key) {
		$this->addLabel("customfield_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : ""
		));

		$val = (isset($item["value"])) ? nl2br(htmlspecialchars($item["value"], ENT_QUOTES, "UTF-8"))  : "";
		if(isset($item["style"])){
			$val = "<span style=\"" . $item["style"] . "\">" . $val . "</span>";
		}

		if(isset($item["link"])){
			$val = "<a href=\"" . $item["link"] . "\" target=\"_blank\">" . $val . "</a>";
		}

		$this->addLabel("customfield_value", array(
			"html" => $val
		));

		if(isset($item["name"])){
			//出力履歴周り、返金や再送は表示しない
			if(
				strpos($item["name"], "出力日") !== false ||
				strpos($item["name"], "返金") !== false ||
				strpos($item["name"], "再送") !== false
			){
				return false;
			}
		}
	}
}
