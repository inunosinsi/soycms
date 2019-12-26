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
			"html" => self::buildUsabledReplaceWordsList()
		));

		self::buildForm();

		//商品コード
		$this->addLabel("item_code", array(
			"text" => $item->getCode()
		));
	}

	private function buildUsabledReplaceWordsList(){
		$html = array();
		$html[] = "<table class=\"form_list\">";
		$html[] = "<caption>使用できる置換文字列</caption>";
		$html[] = "<thead><tr><th>置換文字列</th><th>種類</th></tr></thead>";
		$html[] = "<tbody>";
		foreach(ShippingScheduleUtil::getUsabledReplaceWords() as $k => $w){
			$html[] = "<tr>";
			$html[] = "<td>##" . $k . "##</td>";
			$html[] = "<td>" . $w . "</td>";
			$html[] = "</tr>";
		}
		$html[] = "</tbody>";
		$html[] = "</table>";
		return implode("\n", $html);
	}

	private function buildForm(){
		$this->addForm("form");

		$this->createAdd("notice_list", "ScheduleNoticeEachItemsListComponent", array(
			"list" => ShippingScheduleUtil::getPatterns(),
			"config" => ShippingScheduleEachItemsUtil::getConfig($this->itemId)
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
