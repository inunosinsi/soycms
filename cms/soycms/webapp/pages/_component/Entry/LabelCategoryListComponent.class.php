<?php

class LabelCategoryListComponent extends HTMLList{
	function populateItem($entity, $key, $index){
		$this->addLabel("label_category_name",array(
			"text" => $key,
			"visible" => !is_int($key) && strlen($key),
		));

		$toggleId = "label-".$index;
		$this->addModel("toggle_opened",array(
			"attr:id"	  => "toggle_".$toggleId."_opened",
			//"attr:onclick" => "return toggle_label_list(this, '".$toggleId."');"
		));
		$this->addModel("toggle_closed",array(
			"attr:id" => "toggle_".$toggleId."_closed",
			//"attr:onclick" => "return toggle_label_list(this, '".$toggleId."');"
		));
		$this->addModel("toggle_target",array(
			"attr:id" => $toggleId,
		));

		$this->createAdd("label_list","_component.Entry.LabelListComponent",array(
			"list" => $entity,
		));
	}
}
