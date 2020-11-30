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
			"text" => self::getPrefix() . ':id="' . $entity->getFieldId() . '"'
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
			"text" => "高度な設定",
			"onclick" => '$(\'#field_config_' . $key . '\').toggle();',
			"style" => ($entity->getDefaultValue() || $entity->getEmptyValue() || $entity->getHideIfEmpty() || $entity->getOutput() || $entity->getDescription() || $entity->getOption()) ? "background-color:yellow;" : ""
		));

		$this->addModel("field_config", array(
			"attr:id" => "field_config_" . $key
		));

		$this->addInput("default_value", array(
			"name" => "config[defaultValue]",
			"value" => $entity->getDefaultValue()
		));

		$this->addCheckBox("empty_hide", array(
			"name" => "config[hideIfEmpty]",
			"value" => 1,
			"selected" => $entity->getHideIfEmpty(),
			"elementId" => "radio_empty_hide"
		));
		$this->addCheckBox("empty_show", array(
			"name" => "config[hideIfEmpty]",
			"value" => 0,
			"selected" => !$entity->getHideIfEmpty(),
			"elementId" => "radio_empty_show"
		));
		$this->addInput("empty_value", array(
			"name" => "config[emptyValue]",
			"value" => $entity->getEmptyValue()
		));

		$this->addInput("output", array(
			"name" => "config[output]",
			"value" => $entity->getOutput()
		));

		$this->addInput("description", array(
			"name" => "config[description]",
			"value" => $entity->getDescription()
		));

		$this->addInput("not_index", array(
			"name" => "config[isIndex]",
			"value" => 0
		));

		$this->addCheckBox("is_index", array(
			"name" => "config[isIndex]",
			"value" => 1,
			"label" => "この項目を商品の並べ替えに使用する",
			"selected" => ($entity->isIndex())
		));

		$this->addTextArea("option", array(
			"name" => "config[option]",
			"value" => $entity->getOption()
		));

		$this->addModel("with_options", array(
			"visible" => $entity->hasOption()
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

	private function getPrefix(){
		if(isset($_GET["plugin"]) && $_GET["plugin"] == "custom_search_field"){
			SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
			return CustomSearchFieldUtil::PLUGIN_PREFIX;
		}else{
			return "cms";
		}
	}

	function getTypes() {
		return $this->types;
	}
	function setTypes($types) {
		$this->types = $types;
	}
}
