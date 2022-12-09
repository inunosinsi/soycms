<?php

class FieldListComponent extends HTMLList{

	private $types;

	protected function populateItem($entity,$key){
		$fieldType = (is_string($entity->getType())) ? $entity->getType() : "";

		/* 情報表示用 */

		//ID
		$this->addLabel("label", array(
			"text"=>$entity->getLabel(),
			"attr:id" => "label_text_" . $key,
		));

		//フィールド名
		$this->addLabel("field_text", array(
			"text"=> $entity->getFieldId(),
		));

		//タイプ
		$this->addLabel("type", array(
			"text"=> (isset($this->types[$fieldType])) ? $this->types[$fieldType] : "",
			"attr:id" => "type_text_" . $key,
		));

		$this->addLabel("display_form", array(
			"text"=>'soy:id="user_customfield_'.$entity->getFieldId().'"',
			"style" => "font-size:0.9em;"
		));


		/* 設定変更用 */
		$this->addLink("toggle_update", array(
			"link" => "javascript:void(0)",
			"onclick" => '$(\'#label_input_'.$key.'\').show();' .
						'$(\'#label_text_'.$key.'\').hide();' .
						'$(\'#type_select_'.$key.'\').show();' .
						'$(\'#type_text_'.$key.'\').hide();' .
						'$(\'#update_link_'.$key.'\').show();' .
						'$(this).hide();'
		));

		//設定変更 リンク
		$this->addLink("update_link", array(
			"link" => "javascript:void(0)",
			"attr:id" => "update_link_" . $key,
			"onclick" => '$(\'#update_submit_'.$key.'\').click();' .
						'return false;'
		));

		//変更を保存する リンク
		$this->addInput("update_submit", array(
			"name" => "update_submit",
			"value" => $entity->getFieldId(),
			"attr:id" => "update_submit_" . $key
		));

		//タイプ
		$this->addInput("label_input", array(
			"name" => "obj[label]",
			"attr:id" => "label_input_" . $key,
			"value" => $entity->getLabel(),
		));

		//変更時のタイプ選択 セレクトボックス
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
			"onclick"=>'if(confirm("delete \"'.$entity->getLabel().'\"?")){$(\'#delete_submit_'.$key.'\').click();}return false;'
		));

		/* 高度な設定 */

		//高度な設定 toggleリンク
		$this->addLink("toggle_config", array(
			"link" => "javascript:void(0)",
			"text" => "高度な設定",
			"onclick" => '$(\'#field_config_'.$key.'\').toggle();',
			"style" => ($entity->getDefaultValue() || $entity->getIsRequired() || $entity->getOption()) ? "background-color:yellow;" : ""
//			"visible" => $entity->hasOption()
		));

		//高度な設定 入力行
		$this->addModel("field_config", array(
			"attr:id" => "field_config_" . $key
		));

		//必須項目
		$this->addCheckBox("is_required", array(
			"name" => "config[isRequired]",
			"value" => SOYShop_UserAttribute::IS_REQUIRED,
			"selected" => ($entity->getIsRequired() == SOYShop_UserAttribute::IS_REQUIRED),
			"label" => "このカスタムフィールドを必須項目にする"
		));

		//初期値
		$this->addInput("default_value", array(
			"name" => "config[defaultValue]",
			"value" => $entity->getDefaultValue()
		));

		$this->addModel("display_description_type_checkbox", array(
			"visible" => ($fieldType == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_CHECKBOX)
		));

		$this->addTextArea("attribute_description", array(
			"name" => "config[attributeDescription]",
			"value" => $entity->getAttributeDescription()
		));

		//選択項目
		$this->addTextArea("option", array(
			"name" => "config[option]",
			"value" => $entity->getOption()
		));

		//選択項目 表示
		$this->addModel("with_options", array(
			"visible" => $entity->hasOption()
		));


		//設定保存 ボタン
		$this->addInput("update_advance", array(
			"value"=>"設定保存",
			"onclick"=>'$(\'#update_advance_submit_'.$key.'\').click();return false;'
		));

		//設定保存 submit ボタンで押される
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
?>
