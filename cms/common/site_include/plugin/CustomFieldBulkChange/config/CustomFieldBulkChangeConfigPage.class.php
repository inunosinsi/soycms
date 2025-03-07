<?php

class CustomFieldBulkChangeConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.CustomFieldBulkChange.func.fn", ".php");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["bulk_change"]["entry_id"]) && is_array($_POST["bulk_change"]["entry_id"]) && count($_POST["bulk_change"]["entry_id"])){
			$fieldId = $_POST["bulk_change"]["id"];
			$fieldValue = trim($_POST["bulk_change"]["value"]);
			foreach($_POST["bulk_change"]["entry_id"] as $entryId){
				$entryId = (int)$entryId;
				$attr = soycms_get_entry_attribute_object($entryId, $fieldId);
				$attr->setValue($fieldValue);
				soycms_save_entry_attribute_object($attr);
			}
		}
		CMSPlugin::redirectConfigPage();
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->addSelect("field", array(
			"name" => "bulk_change[id]",
			"options" => cfb_fn_get_customfield_list(),
			"id" => "customfield_select_box"
		));

		$this->addInput("change_value_field", array(
			"name" => "bulk_change[value]",
			"value" => "",
			"placeholder" => "一括変更したい値"
		));

		$this->addSelect("label", array(
			"name" => "",
			"options" => cfb_fn_get_label_list(),
			"id" => "label_select_box"
		));

		$this->addInput("first_label_id_hidden", array(
			"type" => "hidden",
			"value" =>	0,
			"id" => "first_label_id"
		));

		$this->addInput("site_id_hidden", array(
			"type" => "hidden",
			"value" => UserInfoUtil::getSite()->getSiteId(),
			"id" => "site_id"	
		));

		$this->addLabel("table_js_script", array(
			"html" => file_get_contents(dirname(__DIR__)."/js/table.js")	
		));
	}

	function setPluginObj(CustomFieldBulkChangePlugin $pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
