<?php

class TemplateListComponent extends HTMLList {

	function populateItem($entity, $fieldId){

		$txt = InsertStringTemplateUtil::getTextByFieldId($fieldId);

		$this->addLink("field_id_click", array(
			"link" => "javascript:void(0);",
			"id" => "toggle_" . $fieldId,
			"style" => (strlen($txt)) ? "background-color:yellow;" : ""
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=order_mail_insert_template&remove=" . $fieldId),
			"onclick" => 'return confirm("削除してもよろしいでしょうか？");',
			"style" => "margin-left:800px;"
		));

		$this->addLabel("label", array(
			"text" => (isset($entity)) ? $entity : ""
		));

		$this->addModel("field_id_dl", array(
			"attr:id" => "dl_" . $fieldId
		));

		$this->addLabel("field_id", array(
			"text" => $fieldId,
		));

		$this->addTextArea("content", array(
			"name" => "Edit[" . $fieldId . "]",
			"value" => $txt,
			"style" => "height:250px;"
		));
	}
}
