<?php

class CustomFieldListComponent extends HTMLList {

	private $labels = array();

	function populateItem($entity){
		/* 情報表示用 */
		$this->addLabel("label", array(
			"text"=>$entity->getLabel(),
			"id" => "label_text_" . $entity->getId(),
		));

		$this->addLabel("id", array(
			"text"=> $entity->getId(),
		));

		$this->addLabel("type", array(
			"text"=> (is_string($entity->getType()) && isset(CustomField::$TYPES[$entity->getType()])) ? CustomField::$TYPES[$entity->getType()] : "",
			"id" => "type_text_" . $entity->getId(),
		));

		$this->addLabel("display_form", array(
			"text"=>'cms:id="'.$entity->getId().'"'
		));


		/* カスタムフィールド設定変更用 */
		$this->addLink("toggle_update", array(
			"link" => "javascript:void(0)",
			"onclick" => '$(\'#label_input_'.$entity->getId().'\').show();' .
						'$(\'#label_text_'.$entity->getId().'\').hide();' .
						'$(\'#type_select_'.$entity->getId().'\').show();' .
						'$(\'#type_text_'.$entity->getId().'\').hide();' .
						'$(\'#update_link_'.$entity->getId().'\').show();' .
						'$(this).hide();'
		));

		$this->addLink("update_link", array(
			"link" => "javascript:void(0)",
			"id" => "update_link_" . $entity->getId(),
			"onclick" => '$(\'#update_submit_'.$entity->getId().'\').click();' .
						'return false;'
		));

		$this->addInput("update_submit", array(
			"name" => "update_submit",
			"value" => $entity->getId(),
			"attr:id" => "update_submit_".$entity->getId()
		));

		$this->addInput("label_input", array(
			"name" => "label",
			"id" => "label_input_" . $entity->getId(),
			"value" => $entity->getLabel(),
		));

		$this->addSelect("type_select", array(
			"name" => "type",
			"options" => CustomField::$TYPES,
			"id" => "type_select_" . $entity->getId(),
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
			"id" => "delete_submit_".$entity->getId()
		));

		$this->addLink("delete", array(
			"text"=>"削除",
			"link"=>"javascript:void(0);",
			"onclick"=>'if(confirm("delete \"'.$entity->getLabel().'\"?")){$(\'#delete_submit_'.$entity->getId().'\').click();}return false;',
			"attr:id" => "delete_btn_" . $entity->getId()
		));

		/* 高度な設定 */
		$this->addLink("toggle_config", array(
			"link" => "javascript:void(0)",
			"text" => "高度な設定",
			"onclick" => '$(\'#field_config_'.$entity->getId().'\').toggle();',
			"class" => (!$entity->getShowInput() || is_numeric($entity->getLabelId()) || count($entity->getLabelIds())|| $entity->getDefaultValue() || $entity->getEmptyValue() || $entity->getDescription() || $entity->getFixedLabelId() || strlen($entity->getOption())) ? "btn btn-warning" : "btn btn-info",
			"attr:id" => "toggle_config_" . $entity->getId()
		));

		$this->addModel("field_config", array(
			"id" => "field_config_" . $entity->getId()
		));
		
		//表示の切り替え：表示/非表示/ラベルと連動
		$this->addCheckBox("editer_show", array(
			"name" => "config[showInput]",
			"value" => CustomFieldPluginFormPage::SHOW_INPUT_YES,
			"selected" => $entity->getShowInput() && (is_null($entity->getLabelId()) || strlen($entity->getLabelId())==0),
			"label" => "常に表示",
		));
		$this->addCheckBox("editer_hide", array(
			"name" => "config[showInput]",
			"value" => CustomFieldPluginFormPage::SHOW_INPUT_NO,
			"selected" => !$entity->getShowInput(),
			"label" => "常に隠す",
		));
		$this->addCheckBox("editer_label", array(
			"name" => "config[showInput]",
			"value" => CustomFieldPluginFormPage::SHOW_INPUT_LABEL,
			"selected" => (is_string($entity->getLabelId()) && strlen($entity->getLabelId()) || count($entity->getLabelIds())),
			"label" => "ラベルと連動",
		));
		$this->addCheckBox("is_search_item", array(
			"name" => "config[isSearchItem]",
			"value" => 1,
			"selected" => ($entity->getIsSearchItem()),
			"label" => "記事検索の項目にする"
		));
		$this->addSelect("labels", array(
			"options" => $this->labels,
			"property" => "caption",
			"name" => "config[labelId]",
			"selected" => $entity->getLabelId(),
		));

		//複数ラベルの設定
		$this->addLabel("label_ids", array(
			"html" => (is_array($entity->getLabelIds()) && count($this->labels)) ? self::_buildLabelSelectBoxes($entity->getLabelIds()) : "<select><option>----</option></select>"
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
			"value" => $entity->getOption(),
			"attr:id" => "option_form_" . $entity->getId()
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
		$isTmbPlg = CMSPlugin::activeCheck("soycms_thumbnail");
		$this->addModel("is_entry_field_with_thumbnail_plugin", array(
			"visible" => ($entity->getType() == "entry" && $isTmbPlg)
		));

		$this->addLabel("entry_field_tag_example", array(
			"html" => ($entity->getType() == "entry") ? self::_buildExample($entity->getId(), $isTmbPlg) : "" 
		));

		/** 記事フィールド用 **/

		/** リストフィールド用 **/
		$this->addModel("is_list_field", array(
			"visible" => ($entity->getType() == "list")
		));
		/** リストフィールド用 **/

		/** 定義型リストフィールド用 **/
		$this->addModel("is_dllist_field", array(
			"visible" => ($entity->getType() == "dllist")
		));
		/** 定義型リストフィールド用 **/

		$this->addInput("update_advance", array(
			"value"=>"設定保存",
			"onclick"=>'$(\'#update_advance_submit_'.$entity->getId().'\').click();return false;',
			"attr:id" => "update_advance_submit_btn_" . $entity->getId()
		));

		$this->addInput("update_advance_submit", array(
			"name" => "update_advance",
			"value" => $entity->getId(),
			"id" => "update_advance_submit_".$entity->getId(),
			"attr:id" => "update_advance_submit_" . $entity->getId()
		));
	}

	/**
	 * @param string fieldId, bool isTmbPlg サムネイルプラグインが有効であるか？
	 * @return string html
	 */
	private function _buildExample(string $fieldId, bool $isTmbPlg){
		$html = array();
		$html[] = "<h5>記事フィールドで追加されるタグ</h5>";
		$html[] = "<ul>";
		foreach(array("id", "title", "content", "more", "create_date", "label_caption", "label_alias", "blog_title", "blog_uri") as $typ){
			$html[] = "<li>cms:id=\"" . $fieldId . "_" . $typ . "\"</li>";
		}

		if($isTmbPlg){
			foreach(array("upload", "trimming", "thumbnail") as $typ){
				$html[] = "<li>cms:id=\"" . $fieldId . "_" . $typ . "\"</li>";
				$html[] = "<li>cms:id=\"" . $fieldId . "_is_" . $typ . "\"</li>";
				$html[] = "<li>cms:id=\"" . $fieldId . "_no_" . $typ . "\"</li>";
				$html[] = "<li>cms:id=\"" . $fieldId . "_" . $typ . "_text\"</li>";
				$html[] = "<li>cms:id=\"" . $fieldId . "_" . $typ . "_path_text\"</li>";
			}
		}

		$html[] = "</ul>";
		return implode("\n", $html);
	}

	private function _buildLabelSelectBoxes(array $selectedLabelIds){
		$html = array();
		if(count($selectedLabelIds)){
			foreach($selectedLabelIds as $selectedLabelId){
				$html[] = self::_buildLabelSelectBox((int)$selectedLabelId);
			}
		}
		$html[] = self::_buildLabelSelectBox(0);

		return implode("\n", $html);
	}

	private function _buildLabelSelectBox(int $selectedLabelId){
		$html[] = "<select name=\"config[labelIds][]\">";
		$html[] = "<option value=\"\">----</option>";
		foreach($this->labels as $label){
			if($selectedLabelId > 0 && $label->getId() == $selectedLabelId){
				$html[] = "<option value=\"" . $label->getId() . "\" selected=\"selected\">" . $label->getCaption() . "</option>";
			}else{
				$html[] = "<option value=\"" . $label->getId() . "\">" . $label->getCaption() . "</option>";
			}
		}
		$html[] = "</select>";
		return implode("\n", $html);
	}

	function getLabels() {
		return $this->labels;
	}
	function setLabels($labels) {
		$this->labels = $labels;
	}
}
