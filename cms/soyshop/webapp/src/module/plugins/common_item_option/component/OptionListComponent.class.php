<?php
class OptionListComponent extends HTMLList{

	private $languages;	//多言語化プラグインの設定状況
	private $installedLangPlugin;	//多言語化プラグインがアクティブかどうか

	protected function populateItem($entity, $key){

		$types = ItemOptionUtil::getTypes();

		/* 情報表示用 */
		$this->addLabel("label", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : "",
			"attr:id" => "label_text_" . $key,
		));

		$this->addLabel("type", array(
			"text"=> (isset($entity["type"])) ? $types[$entity["type"]] : "セレクトボックス",
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
			"value" => (isset($entity["name"])) ? $entity["name"] : "",
		));

		$this->addSelect("type_select", array(
			"name" => "obj[type]",
			"options" => $types,
			"attr:id" => "type_select_" . $key,
			"selected" => (isset($entity["type"])) ? $entity["type"] : "セレクトボックス"
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
			"onclick"=>'if(confirm("delete \"' . $entity["name"] . '\"?")){$(\'#delete_submit_' . $key . '\').click();}return false;'
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

		$this->createAdd("language_label_list", "LanguageLabelListComponent", array(
			"list" => $this->languages,
			"labels" => $entity
		));

		$this->addInput("label_jp_input", array(
			"name" => "Option[name]",
			"value" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addInput("option_type", array(
			"name" => "Option[type]",
			"value" => (isset($entity["type"])) ? $entity["type"] : "select"
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
