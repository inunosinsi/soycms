<?php

class ReserveCalendarListPage extends WebPage{

	private $configObj;
	private $itemId;

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");

		$this->y = (isset($_GET["y"]) && (int)$_GET["y"] > 0) ? (int)$_GET["y"] : date("Y");
		$this->m = (isset($_GET["m"]) && (int)$_GET["m"] > 0) ? (int)$_GET["m"] : date("n");
		$this->itemId = (isset($_GET["item_id"]) && strlen($_GET["item_id"])) ? (int)$_GET["item_id"] : null;
	}

	function execute(){
		parent::__construct();

		self::_buildCalendarArea();
		self::_buildExportModuleArea();
	}

	private function _buildCalendarArea(){
		//再予約モード
		if(soy2_check_token() && isset($_GET["re_reserve"]) && is_numeric($_GET["re_reserve"])){
			ReserveCalendarUtil::saveSessionValue("user", (int)$_GET["re_reserve"]);
			if(isset($_GET["re_order_id"]) && is_numeric($_GET["re_order_id"])) ReserveCalendarUtil::saveSessionValue("order", (int)$_GET["re_order_id"]);
		}

		$items = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getRegisteredItemsOnLabel();
		if(count($items) === 1) {	//登録されている商品が１件の場合は強制的にそのカレンダーにする
			$this->itemId = key($items);
		}

		$this->addSelect("item_select", array(
			"name" => "item_id",
			"options" => $items,
			"selected" => $this->itemId,
			"attr:id" => "item_select",
			"attr:onchange" => "redirectAfterSelectOfSch()"
		));

		$this->addSelect("sch_year", array(
			"name" => "y",
			"options" => range($this->y - 1, $this->y + 2),
			"selected" => $this->y,
			"attr:id" => "year_select",
			"attr:onchange" => "redirectAfterSelectOfSch()"
		));

		$this->addSelect("sch_month", array(
			"name" => "m",
			"options" => range(1, 12),
			"selected" => $this->m,
			"attr:id" => "month_select",
			"attr:onchange" => "redirectAfterSelectOfSch()"
		));

		DisplayPlugin::toggle("schedule_register_button", isset($this->itemId));
		$this->addLink("schedule_setting_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&calendar&item_id=" . $this->itemId)
		));

		$this->addLabel("calendar", array(
			"html" => str_replace("<table>", "<table class=\"table\">", SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.CalendarLogic", array("itemId" => $this->itemId))->build($this->y, $this->m))
		));

		$this->addLabel("calendar_css", array(
			"html" => file_get_contents(SOY2::RootDir() . "module/plugins/reserve_calendar/css/calendar.css")
		));
	}

	private function _buildExportModuleArea(){
		/* 出力用 */
		$list = self::_getExportModuleList();
		
		DisplayPlugin::toggle("export_module_menu", (count($list) > 0));
		$this->createAdd("module_list", "_common.Order.ExportModuleListComponent", array(
			"list" => $list
		));

		$this->addForm("export_form", array(
			"action" => SOY2PageController::createLink("Reserve.Export")
		));
	}

	private function _getExportModuleList(){
		SOYShopPlugin::load("soyshop.calendar.export");
		return SOYShopPlugin::invoke("soyshop.calendar.export", array(
			"mode" => "list"
		))->getList();
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
