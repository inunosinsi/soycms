<?php

class FieldListComponent extends HTMLList{

	private $types;

	protected function populateItem($entity, $key){
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
			"text"=>'cms:id="'.$entity->getFieldId().'"'
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
			"style" => ($entity->getAttributeDescription() || $entity->getDefaultValue() || $entity->getIsRequired() || $entity->getIsAdminOnly()) ? "background-color:yellow;" : ""
		));

		$this->addModel("field_config", array(
			"attr:id" => "field_config_" . $key
		));

		//必須項目
		$this->addCheckBox("is_required", array(
			"name" => "config[isRequired]",
			"value" => SOYShop_OrderAttribute::IS_REQUIRED,
			"selected" => ($entity->getIsRequired() == SOYShop_OrderAttribute::IS_REQUIRED),
			"label" => "このカスタムフィールドを必須項目にする"
		));

		//表示範囲
		$this->addCheckBox("admin_only", array(
			"name" => "config[isAdminOnly]",
			"value" => SOYShop_OrderAttribute::DISPLAY_ADMIN_ONLY,
			"selected" => ($entity->getIsAdminOnly() == SOYShop_OrderAttribute::DISPLAY_ADMIN_ONLY),
			"label" => "管理画面側のみフォームを表示する"
		));

		//初期値
		$this->addInput("default_value", array(
			"name" => "config[defaultValue]",
			"value" => $entity->getDefaultValue()
		));

		$this->addModel("display_description_type_checkbox", array(
			"visible" => ($fieldType == SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX)
		));

		$this->addTextArea("attribute_description", array(
			"name" => "config[attributeDescription]",
			"value" => $entity->getAttributeDescription()
		));

		$this->addTextArea("option", array(
			"name" => "config[option]",
			"value" => $entity->getOption()
		));

		$this->addModel("with_options", array(
			"visible" => $entity->hasOption()
		));

		$this->addModel("display_option_type_checkbox", array(
			"visible" => ($fieldType == SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_CHECKBOX)
		));

		$this->addModel("with_radio_options", array(
			"visible" => $entity->hasRadioOption()
		));

		$this->addCheckBox("attribute_other", array(
			"name" => "config[attributeOther]",
			"value" => SOYShop_OrderAttribute::CUSTOMFIELD_ATTRIBUTE_OTHER,
			"selected" => ($entity->getAttributeOther() == SOYShop_OrderAttribute::CUSTOMFIELD_ATTRIBUTE_OTHER),
			"elementId" => "attribute_other"
		));
		$this->addInput("attribute_other_text", array(
			"name" => "config[attributeOtherText]",
			"value" => $entity->getAttributeOtherText()
		));

		//許可するのはセレクトボックスのみ　@ToDo 後々項目は追加していきたい
		$this->addModel("is_order_search", array(
			"visible" => ($fieldType == SOYShop_OrderAttribute::CUSTOMFIELD_TYPE_SELECT)
		));

		$this->addCheckBox("add_order_search_item", array(
			"name" => "config[orderSearchItem]",
			"value" => 1,
			"selected" => $entity->getOrderSearchItem(),
			"label" => "検索項目として追加する"
		));

		$this->addModel("with_file_options", array(
			"visible" => $entity->hasFileOption()
		));

		$this->addTextArea("file_option", array(
			"name" => "config[fileOption]",
			"value" => $entity->getFileOption()
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
