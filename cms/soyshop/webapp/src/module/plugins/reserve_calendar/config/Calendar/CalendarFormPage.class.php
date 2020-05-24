<?php

class CalendarFormPage extends WebPage{

	private $config;
	private $itemId;

	private $y;	//年
	private $m;	//月

	function __construct(){
		//年月
		$this->y = (isset($_GET["y"]) && (int)$_GET["y"] > 0) ? (int)$_GET["y"] : date("Y");
		$this->m = (isset($_GET["m"]) && (int)$_GET["m"] > 0) ? (int)$_GET["m"] : date("n");

		SOY2::imports("module.plugins.reserve_calendar.domain.*");
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
	}

	function doPost(){

		if(soy2_check_token()){
			$schDao = self::schDao();

			//自動登録
			$auto = (isset($_POST["auto_register"]) && $_POST["auto_register"]) ? 1 : 0;
			$autoSeat = (isset($_POST["auto_seat"]) && is_numeric($_POST["auto_seat"])) ? (int)$_POST["auto_seat"] : 0;
			ReserveCalendarUtil::saveAutoConfig($this->itemId, array("register" => $auto, "seat" => $autoSeat));

			if(isset($_POST["register"]) && isset($_POST["column"]) && count($_POST["column"]) && (int)$_POST["unsoldSeat"] >= 0 && isset($_POST["labelId"])){

				$price = (int)$_POST["price"];
				$seat = (int)$_POST["unsoldSeat"];
				foreach($_POST["column"] as $d => $v){

					$obj = new SOYShopReserveCalendar_Schedule();
					$obj->setItemId($this->itemId);
					$obj->setLabelId($_POST["labelId"]);
					$obj->setPrice($price);
					$obj->setYear($this->y);
					$obj->setMonth($this->m);
					$obj->setDay($d);
					$obj->setUnsoldSeat($seat);

					try{
						$schId = $schDao->insert($obj);
					}catch(Exception $e){
						//
					}

					//拡張ポイント
					SOYShopPlugin::load("soyshop.add.price.on.calendar");
					SOYShopPlugin::invoke("soyshop.add.price.on.calendar", array(
						"scheduleId" => $schId
					));
				}

			}else if(isset($_POST["remove"])){
				if(isset($_POST["Schedule"]) && count($_POST["Schedule"])){
					foreach($_POST["Schedule"] as $schId => $v){
						try{
							$schDao->deleteById($schId);
						}catch(Exception $e){
							//var_dump($e);
						}
					}

					$this->config->redirect("removed&calendar&item_id=" . $this->itemId . "&y=" . $this->y . "&m="  . $this->m);
				}
			}

			//CSV一括登録用のフォーマットのダウンロード
			if(isset($_POST["export"])){
				SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ExportLogic")->export();
				exit;
			}

			if(isset($_POST["import"])){
				//一括登録
				if(SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ImportLogic", array("itemId" => $this->itemId))->import()){
					$this->config->redirect("successed&calendar&item_id=" . $this->itemId . "&y=" . $this->y . "&m="  . $this->m);
				//失敗
				}else{
					$this->config->redirect("error&calendar&item_id=" . $this->itemId . "&y=" . $this->y . "&m="  . $this->m);
				}
			}
		}

		$this->config->redirect("updated&calendar&item_id=" . $this->itemId . "&y=" . $this->y . "&m="  . $this->m);
	}

	function execute(){

		parent::__construct();

		DisplayPlugin::toggle("removed", (isset($_GET["removed"])));
		DisplayPlugin::toggle("error", (isset($_GET["error"])));
		DisplayPlugin::toggle("successed", (isset($_GET["successed"])));

		self::buildCalendarForm();
		self::buildImExportForm();
	}

