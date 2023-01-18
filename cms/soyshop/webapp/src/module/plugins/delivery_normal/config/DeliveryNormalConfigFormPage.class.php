<?php
class DeliveryNormalConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
		SOY2::import("module.plugins.delivery_normal.component.DeliveryPriceListComponent");
		SOY2::import("module.plugins.delivery_normal.component.DeliveryTimeConfigListComponent");
		SOY2::import("module.plugins.delivery_normal.component.FeeExceptionListComponent");
		SOY2DAOFactory::importEntity("config.SOYShop_Area");
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
		SOY2::import("util.SOYShopPluginUtil");
	}

	function doPost(){

		if(soy2_check_token()){

			if(isset($_POST["title"])){
				DeliveryNormalUtil::saveTitle($_POST["title"]);
			}
			if(isset($_POST["description"])){
				DeliveryNormalUtil::saveDescription($_POST["description"]);
			}

			if(isset($_POST["config"])){
				DeliveryNormalUtil::saveFreePrice($_POST["config"]);
			}

			if(isset($_POST["price"])){
				DeliveryNormalUtil::savePrice($_POST["price"]);
			}

			if(isset($_POST["delivery_time_config"])){
				DeliveryNormalUtil::saveDeliveryTimeConfig($_POST["delivery_time_config"]);

				//配達時間帯を使用するかどうかの設定
				$useDeliveryTime["use"] = (isset($_POST["use_delivery_time"]) && $_POST["use_delivery_time"] == 1) ? 1 : 0;
				DeliveryNormalUtil::saveUseDeliveryTimeConfig($useDeliveryTime);
			}

			if(isset($_POST["Date"])){
				//jQuryUIのファイルをコピーする
				self::_copyFiles();
				DeliveryNormalUtil::saveDeliveryDateConfig($_POST["Date"]);
			}

			/** 配送料無料の例外設定 **/
			if(isset($_POST["Add"]) && isset($_POST["Add"]["code"]) && is_array($_POST["Add"]["code"]) && count($_POST["Add"]["code"])){
				$cnfs = DeliveryNormalUtil::getExceptionFeeConfig();
				$cnfs[] = $_POST["Add"];
				DeliveryNormalUtil::saveExceptionFeeConfig($cnfs);
			}

			if(isset($_POST["Change"])){
				DeliveryNormalUtil::saveExceptionFeeConfig($_POST["Change"]);
			}
			/** 配送料無料の例外設定ここまで **/

			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		self::_buildTextForm();
		self::_buildPriceForm();
		self::_buildTimeForm();
		self::_buildDateForm();
	}

	private function _copyFiles(){
		$filesDir = dirname(dirname(__FILE__)) . "/files/";
		$commonDir = SOYSHOP_SITE_DIRECTORY . "themes/common/";

		//JSファイルへのコピー 常に上書きする
		$jsDir = $commonDir . "js/";
		foreach(array("jquery-ui.min", "datepicker-ja") as $fileName){
			copy($filesDir . $fileName . ".js", $jsDir . $fileName . ".js");
		}

		//CSSファイルのコピー
		copy($filesDir . "jquery-ui.min.css", $commonDir . "css/jquery-ui.min.css");

		//CSSのイメージファイル
		$imageFiles = scandir($filesDir . "images/");
		$imageDir = $commonDir . "css/images/";
		if(!file_exists($imageDir)) mkdir($imageDir);
		foreach($imageFiles as $imgFile){
			if(strpos($imgFile, ".") === 0) continue;
			copy($filesDir . "images/" . $imgFile, $imageDir . $imgFile);
		}
	}

	private function _buildTextForm(){

		$this->addInput("title", array(
			"value" => DeliveryNormalUtil::getTitle(),
			"name"  => "title"
		));
		$this->addTextArea("description", array(
			"value" => DeliveryNormalUtil::getDescription(),
			"name"  => "description"
		));
	}

	private function _buildPriceForm(){
		$free = DeliveryNormalUtil::getFreePrice();

		$isFreeConfig = (isset($free["free"]) && is_numeric($free["free"]));
		$this->addInput("price_free", array(
			"name" => "config[free]",
			"value" => ($isFreeConfig) ? $free["free"] : ""
		));

		DisplayPlugin::toggle("price_free_annotation", $isFreeConfig);

		$this->createAdd("prices", "DeliveryPriceListComponent", array(
			"list"   => SOYShop_Area::getAreas(),
			"prices" => DeliveryNormalUtil::getPrice()
		));

		//配送料無料の例外の設定
		self::_buildExceptionListArea();
		self::_buildExceptionForm();
	}

	private function _buildExceptionListArea(){
		$cnf = DeliveryNormalUtil::getExceptionFeeConfig();
		DisplayPlugin::toggle("exception_config", count($cnf));

		$this->createAdd("exception_list", "FeeExceptionListComponent", array(
			"list" => $cnf
		));
	}

	private function _buildExceptionForm(){
		$this->addForm("exception_form");

		//新規作成の方
		$this->addCheckBox("new_radio_and", array(
			"name" => "Add[pattern]",
			"value" => DeliveryNormalUtil::PATTERN_AND,
			"selected" => true,
			"label" => DeliveryNormalUtil::getPatternText(DeliveryNormalUtil::PATTERN_AND)
		));

		$this->addCheckBox("new_radio_or", array(
			"name" => "Add[pattern]",
			"value" => DeliveryNormalUtil::PATTERN_OR,
			"label" => DeliveryNormalUtil::getPatternText(DeliveryNormalUtil::PATTERN_OR)
		));

		$this->addCheckBox("new_radio_match", array(
			"name" => "Add[pattern]",
			"value" => DeliveryNormalUtil::PATTERN_MATCH,
			"label" => DeliveryNormalUtil::getPatternText(DeliveryNormalUtil::PATTERN_MATCH)
		));
	}

	private function _buildTimeForm(){
		$time_config = DeliveryNormalUtil::getDeliveryTimeConfig();
		while(count($time_config) < 6){
			$time_config[] = "";
		}

		$useDeliveryTime = DeliveryNormalUtil::getUseDeliveryTimeConfig();
		$this->addCheckBox("use_delivery_time", array(
			"name" => "use_delivery_time",
			"value" => 1,
			"selected" => (isset($useDeliveryTime["use"]) && $useDeliveryTime["use"] == 1),
			"elementId" => "use_delivery_time"
		));

		$this->createAdd("delivery_time_config", "DeliveryTimeConfigListComponent", array(
			"list" => $time_config,
		));
	}

	private function _buildDateForm(){
		$config = DeliveryNormalUtil::getDeliveryDateConfig();

		$this->addCheckBox("use_delivery_date", array(
			"name" => "Date[use_delivery_date]",
			"value" => 1,
			"selected" => (isset($config["use_delivery_date"]) && $config["use_delivery_date"] == 1),
			"label" => "お届け日の指定を表示する"
		));

		$this->addCheckBox("use_format_calendar", array(
			"name" => "Date[use_format_calendar]",
			"value" => 1,
			"selected" => (isset($config["use_format_calendar"]) && $config["use_format_calendar"] == 1),
			"label" => "カレンダー形式の表示に切り替える(β版)"
		));

		$this->addCheckBox("use_delivery_date_unspecified", array(
			"name" => "Date[use_delivery_date_unspecified]",
			"value" => 1,
			"selected" => (isset($config["use_delivery_date_unspecified"]) && $config["use_delivery_date_unspecified"] == 1),
			"label" => "お届け日のセレクトボックスに指定なしを追加する"
		));

		$this->addInput("delivery_shortest_date", array(
			"name" => "Date[delivery_shortest_date]",
			"value" => (isset($config["delivery_shortest_date"])) ? (int)$config["delivery_shortest_date"] : "",
			"style" => "width:60px;text-align:right;"
		));

		$this->addCheckBox("use_re_calc_shortest_date", array(
			"name" => "Date[use_re_calc_shortest_date]",
			"value" => 1,
			"selected" => (isset($config["use_re_calc_shortest_date"]) && $config["use_re_calc_shortest_date"] == 1),
			"label" => "注文日が定休日の場合、最短のお届け日を翌営業日から表示する"
		));

		$installedCalender = SOYShopPluginUtil::checkIsActive("parts_calendar");
		DisplayPlugin::toggle("notice_re_calc_shortest_date", !$installedCalender);

		DisplayPlugin::toggle("installed_calendar_plugin", $installedCalender);

		$this->addLink("calendar_config_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=parts_calendar")
		));


		$this->addInput("delivery_date_period", array(
			"name" => "Date[delivery_date_period]",
			"value" => (isset($config["delivery_date_period"])) ? (int)$config["delivery_date_period"] : "",
			"style" => "width:60px;text-align:right;"
		));

		$this->addInput("delivery_date_format", array(
			"name" => "Date[delivery_date_format]",
			"value" => (isset($config["delivery_date_format"])) ? $config["delivery_date_format"] : "",
		));

		$this->addInput("delivery_date_mail_insert_date", array(
			"name" => "Date[delivery_date_mail_insert_date]",
			"value" => (isset($config["delivery_date_mail_insert_date"])) ? (int)$config["delivery_date_mail_insert_date"] : 0,
			"style" => "width:60px;text-align:right;"
		));
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
