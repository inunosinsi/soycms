<?php

class LabelsListComponent extends HTMLList{

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

		$this->addModel("parent_category_count_prop", array(
			"attr:id" => "panel_" . $cnt
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
}
