<?php

class LabelsListComponent extends HTMLList{

	private $accordionConfig;

	function populateItem($entity, $key, $cnt){
		if(!is_string($key)) $key = "";
		
		$this->addLabel("category_name", array(
			"text" => $key,
			"visible" => (!is_int($key) && strlen($key)),
		));

		$this->addLabel("parent_category_count", array(
			"text" => $cnt,
		));

		$this->addLink("parent_category_count_href", array(
			"link" => "#panel_" . $cnt
		));


		/** ラベル一覧ページを開いた時に最初から閉じておく設定 **/
		$isChecked = (is_numeric(array_search($key, $this->accordionConfig)));
		$prop = "panel-collapse collapse";
		if(!$isChecked) $prop .= " in";
		$this->addModel("parent_category_count_prop", array(
			"attr:class" => $prop,
			"attr:id" => "panel_" . $cnt
		));

		$this->addModel("show_default_close", array(
			"visible" => (strlen(trim($key)))	
		));

		$this->addCheckBox("default_close", array(
			"onclick" => "send_default_close(this, '".$key."');",
			"attr:class" => "default_close_checkbox",
			"selected" => $isChecked
		));

		$this->createAdd("list", "LabelListComponent", array(
			"list" => $entity
		));

		return ( count($entity) > 0 );
	}

	private function convert($str){
		$str = str_replace("　", " ", $str);
		return str_replace(" ", "_", $str);
	}

	function setAccordionConfig(array $accordionConfig){
		$this->accordionConfig = $accordionConfig;
	}
}
