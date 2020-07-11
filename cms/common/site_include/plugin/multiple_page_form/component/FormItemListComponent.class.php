<?php

class FormItemListComponent extends HTMLList {

	private $hash;

	protected function populateItem($entity, $idx){
		$this->addLabel("name", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("type", array(
			"text" => (isset($entity["type"])) ? MPFTypeFormUtil::getTypeText($entity["type"]) : ""
		));

		$this->addInput("order", array(
			"name" => "Config[Order][]",
			"value" => (isset($entity["order"])) ? $entity["order"] : ""
		));

		$this->addCheckBox("required", array(
			"name" => "Config[Required][" . $idx . "]",
			"value" => 1,
			"selected" => (isset($entity["required"]) && (int)$entity["required"])
		));

		//入力フォームの高度な設定
		$this->addModel("is_input_config", array(
			"visible" => (isset($entity["type"]) && ($entity["type"] == MPFTypeFormUtil::TYPE_INPUT))
		));

		$this->addInput("input_type", array(
			"name" => "Config[InputType][" . $idx . "]",
			"value" => (isset($entity["inputType"])) ? $entity["inputType"] : "text"
		));

		$this->addInput("attribute", array(
			"name" => "Config[Attribute][" . $idx . "]",
			"value" => (isset($entity["attribute"])) ? $entity["attribute"] : "",
			"attr:placeholder" => "class=\"sample\" title=\"サンプル\" placeholder=\"\" pattern=\"\""
		));

		$this->addModel("is_advance_config", array(
			"visible" => (isset($entity["type"]) && ($entity["type"] == MPFTypeFormUtil::TYPE_CHECKBOX || $entity["type"] == MPFTypeFormUtil::TYPE_RADIO || $entity["type"] == MPFTypeFormUtil::TYPE_SELECT))
		));

		$this->addLink("advance_link", array(
			"link" => "javascript:void(0);",
			"attr:id" => "advance_link_" . $idx
		));

		$this->addModel("advance_tr", array(
			"attr:id" => "advance_tr_" . $idx
		));

		//高度な設定
		//置換文字列
		$this->addInput("replacement", array(
			"name" => "Config[Replacement][" . $idx . "]",
			"value" => (isset($entity["replacement"])) ? $entity["replacement"] : "",
			"attr:placeholder" => "#NAME#"
		));

		$opt = (isset($entity["option"])) ? $entity["option"] : "";
		$brCnt = substr_count($opt, "\n");
		$this->addTextArea("option", array(
			"name" => "Config[Option][" . $idx . "]",
			"value" => $opt,
			"style" => "height:" . (26 * ($brCnt + 2)) . "px;"
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Plugin.Config?multiple_page_form&detail=" . $this->hash . "&remove=" . $idx),
			"onclick" => "return confirm('削除してもよろしいでしょうか？');"
		));
	}

	function setHash($hash){
		$this->hash = $hash;
	}
}
