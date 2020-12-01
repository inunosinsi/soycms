<?php
class CustomSearchFieldListComponent extends HTMLList{

    private $languages;
    private $mode = "item";
	private $isCustomField = false;

    protected function populateItem($entity, $key){

        $this->addLabel("key", array(
            "text" => $key
        ));

        $this->addLabel("label", array(
            "text" => (isset($entity["label"])) ? $entity["label"] : ""
        ));

        $this->addLabel("type", array(
            "text" => (isset($entity["type"])) ? CustomSearchFieldUtil::getTypeText($entity["type"]) : ""
        ));

        $this->addLabel("display", array(
            "text" => self::_getPrefix() . ":id=\"" . $key . "\""
        ));

        /* 高度な設定 */
        $this->addLink("toggle_config", array(
            "link" => "javascript:void(0)",
            "text" => "詳細設定",
            "onclick" => '$(\'#field_config_' . $key . '\').toggle();',
            "style" => ((isset($entity["option"]) && is_array($entity["option"])) || (isset($entity["denial"]) && $entity["denial"] == 1) || (isset($entity["showInput"]) && is_numeric($entity["showInput"]))) ? "background-color:yellow;" : ""
        ));

        /* 順番変更用 */
        $this->addInput("field_id", array(
            "name" => "field_id",
            "value" => $key,
        ));

        $this->addModel("field_config", array(
            "attr:id" => "field_config_" . $key
        ));

        /* 削除用 */
        $this->addInput("delete_submit", array(
            "name" => "delete_submit",
            "value" => $key,
            "attr:id" => "delete_submit_" . $key
        ));

        $this->addLink("delete", array(
            "text"=>"削除",
            "link"=>"javascript:void(0);",
            "onclick"=>'if(confirm("delete \"' . $entity["label"] . '\"?")){$(\'#delete_submit_' . $key . '\').click();}return false;'
        ));

		//カテゴリとの連動
		$this->addSelect("show_input", array(
			"name" => "config[showInput]",
			"options" => soyshop_get_category_list(),
			"selected" => (isset($entity["showInput"])) ? $entity["showInput"] : ""
		));

		// 否定モード　現時点では文字列のみ
		$this->addModel("is_denial", array(
            "visible" => (isset($entity["type"])) ? ($entity["type"] == CustomSearchFieldUtil::TYPE_STRING) : false
        ));

		$this->addCheckBox("denial", array(
			"name" => "config[denial]",
			"value" => 1,
			"selected" => (isset($entity["denial"]) && $entity["denial"] == 1),
			"label" => "このカラムを否定として使用する"
		));

        $this->addModel("with_options", array(
            "visible" => (isset($entity["type"])) ? self::_checkDisplayOptionsForm($entity["type"]) : false
        ));

		//その他の項目
		$this->addModel("with_other_item", array(
            "visible" => (isset($entity["type"])) ? self::_checkDisplayOtherItemForm($entity["type"]) : false
        ));

		$this->addCheckBox("use_other_item", array(
			"name" => "config[other]",
			"value" => 1,
			"selected" => (isset($entity["other"]) && is_numeric($entity["other"]) && (int)$entity["other"] === 1),
			"label" => "その他の項目を追加する"
		));

        $this->addModel("sitemap_option_area", array(
            "visible" => (isset($entity["type"])) ? self::_checkDisplaySiteMapOptionsForm($entity["type"]) : false
        ));

        $this->addSelect("custom_search_item_list_page", array(
          "name" => "config[sitemap]",
          "options" => CustomSearchFieldUtil::getCustomSearchItemListPages(),
          "selected" => (isset($entity["sitemap"])) ? (int)$entity["sitemap"] : false
        ));

        //言語ごとのオプション
        $this->createAdd("multi_lang_list", "MultiLanguageOptionListComponent", array(
            "list" => $this->languages,
            "field" => (isset($entity["option"])) ? $entity : array()
        ));

        $this->addInput("update_advance", array(
            "value"=>"設定保存",
            "onclick"=>'$(\'#update_advance_submit_' . $key . '\').click();return false;'
        ));

        $this->addModel("radio_search_form_default_area", array(
            "visible" => (isset($entity["type"]) && $entity["type"] == CustomSearchFieldUtil::TYPE_RADIO)
        ));

        $this->addCheckBox("radio_search_form_default", array(
            "name" => "config[default]",
            "value" => 1,
            "selected" => (isset($entity["default"]) && $entity["default"] == 1),
            "label" => "公開側の検索フォームで未選択時に最初の値にチェックを入れておく"
        ));

        $this->addLink("setting_link", array(
            "link" => SOY2PageController::createLink("Config.Detail?plugin=custom_search_field&collective&field_id=".$key)
        ));

        $this->addModel("checkbox_tag_supple_area", array(
            "visible" => (isset($entity["type"]) && $entity["type"] == CustomSearchFieldUtil::TYPE_CHECKBOX)
        ));

        $this->addLabel("checkbox_tag_supple", array(
            "html" => (isset($entity["type"]) && $entity["type"] == CustomSearchFieldUtil::TYPE_CHECKBOX && isset($entity["option"])) ? self::_buildCheckBoxSuppleTag($key, $entity["option"]) : ""
        ));

        $this->addInput("update_advance_submit", array(
            "name" => "update_advance",
            "value" => $key,
            "attr:id" => "update_advance_submit_" . $key
        ));

		//カスタムサーチフィールド用のカスタムフィールド
		$this->addModel("is_custom_field", array(
			"visible" => $this->isCustomField
		));

		$this->addLink("custom_field_form_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=custom_search_field&customset=" . $key)
		));
    }

    private function _getPrefix(){
		switch($this->mode){
			case "category":
				return CustomSearchFieldUtil::PLUGIN_CATEGORY_PREFIX;
			default:
				return CustomSearchFieldUtil::PLUGIN_PREFIX;
			}
    }

    private function _buildCheckBoxSuppleTag($key, $options){
//        if(!strlen($options)) return "";
        $prefix = self::_getPrefix();

        $text = "";
        foreach($options as $lang => $option){
            if(!strlen($option)) continue;
            $opts = explode("\n", $option);
            foreach($opts as $i => $opt){
                $opt = trim($opt);
                $text .= $opt . "のタグ<br>";
                $text .= "&lt;!-- " . $prefix . ":id=\"" . $key . "_" . $i . "_visible\" --&gt;";
                $text .= "&lt;!-- " . $prefix . ":id=\"" . $key . "_" . $i . "\" --&gt" . $opt . "&lt;!-- /" . $prefix . ":id=\"" . $key . "_" . $i . "\" --&gt";
                $text .= "&lt;!-- /" . $prefix . ":id=\"" . $key . "_" . $i . "_visible\" --&gt;<br><br>";
            }
        }

        return $text;
    }

    private function _checkDisplayOptionsForm($type){
        return ($type === CustomSearchFieldUtil::TYPE_RADIO || $type === CustomSearchFieldUtil::TYPE_CHECKBOX || $type === CustomSearchFieldUtil::TYPE_SELECT);
    }

	private function _checkDisplayOtherItemForm($type){
		return ($type === CustomSearchFieldUtil::TYPE_RADIO || $type === CustomSearchFieldUtil::TYPE_CHECKBOX);
	}

    private function _checkDisplaySiteMapOptionsForm($type){
      if(!SOYShopPluginUtil::checkIsActive("common_sitemap_xml")) return false;
      return self::_checkDisplayOptionsForm($type);
    }

    function setLanguages($languages){
        $this->languages = $languages;
    }
    function setMode($mode){
      $this->mode = $mode;
    }
	function setIsCustomField($isCustomField){
		$this->isCustomField = $isCustomField;
	}
}


class MultiLanguageOptionListComponent extends HTMLList{

    private $field;

    protected function populateItem($entity, $key){
        $this->addModel("label_area", array(
            "visible" => (isset($key) && $key != UtilMultiLanguageUtil::LANGUAGE_JP)
        ));

        $this->addLabel("multi_language_label", array(
            "text" => (isset($entity)) ? $entity : ""
        ));

        $this->addTextArea("option", array(
            "name" => "config[option][" . $key . "]",
            "value" => (isset($key) && isset($this->field["option"][$key]) && strlen($this->field["option"][$key])) ? $this->field["option"][$key] : null
        ));
    }

    function setField($field){
        $this->field = $field;
    }
}
