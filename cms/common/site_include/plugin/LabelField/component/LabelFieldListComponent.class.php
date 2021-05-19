<?php

class LabelFieldListComponent extends HTMLList {

	function populateItem($cnf, $postfix, $i){
		$label = (is_array($cnf) && isset($cnf["label"]) && is_string($cnf["label"])) ? $cnf["label"] : "";

		/* 情報表示用 */
		$this->addLabel("label", array(
			"text" => $label,
		));
		$this->addLabel("id", array(
			"text" => $postfix,
		));

		$this->addLabel("display_form", array(
			"text"=>'cms:module="labelfield.'.$postfix.'"'
		));
		//
		// /* 順番変更用 */
		$this->addInput("field_id", array(
			"name" => "field_id",
			"value" => $postfix,
		));
		//
		// /* 削除用 */
		$this->addInput("delete_submit", array(
			"name" => "delete_submit",
			"value" => $postfix,
			"id" => "delete_submit_".$i
		));

		$this->addLink("delete", array(
			"text"=>"削除",
			"link"=>"javascript:void(0);",
			"onclick"=>'if(confirm("delete \"'.$label.'\"?")){$(\'#delete_submit_'.$i.'\').click();}return false;'
		));
		//
		// /* 高度な設定 */
		$this->addLink("toggle_config", array(
			"link" => "javascript:void(0)",
			"text" => "高度な設定",
			"onclick" => '$(\'#field_config_'.$i.'\').toggle();',
			//"class" => (!$entity->getShowInput() || $entity->getLabelId() || $entity->getDefaultValue() || $entity->getEmptyValue() || $entity->getDescription() || $entity->getFixedLabelId() || strlen($entity->getOption())) ? "btn btn-warning" : "btn btn-info"
		));

		$this->addModel("field_config", array(
			"id" => "field_config_" . $i
		));
		//
		// //表示の切り替え：表示/非表示/ラベルと連動
		// $this->addCheckBox("editer_show", array(
		// 	"name" => "config[showInput]",
		// 	"value" => CustomFieldPluginFormPage::SHOW_INPUT_YES,
		// 	"selected" => $entity->getShowInput() && strlen($entity->getLabelId())==0,
		// 	"label" => "常に表示",
		// ));
		// $this->addCheckBox("editer_hide", array(
		// 	"name" => "config[showInput]",
		// 	"value" => CustomFieldPluginFormPage::SHOW_INPUT_NO,
		// 	"selected" => !$entity->getShowInput(),
		// 	"label" => "常に隠す",
		// ));
		// $this->addCheckBox("editer_label", array(
		// 	"name" => "config[showInput]",
		// 	"value" => CustomFieldPluginFormPage::SHOW_INPUT_LABEL,
		// 	"selected" => strlen($entity->getLabelId()),
		// 	"label" => "ラベルと連動",
		// ));
		// $this->addSelect("labels", array(
		// 	"options" => $this->labels,
		// 	"property" => "caption",
		// 	"name" => "config[labelId]",
		// 	"selected" => $entity->getLabelId(),
		// ));
		//

		// $this->addInput("description", array(
		// 	"name" => "config[description]",
		// 	"value" => $entity->getDescription()
		// ));
		//

		$this->addInput("update_advance", array(
			"value"=>"設定保存",
			"onclick"=>'$(\'#update_advance_submit_'.$i.'\').click();return false;'
		));

		$this->addInput("update_advance_submit", array(
			"name" => "update_advance",
			"value" => $postfix,
			"id" => "update_advance_submit_".$i
		));
	}
}
