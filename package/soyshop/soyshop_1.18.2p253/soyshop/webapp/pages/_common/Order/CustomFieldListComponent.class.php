<?php

class CustomfieldListComponent extends HTMLList {

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

		//伝票番号の表示
		if(mb_strpos($item["name"], "伝票番号") !== false || strpos($item["name"], "slip_number")) {
			$val = str_replace(",", "<br>", $val);
		}

		$this->addLabel("customfield_value", array(
			"html" => $val
		));
	}
}
