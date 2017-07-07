<?php

class AttributeFormListComponent extends HTMLList {

	protected function populateItem($item, $key) {

		$this->addLabel("attribute_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : ""
		));

		$this->addTextArea("attribute_value", array(
			"name" => "Attribute[" . $key . "]",
			"value" => (isset($item["value"])) ? $item["value"] : "",
			"readonly" => (isset($item["readonly"]) && $item["readonly"] == true)
		));

		//オーダーカスタムフィールドの値は表示しない
		if(strpos($key, "order_customfield") === 0 || strpos($key, "order_date_customfield") === 0) return false;
	}
}
