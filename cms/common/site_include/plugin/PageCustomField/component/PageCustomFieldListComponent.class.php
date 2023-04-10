<?php

class PageCustomFieldListComponent extends HTMLList {

	private $pages = array();

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

		$typeText = (is_string($entity->getType()) && isset(CustomField::$TYPES[$entity->getType()])) ? CustomField::$TYPES[$entity->getType()] : "";
		if(!strlen($typeText) && is_string($entity->getType()) && isset(PageCustomField::$TYPES[$entity->getType()])) $typeText = PageCustomField::$TYPES[$entity->getType()];
		$this->addLabel("type", array(
			"text"=> $typeText,
			"id" => "type_text_" . $i,
		));

		$soyTag = ($entity->getType() != "id" && $entity->getType() != "class") ? "cms" : "pcf";
		$this->addLabel("display_form", array(
			"text"=>$soyTag.':id="'.$entity->getId().'"'
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
			"class" => (!$entity->getShowInput() || count($entity->getPageIds()) || $entity->getDefaultValue() || $entity->getEmptyValue() || $entity->getDescription() || $entity->getFixedLabelId() || strlen($entity->getOption())) ? "btn btn-warning" : "btn btn-info"
		));

		$this->addModel("field_config", array(
			"id" => "field_config_" . $i
		));

		$this->addCheckBox("editer_show", array(
			"name" => "config[showInput]",
			"value" => PageCustomFieldFormPage::SHOW_INPUT_YES,
			"selected" => $entity->getShowInput() && (is_null($entity->getPageId()) || strlen($entity->getPageId())==0),
			"label" => "常に表示",
		));
		$this->addCheckBox("editer_page", array(
			"name" => "config[showInput]",
			"value" => PageCustomFieldFormPage::SHOW_INPUT_PAGE,
			"selected" => (is_string($entity->getPageId()) && strlen($entity->getPageId()) || count($entity->getPageIds())),
			"label" => "ページと連動",
		));

		//複数ページの設定
		$this->addLabel("page_ids", array(
			"html" => (is_array($entity->getPageIds()) && count($this->pages)) ? self::_buildPageSelectBoxes($entity->getPageIds()) : "<select><option>----</option></select>"
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
			"onclick"=>'$(\'#update_advance_submit_'.$i.'\').click();return false;'
		));

		$this->addInput("update_advance_submit", array(
			"name" => "update_advance",
			"value" => $entity->getId(),
			"id" => "update_advance_submit_".$i
		));
	}

	private function _buildPageSelectBoxes(array $selectedPageIds){
		$html = array();
		if(count($selectedPageIds)){
			foreach($selectedPageIds as $selectedPageId){
				$html[] = self::_buildPageSelectBox((int)$selectedPageId);
			}
		}
		$html[] = self::_buildPageSelectBox(0);

		return implode("\n", $html);
	}

	private function _buildPageSelectBox(int $selectedPageId){
		$html[] = "<select name=\"config[pageIds][]\">";
		$html[] = "<option value=\"\">----</option>";
		foreach($this->pages as $page){
			if($selectedPageId > 0 && $page->getId() == $selectedPageId){
				$html[] = "<option value=\"" . $page->getId() . "\" selected=\"selected\">" . $page->getTitle() . "</option>";
			}else{
				$html[] = "<option value=\"" . $page->getId() . "\">" . $page->getTitle() . "</option>";
			}
		}
		$html[] = "</select>";
		return implode("\n", $html);
	}

	function getPages() {
		return $this->pages;
	}
	function setPages($pages) {
		$this->pages = $pages;
	}
}
