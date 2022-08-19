<?php

class DlListFieldListComponent extends HTMLList {

	function populateItem($entity, $i){
		foreach(array("label", "value") as $l){
			$this->addLabel($l, array(
				"soy2prefix" => "cms",
				"text" => (is_array($entity) && isset($entity[$l])) ? $entity[$l] : ""
			));
        }
	}
}
