<?php

class LabelCustomFieldListComponent extends HTMLList {

	function populateItem($entity, $i){
		static $i = 0;
		$i++;

		/* 情報表示用 */
		$this->addLabel("label", array(
			"text"=>$entity->getLabel(),
			"id" => "label_text_" . $i,
		));

		$this->addLabel("id", array(
			"text"=> $entity->getId(),
		));

		$this->addLabel("type", array(
			"text"=> (is_string($entity->getType()) && isset(CustomField::$TYPES[$entity->getType()])) ? CustomField::$TYPES[$entity->getType()] : "",
			"id" => "type_text_" . $i,
		));

		$this->addLabel("display_form", array(
			"text"=>'cms:id="'.$entity->getId().'"'
		));


		/* カスタムフィールド設定変更用 */
		$this->addLink("toggle_update", array(
			"link" => "javascript:void(0)",
			"onclick" => '$(\'#label_input_'.$i.'\').show();' .
						'$(\'#label_text_'.$i.'\').hide();' .
						'$(\'#type_select_'.$i.'\').show();' .
						'$(\'#type_text_'.$i.'\').hide();' .
						'$(\'#update_link_'.$i.'\').show();' .
						'$(this).hide();'
		));

		$this->addLink("update_link", array(
			"link" => "javascript:void(0)",
			"id" => "update_link_" . $i,
			"onclick" => '$(\'#update_submit_'.$i.'\').click();' .
						'return false;'
		));

		$this->addInput("update_submit", array(
			"name" => "update_submit",
			"value" => $entity->getId(),
			"attr:id" => "update_submit_".$i
		));

		$this->addInput("label_input", array(
			"name" => "label",
			"id" => "label_input_" . $i,
			"value" => $entity->getLabel(),
		));

		$this->addSelect("type_select", array(
			"name" => "type",
			"options" => CustomField::$TYPES,
			"id" => "type_select_" . $i,
			"selected" => $entity->getType(),
		));

		/* 順番変更用 */
		$this->addInput("field_id", array(
			"name" => "field_id",
			"value" => $entity->getId(),
		));


		/* 削除用 */
		$this->addInput("delete_submit", array(
			"name" => "delete_submit",
			"value" => $entity->getId(),
			"id" => "delete_submit_".$i
		));

		$this->addLink("delete", array(
			"text"=>"削除",
			"link"=>"javascript:void(0);",
			"onclick"=>'if(confirm("delete \"'.$entity->getLabel().'\"?")){$(\'#delete_submit_'.$i.'\').click();}return false;'
		));

		/* 高度な設定 */
		$this->addLink("toggle_config", array(
			"link" => "javascript:void(0)",
			"text" => "高度な設定",
			"onclick" => '$(\'#field_config_'.$i.'\').toggle();',
			"class" => (!$entity->getShowInput() || $entity->getLabelId() || $entity->getDefaultValue() || $entity->getEmptyValue() || $entity->getDescription() || $entity->getFixedLabelId() || strlen($entity->getOption())) ? "btn btn-warning" : "btn btn-info"
		));

		$this->addModel("field_config", array(
			"id" => "field_config_" . $i
		));

		$this->addInput("default_value", array(
			"name" => "config[defaultValue]",
			"value" => $entity->getDefaultValue()
		));

		$this->addCheckBox("empty_hide", array(
			"name" => "config[hideIfEmpty]",
			"value" => 1,
			"selected" => $entity->getHideIfEmpty(),
			"label" => "表示しない",
		));
		$this->addCheckBox("empty_show", array(
			"name" => "config[hideIfEmpty]",
			"value" => 0,
			"selected" => !$entity->getHideIfEmpty(),
			"label" => "指定の値を出力",
		));
		$this->addInput("empty_value", array(
			"name" => "config[emptyValue]",
			"value" => $entity->getEmptyValue()
		));

		$this->addInput("output", array(
			"name" => "config[output]",
			"value" => $entity->getOutput()
		));

		$this->addModel("use_extra", array(
			"visible" => $entity->hasExtra(),
		));

		$this->addTextArea("extra_outputs", array(
			"name" => "config[extraOutputs]",
			"value" => $entity->getExtraOutputs(),
		));

		$this->addTextArea("option", array(
			"name" => "config[option]",
			"value" => $entity->getOption()
		));

		//ペアフィールド用
		$this->addModel("is_pair", array(
			"visible" => ($entity->getType() == "pair")
		));

		$this->addLabel("pair_form", array(
			"html" => ($entity->getType() == "pair") ? $entity->getPairForm() : ""
		));

		$this->addInput("description", array(
			"name" => "config[description]",
			"value" => $entity->getDescription()
		));

		$this->addModel("with_options", array(
			"visible" => $entity->hasOption()
		));

		/** 記事フィールド用 **/
		$this->addModel("is_entry_field", array(
			"visible" => ($entity->getType() == "entry")
		));
		$this->addSelect("fixed_label_id", array(
			"name" => "config[fixedLabelId]",
			"options" => $entity->getLabels(),
			"selected" => $entity->getFixedLabelId()
		));

		/** 記事フィールド用 **/

		$this->addInput("update_advance", array(
			"value"=>"設定保存",
			"onclick"=>'$(\'#update_advance_submit_'.$i.'\').click();return false;'
		));

		$this->addInput("update_advance_submit", array(
			"name" => "update_advance",
			"value" => $entity->getId(),
			"id" => "update_advance_submit_".$i
		));
	}
}
