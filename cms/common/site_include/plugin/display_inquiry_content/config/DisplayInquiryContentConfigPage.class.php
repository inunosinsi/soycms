<?php

class DisplayInquiryContentConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){
		SOY2::import("site_include.plugin.display_inquiry_content.util.DisplayInquiryContentUtil");
		SOY2::imports("site_include.plugin.display_inquiry_content.component.*");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["formIdRegiseter"])){
				$formId = (isset($_POST["Config"]["formId"])) ? (int)$_POST["Config"]["formId"] : null;
				$this->pluginObj->setFormId($formId);
				$this->pluginObj->setConnects(array());	//初期化

				CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
				CMSPlugin::redirectConfigPage();
			}

			if(isset($_POST["connectRegister"])){
				$values = (isset($_POST["Config"]["customfield"])) ? $_POST["Config"]["customfield"] : array();

				//メモリの節約の為の整形
				if(count($values)){
					$list = array();
					foreach($values as $columnId => $fieldId){
						if(!strlen($fieldId)) continue;
						$list[$columnId] = $fieldId;
					}
					$values = $list;
				}

				$this->pluginObj->setConnects($values);

				//ラベルIDの登録
				$labelId = (isset($_POST["Config"]["labelId"]) && strlen($_POST["Config"]["labelId"])) ? (int)$_POST["Config"]["labelId"] : null;
				$this->pluginObj->setLabelId($labelId);

				$this->pluginObj->setIsPublished($_POST["Config"]["isPublished"]);

				CMSPlugin::savePluginConfig($this->pluginObj->getId(), $this->pluginObj);
				CMSPlugin::redirectConfigPage();
			}
		}

	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		//SOY Inquiryのフォームを取得
		$formId = $this->pluginObj->getFormId();
		$this->addSelect("form", array(
			"name" => "Config[formId]",
			"options" => DisplayInquiryContentUtil::getInquiryFormList(),
			"selected" => $formId
		));

		$this->addModel("form_button", array(
			"attr:onclick" => (isset($formId)) ? "return confirm('カスタムフィールドとの連携設定が初期化されますがよろしいですか？')" : ""
		));

		DisplayPlugin::toggle("active_custom_field", CMSPlugin::activeCheck("CustomFieldAdvanced"));
		DisplayPlugin::toggle("inactive_custom_field", !CMSPlugin::activeCheck("CustomFieldAdvanced"));

		//formIdが設定されている場合はカスタムフィールドとの連携を設定する
		DisplayPlugin::toggle("is_config", isset($formId) && (int)$formId > 0);

		$this->addForm("connect_form");

		$this->createAdd("column_list", "ColumnListComponent", array(
			"list" => DisplayInquiryContentUtil::getColumnsByFormId($formId),
			"customfields" => DisplayInquiryContentUtil::getCustomFieldConfig(),
			"connects" => $this->pluginObj->getConnects()
		));


		//ラベルの設定
		$this->addSelect("label", array(
			"name" => "Config[labelId]",
			"options" => DisplayInquiryContentUtil::getLabelList(),
			"selected" => $this->pluginObj->getLabelId()
		));

		//記事の公開設定
		SOY2::import("domain.cms.Entry");
		$this->addCheckBox("no_published", array(
			"name" => "Config[isPublished]",
			"value" => 0,
			"selected" => ($this->pluginObj->getIsPublished() != Entry::ENTRY_ACTIVE),
			"label" => "非公開"
		));

		$this->addCheckBox("is_published", array(
			"name" => "Config[isPublished]",
			"value" => Entry::ENTRY_ACTIVE,
			"selected" => ($this->pluginObj->getIsPublished() == Entry::ENTRY_ACTIVE),
			"label" => "公開"
		));
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
