<?php

class FieldListComponent extends HTMLList{

	private $types;

	protected function populateItem($entity,$key){
		$fieldType = (is_string($entity->getType())) ? $entity->getType() : "";

		/* 情報表示用 */
		$this->addLabel("label", array(
			"text"=>$entity->getLabel(),
			"attr:id" => "label_text_" . $key,
		));

		$this->addLabel("field_text", array(
			"text"=> $entity->getFieldId(),
		));

		$this->addLabel("type", array(
			"text"=> (isset($this->types[$fieldType])) ? $this->types[$fieldType] : "",
			"attr:id" => "type_text_" . $key,
		));

		$this->addLabel("display_form", array(
			"text"=>'cms:id="' . $entity->getFieldId().'"'
		));

		/* 設定変更用 */
		$this->addLink("toggle_update", array(
			"link" => "javascript:void(0)",
			"onclick" => '$(\'#label_input_' . $key . '\').show();' .
						'$(\'#label_text_' . $key . '\').hide();' .
						'$(\'#type_select_' . $key . '\').show();' .
						'$(\'#type_text_' . $key . '\').hide();' .
						'$(\'#update_link_' . $key . '\').show();' .
						'$(this).hide();'
		));

		$this->addLink("update_link", array(
			"link" => "javascript:void(0)",
			"attr:id" => "update_link_" . $key,
			"onclick" => '$(\'#update_submit_' . $key . '\').click();' .
						'return false;'
		));

		$this->addInput("update_submit", array(
			"name" => "update_submit",
			"value" => $entity->getFieldId(),
			"attr:id" => "update_submit_" . $key
		));

		$this->addInput("label_input", array(
			"name" => "obj[label]",
			"attr:id" => "label_input_" . $key,
			"value" => $entity->getLabel(),
		));

		$this->addSelect("type_select", array(
			"name" => "obj[type]",
			"options" => $this->types,
			"attr:id" => "type_select_" . $key,
			"selected" => $fieldType,
		));

		/* 順番変更用 */
		$this->addInput("field_id", array(
			"name" => "field_id",
			"value" => $entity->getFieldId(),
		));

		/* 削除用 */
		$this->addInput("delete_submit", array(
			"name" => "delete_submit",
			"value" => $entity->getFieldId(),
			"attr:id" => "delete_submit_" . $key
		));

		$this->addLink("delete", array(
			"text"=>"削除",
			"link"=>"javascript:void(0);",
			"onclick"=>'if(confirm("delete \"' . $entity->getLabel() . '\"?")){$(\'#delete_submit_' . $key . '\').click();}return false;'
		));

		/* 高度な設定 */
		$this->addLink("toggle_config", array(
			"link" => "javascript:void(0)",
			"text" => "詳細設定",
			"onclick" => '$(\'#field_config_' . $key . '\').toggle();',
			"style" => ($entity->getAttributeName() || $entity->getAttributeDescription() || $entity->getIsAdminOnly() == SOYShop_OrderDateAttribute::DISPLAY_ADMIN_ONLY) ? "background-color:yellow;" : ""
		));


		$this->addModel("field_config", array(
			"attr:id" => "field_config_" . $key
		));

		//表示範囲
		$this->addCheckBox("admin_only", array(
			"name" => "config[isAdminOnly]",
			"value" => SOYShop_OrderDateAttribute::DISPLAY_ADMIN_ONLY,
			"selected" => ($entity->getIsAdminOnly() == SOYShop_OrderDateAttribute::DISPLAY_ADMIN_ONLY),
			"label" => "管理画面側のみフォームを表示する"
		));

		$this->addInput("attribute_name", array(
			"name" => "config[attributeName]",
			"value" => $entity->getAttributeName()
		));
		$this->addTextArea("attribute_description", array(
			"name" => "config[attributeDescription]",
			"value" => $entity->getAttributeDescription()
		));

		$this->addInput("attribute_year_start", array(
			"name" => "config[attributeYearStart]",
			"value" => $entity->getAttributeYearStart(),
			"style" => "text-align:right;ime-mode:inactive;",
			"size" => 5
		));
		$this->addInput("attribute_year_end", array(
			"name" => "config[attributeYearEnd]",
			"value" => $entity->getAttributeYearEnd(),
			"style" => "text-align:right;ime-mode:inactive;",
			"size" => 5
		));

		//許可するのは日付のみ　@ToDo 後々項目は追加していきたい
		$this->addModel("is_order_search", array(
			"visible" => ($fieldType == SOYShop_OrderDateAttribute::CUSTOMFIELD_TYPE_DATE)
		));

		$this->addCheckBox("add_order_search_item", array(
			"name" => "config[orderSearchItem]",
			"value" => 1,
			"selected" => $entity->getOrderSearchItem(),
			"label" => "検索項目として追加する"
		));

		$this->addInput("update_advance", array(
			"value"=>"設定保存",
			"onclick"=>'$(\'#update_advance_submit_' . $key . '\').click();return false;'
		));

		$this->addInput("update_advance_submit", array(
			"name" => "update_advance",
			"value" => $entity->getFieldId(),
			"attr:id" => "update_advance_submit_" . $key
		));

	}

	function getTypes() {
		return $this->types;
	}
	function setTypes($types) {
		$this->types = $types;
	}
}
