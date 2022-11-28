<?php

class AttributeListComponent extends HTMLList {

	protected function populateItem($item, $key) {
		if(!is_string($key)) $key = "";
		$this->addLabel("attribute_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : "",
			"title" => (isset($item["name"])) ? $item["name"]." (" . $key . ")" : ""
		));

		$this->addLabel("attribute_value", array(
			"html" => (isset($item["value"])) ? nl2br(htmlspecialchars($item["value"], ENT_QUOTES, "UTF-8")) : ""
		));

		//オーダーカスタムフィールドの値は表示しない
		if(strpos($key, "order_customfield") === 0 || strpos($key, "order_date_customfield") === 0) return false;

		//管理画面でも表示させたくない値
		if(isset($item["admin"]) && $item["admin"] === false) return false;
	}
}
