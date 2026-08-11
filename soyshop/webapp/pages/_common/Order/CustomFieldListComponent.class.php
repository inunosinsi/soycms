<?php

class CustomfieldListComponent extends HTMLList {

	protected function populateItem($entity, $key) {
		$name = (isset($entity["name"]) && is_string($entity["name"])) ? $entity["name"] : "";
		$htmlMode = (isset($entity["html"]) && is_bool($entity["html"]) && $entity["html"] == true);

		$this->addLabel("customfield_title", array(
			"text" => $name
		));

		if($htmlMode){
			$val = (isset($entity["value"])) ? $entity["value"] : "";
		}else{
			$val = (isset($entity["value"])) ? nl2br(htmlspecialchars($entity["value"], ENT_QUOTES, "UTF-8"))  : "";
		}
		if(isset($entity["style"])){
			$val = "<span style=\"" . $entity["style"] . "\">" . $val . "</span>";
		}

		if(isset($entity["link"])){
			$val = "<a href=\"" . $entity["link"] . "\" target=\"_blank\">" . $val . "</a>";
		}

		//伝票番号の表示
		if(is_numeric(mb_strpos($name, "伝票番号")) || is_numeric(strpos($name, "slip_number"))) {
			$val = str_replace(",", "<br>", $val);
		}

		$this->addLabel("customfield_value", array(
			"html" => $val
		));
	}
}
