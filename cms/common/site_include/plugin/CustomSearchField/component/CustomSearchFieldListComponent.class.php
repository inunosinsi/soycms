<?php
class CustomSearchFieldListComponent extends HTMLList{

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
            "class" => ((isset($entity["option"]) && strlen($entity["option"]))) ? "btn btn-warning" : "btn btn-info"
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

        $this->addModel("with_options", array(
            "visible" => (isset($entity["type"])) ? self::_checkDisplayOptionsForm($entity["type"]) : false
        ));

		//選択項目
		$this->addTextArea("option", array(
			"name" => "config[option]",
			"value" => (isset($entity["option"])) ? $entity["option"] : ""
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

        $this->addInput("update_advance", array(
            "value"=>"設定保存",
            "onclick"=>'$(\'#update_advance_submit_' . $key . '\').click();return false;'
		));

		$this->addModel("checkbox_admin_br_area", array(
            "visible" => (isset($entity["type"])) ? self::_checkInsertBrCheckBox($entity["type"]) : false
        ));

		$this->addCheckBox("checkbox_admin_br", array(
			"name" => "config[br]",
			"value" => 1,
			"selected" => (isset($entity["br"]) && $entity["br"] == 1),
			"label" => "各項目毎に改行コードを追加する"
		));

        $this->addModel("radio_search_form_default_area", array(
            "visible" => (isset($entity["type"])) ? self::_checkRadioDefaultValueCheckBox($entity["type"]) : false
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

		$this->addLink("custom_field_form_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=custom_search_field&customset=" . $key)
		));
    }

    private function _getPrefix(){
		return CustomSearchFieldUtil::PLUGIN_PREFIX;
    }

    private function _buildCheckBoxSuppleTag($key, $options){
		if(!strlen($options)) return "";
        $prefix = self::_getPrefix();

        $text = "";
		$opts = explode("\n", $options);
        foreach($opts as $i => $opt){
            $opt = trim($opt);
            $text .= $opt . "のタグ<br>";
            $text .= "&lt;!-- " . $prefix . ":id=\"" . $key . "_" . $i . "_visible\" --&gt;";
            $text .= "&lt;!-- " . $prefix . ":id=\"" . $key . "_" . $i . "\" --&gt" . $opt . "&lt;!-- /" . $prefix . ":id=\"" . $key . "_" . $i . "\" --&gt";
            $text .= "&lt;!-- /" . $prefix . ":id=\"" . $key . "_" . $i . "_visible\" --&gt;<br><br>";
        }

        return $text;
    }

    private function _checkDisplayOptionsForm($type){
        return ($type === CustomSearchFieldUtil::TYPE_RADIO || $type === CustomSearchFieldUtil::TYPE_CHECKBOX || $type === CustomSearchFieldUtil::TYPE_SELECT);
    }

	private function _checkDisplayOtherItemForm($type){
		return ($type === CustomSearchFieldUtil::TYPE_RADIO || $type === CustomSearchFieldUtil::TYPE_CHECKBOX);
	}

	private function _checkInsertBrCheckBox($type){
		return ($type === CustomSearchFieldUtil::TYPE_RADIO || $type === CustomSearchFieldUtil::TYPE_CHECKBOX);
	}

	private function _checkRadioDefaultValueCheckBox($type){
		return ($type === CustomSearchFieldUtil::TYPE_RADIO);
	}
}
