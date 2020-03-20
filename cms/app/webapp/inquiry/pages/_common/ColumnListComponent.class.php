<?php

class ColumnListComponent extends HTMLList{

	const MODE_ADD = "add";
	const MODE_CHANGE = "change";

	private $mode = self::MODE_ADD;

	protected function populateItem($entity){
		if(!is_a($entity,"SOYInquiry_Column")){
			$entity = new SOYInquiry_Column();
		}

		$obj = $entity->getColumn();
		$label = $obj->getLabel();

		$this->addLabel("label", array(
			"text" => $label,
			"visible" => (strlen($label)>0)
		));

		$this->addLabel("form", array(
			"html" => self::_stripRequired($obj->getForm()),
			"colspan" => (strlen($label)>0) ? "1" : "2"
		));

		if($this->mode == self::MODE_ADD){
			$this->addCheckBox("display_order", array(
				"name" => "Column[order]",
				"label" => "ここに追加",
				"value" => $entity->getOrder() - 1,
			));
		}else{
			$this->addCheckBox("display_order", array(
				"elementId" => "display_order_" . $entity->getId(),
				"name" => "displayOrders",
				"value" => $entity->getOrder(),
			));
		}

		$this->addInput("display_order_hidden", array(
			"name" => "displayOrder[]",
			"value" => $entity->getId()
		));

		$this->addModel("column_row", array(
			"onclick" => "select_row(this,'display_order_".$entity->getId()."');"
		));
	}

	private function _stripRequired($form){
		if(!strlen($form)) return "";
		$form = str_replace("required=\"required\"", "", $form);
		return str_replace("required", "", $form);
	}

	function setMode($mode){
		$this->mode = $mode;
	}
}
