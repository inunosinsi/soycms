<?php

class AttributeFormListComponent extends HTMLList {

	protected function populateItem($entity, $key) {
		if(!is_string($key)) $key = "";
		$name = (isset($entity["name"]) && is_string($entity["name"])) ? $entity["name"] : "";


		$this->addLabel("attribute_title", array(
			"text" => $name
		));

		$this->addTextArea("attribute_value", array(
			"name" => "Attribute[" . $key . "]",
			"value" => (isset($entity["value"])) ? $entity["value"] : "",
			"readonly" => (isset($entity["readonly"]) && $entity["readonly"] == true)
		));

		//オーダーカスタムフィールドの値は表示しない
		if(strpos($key, "order_customfield") === 0 || strpos($key, "order_date_customfield") === 0) return false;

		//支払い方法も無視する
		if(strpos($key, "payment_") === 0) return false;

		//配送時間帯がある場合も無視する
		if(
			strpos($key, "delivery_") !== false &&
			(strpos($key, "_time_") || strpos($key, ".time"))
		) return false;
	}
}
