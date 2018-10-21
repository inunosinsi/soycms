<?php

class LabelsListComponent extends HTMLList{

	function populateItem($entity, $key, $cnt){
		$this->addLabel("category_name", array(
			"text" => $key,
			"visible" => !is_int($key) && strlen($key),
		));

		$this->addLabel("parent_category_count", array(
			"text" => $cnt,
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