	private function buildCalendarForm(){
		$item = soyshop_get_item_object($this->itemId);

		$this->addLink("reserve_calendar_link", array(
			"link" => SOY2PageController::createLink("Extension.reserve_calendar?item_id=" . $this->itemId),
			"text" => "予約ページに戻る"
		));

		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Item.Detail." . $this->itemId),
			"text" => $item->getName() . "の詳細ページに戻る"
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

		//スケジュールを取得
		$GLOBALS["scheduleList"] = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->getScheduleList($this->itemId, $this->y, $this->m);
		$GLOBALS["labelList"] = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelList($this->itemId);

		$this->addForm("form");

		$this->addLabel("calendar", array(
			"html" => str_replace("<table>", "<table class=\"table\">", SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Config.CalendarLogic", array("itemId" => $this->itemId))->build($this->y, $this->m))
		));

		$this->addLink("holiday_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&holiday&item_id=" . $this->itemId)
		));

		DisplayPlugin::toggle("no_holiday_config", !ReserveCalendarUtil::checkIsDayOfWeekConfig($this->itemId));

		$this->addLink("label_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&label&item_id=" . $this->itemId)
		));

		$labelCnt = count($GLOBALS["labelList"]);
		DisplayPlugin::toggle("no_label", ($labelCnt === 0));
		DisplayPlugin::toggle("has_label", ($labelCnt > 0));

		$this->addSelect("label_list", array(
			"name" => "labelId",
			"options" => $GLOBALS["labelList"]
		));

		$this->addInput("unsold_seat", array(
			"name" => "unsoldSeat",
			"value" => "",
			"style" => "width:60px"
		));

		$this->addInput("price", array(
			"name" => "price",
			"value" => $item->getPrice(),
			"style" => "width:100px"
		));

		//価格に関する拡張ポイント
		SOYShopPlugin::load("soyshop.add.price.on.calendar");
		$this->addLabel("extension_add_price_area", array(
			"html" => SOYShopPlugin::display("soyshop.add.price.on.calendar")
		));

		$autoConfig = ReserveCalendarUtil::getAutoConfig($this->itemId);
		$this->addCheckBox("auto_register", array(
			"name" => "auto_register",
			"value" => 1,
			"selected" => (isset($autoConfig["register"]) && (int)$autoConfig["register"] === 1),
			"label" => "来月から営業日すべてにスケジュールを登録する"
		));

		$this->addInput("auto_seat", array(
			"name" => "auto_seat",
			"value" => (isset($autoConfig["seat"])) ? (int)$autoConfig["seat"] : 0,
			"style" => "width:60px;"
		));

		$this->addLabel("calendar_css", array(
			"html" => file_get_contents(SOY2::RootDir() . "module/plugins/reserve_calendar/css/calendar.css")
		));
	}

	private function buildImExportForm(){
		$this->addForm("export_form");

		$this->addForm("import_form", array(
			"ENCTYPE" => "multipart/form-data"
		));

		$this->addLabel("item_name", array(
			"text" => soyshop_get_item_object($this->itemId)->getName()
		));

		$this->addLabel("label_list_string", array(
			"html" => self::_buildLabelListTable()
		));
	}

	private function _buildLabelListTable(){
		if(!is_array($GLOBALS["labelList"]) || !count($GLOBALS["labelList"])) return "";

		$html = array();
		$html[] = "<table class=\"table table-striped\" style=\"width:300px;float:left;\">";
		$html[] = "<thead>";
		$html[] = "<tr>";
		$html[] = "<th>ラベルID</th>";
		$html[] = "<th>ラベル</th>";
		$html[] = "</tr>";
		$html[] = "</thead>";
		$html[] = "<tbody>";
		foreach($GLOBALS["labelList"] as $labelId => $label){
			$html[] = "<tr>";
			$html[] = "<td>" . $labelId . "</td>";
			$html[] = "<td>" . $label . "</td>";
			$html[] = "</tr>";
		}
		$html[] = "</tbody>";
		$html[] = "</table>";
		$html[] = "<br style=\"clear:both;\">";

		return implode("\n", $html);
	}

	private function schDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO");
		return $dao;
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
