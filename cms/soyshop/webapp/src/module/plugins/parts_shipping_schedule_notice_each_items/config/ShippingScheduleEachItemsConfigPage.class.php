<?php

class ShippingScheduleEachItemsConfigPage extends WebPage {

	private $configObj;
	private $itemId;

	function __construct(){
		SOY2::import("module.plugins.parts_shipping_schedule_notice.util.ShippingScheduleUtil");
		SOY2::import("module.plugins.parts_shipping_schedule_notice_each_items.util.ShippingScheduleEachItemsUtil");
		SOY2::import("module.plugins.parts_shipping_schedule_notice_each_items.component.ScheduleNoticeEachItemsListComponent");
	}

	function doPost(){
		if(soy2_check_token() && isset($_POST["Config"])){
			ShippingScheduleEachItemsUtil::save($_POST["Config"], $this->itemId);
			$this->configObj->redirect("updated&item_id=" . $this->itemId);
		}
	}

	function execute(){
		if(!isset($this->itemId)) SOY2PageController::jump("");

		parent::__construct();

		$item = soyshop_get_item_object($this->itemId);

		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Item.Detail." . $this->itemId),
			"text" => $item->getName() . "の詳細ページに戻る"
		));

		$isInstalledCalendarPlugin = SOYShopPluginUtil::checkIsActive("parts_calendar");
		DisplayPlugin::toggle("no_installed_calendar_plugin", !$isInstalledCalendarPlugin);
		DisplayPlugin::toggle("installed_calendar_plugin", $isInstalledCalendarPlugin);

		$this->addLabel("replace_words_list", array(
			"html" => ShippingScheduleUtil::buildUsabledReplaceWordsList()
		));

		self::buildForm();

		//商品コード
		$this->addLabel("item_code", array(
			"text" => $item->getCode()
		));

		SOY2::import("util.SOYShopPluginUtil");
		DisplayPlugin::toggle("parts_item_detail", SOYShopPluginUtil::checkIsActive("parts_item_detail"));
	}

	private function buildForm(){
		$this->addForm("form");

		$cnf = ShippingScheduleEachItemsUtil::getConfig($this->itemId);
		if(!count($cnf)){	//何も設定していない時はテンプレートから取得
			$cnf = ShippingScheduleEachItemsUtil::getTemplates();
		}

		$this->addCheckBox("hidden", array(
			"name" => "Config[hidden]",
			"value" => 1,
			"selected" => (isset($cnf["hidden"]) && $cnf["hidden"] == 1),
			"label" => "公開側で出荷予定日通知を非表示にする"
		));

		$this->createAdd("notice_list", "ScheduleNoticeEachItemsListComponent", array(
			"list" => ShippingScheduleUtil::getPatterns(),
			"config" => $cnf
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
