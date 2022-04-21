<?php
class OptionListComponent extends HTMLList{

	private $languages;	//多言語化プラグインの設定状況
	private $installedLangPlugin;	//多言語化プラグインがアクティブかどうか

	protected function populateItem($entity, $key){
		$optType = (isset($entity["type"])) ? $entity["type"] : ItemOptionUtil::OPTION_TYPE_SELECT;
		$types = ItemOptionUtil::getTypes();
		if(!isset($types[$optType])) $optType = ItemOptionUtil::OPTION_TYPE_SELECT;

		$optName = (isset($entity["name"])) ? $entity["name"] : "";

		/* 情報表示用 */
		$this->addLabel("label", array(
			"text" => $optName,
			"attr:id" => "label_text_" . $key,
		));

		$this->addLabel("type", array(
			"text"=> $types[$optType],
			"attr:id" => "type_text_" . $key,
		));

		$this->addLabel("field_text", array(
			"text"=> (isset($key)) ? $key : "",
		));

		$this->addLabel("display_form", array(
			"text"=>'cms:id="' . $key . '"'
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
			"value" => $key,
			"attr:id" => "update_submit_" . $key
		));

		$this->addInput("label_input", array(
			"name" => "obj[name]",
			"attr:id" => "label_input_" . $key,
			"value" => $optName,
		));

		$this->addSelect("type_select", array(
			"name" => "obj[type]",
			"options" => $types,
			"attr:id" => "type_select_" . $key,
			"selected" => $types[$optType]
		));

		/* 順番変更用 */
		$this->addInput("option_id", array(
			"name" => "option_id",
			"value" => $key,
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
			"onclick"=>'if(confirm("delete \"' . $optName . '\"?")){$(\'#delete_submit_' . $key . '\').click();}return false;'
		));

		//高度な設定 toggleリンク
		$this->addLink("toggle_config", array(
			"link" => "javascript:void(0)",
			"text" => "高度な設定",
			"onclick" => '$(\'#field_config_' . $key . '\').toggle();',
			"style" => (count($entity) > 2) ? "background-color:yellow;" : "",
			"visible" => ($this->installedLangPlugin)
		));

		//高度な設定 入力行
		$this->addModel("field_config", array(
			"attr:id" => "field_config_" . $key
		));

		// 選択して下さいといった初期値　セレクトボックスのみ
		$this->addModel("is_initial_value", array(
			"visible" => ($optType === ItemOptionUtil::OPTION_TYPE_SELECT)
		));

		$this->addCheckBox("initial_value", array(
			"name" => "Option[initial_value]",
			"value" => 1,
			"selected" => (isset($entity["initial_value"]) && (int)$entity["initial_value"] === 1),
			"label" => "選択して下さいのoptionを挿入する"
		));

		$this->createAdd("language_label_list", "LanguageLabelListComponent", array(
			"list" => $this->languages,
			"labels" => $entity
		));

		$this->addInput("label_jp_input", array(
			"name" => "Option[name]",
			"value" => $optName
		));

		$this->addInput("option_type", array(
			"name" => "Option[type]",
			"value" => $optType
		));

		//設定保存 ボタン
		$this->addInput("update_advance", array(
			"value"=>"設定保存",
			"onclick"=>'$(\'#update_advance_submit_' . $key . '\').click();return false;'
		));

		//設定保存 submit ボタンで押される
		$this->addInput("update_advance_submit", array(
			"name" => "update_advance",
			"value" => $key,
			"attr:id" => "update_advance_submit_" . $key
		));
	}

	function setLanguages($languages){
		$this->languages = $languages;
	}

	function setInstalledLangPlugin($installedLangPlugin){
		$this->installedLangPlugin = $installedLangPlugin;
	}
}
