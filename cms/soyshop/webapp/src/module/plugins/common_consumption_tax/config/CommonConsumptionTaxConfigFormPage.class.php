<?php
SOY2::imports("module.plugins.common_consumption_tax.domain.*");
SOY2::imports("module.plugins.common_consumption_tax.config.*");
class CommonConsumptionTaxConfigFormPage extends WebPage{

	private $configObj;
	private $scheduleDao;

	function __construct() {
		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
		$this->scheduleDao = SOY2DAOFactory::create("SOYShop_ConsumptionTaxScheduleDAO");
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Register"])){
				$register = $_POST["Register"];

				$register["taxRate"] = soyshop_convert_number($register["taxRate"], 0);
				$register["startDate"] = self::_convertDate($register["startDate"]);

				if($register["taxRate"] > 0 && strlen($register["startDate"]) > 0){
					$schedule = SOY2::cast("SOYShop_ConsumptionTaxSchedule", $register);
					try{
						$this->scheduleDao->insert($schedule);
						$this->configObj->redirect("updated");
					}catch(Exception $e){
						//
					}
				}
			}

			//サーバで設定されている時間の確認
			if(isset($_POST["confirm"])){
				$this->configObj->redirect("time");
			}

			$config = ConsumptionTaxUtil::getConfig();

			//軽減税率
			if(isset($_POST["Reduced"])){
				$config["reduced_tax_rate"] = (isset($_POST["Reduced"]["reduced_tax_rate"])) ? (int)$_POST["Reduced"]["reduced_tax_rate"] : 0;
				if(isset($_POST["Reduced"]["reduced_tax_rate_start_date"]) && strlen($_POST["Reduced"]["reduced_tax_rate_start_date"])){
					$values = explode("-", $_POST["Reduced"]["reduced_tax_rate_start_date"]);
					$config["reduced_tax_rate_start_date"] = mktime(0, 0, 0, (int)$values[1], (int)$values[2], (int)$values[0]);
				}
				ConsumptionTaxUtil::saveConfig($config);

				$this->configObj->redirect("updated");
			}

			//消費税の金額の小数点の扱いについての設定
			if(isset($_POST["Update"])){
				$config["method"] = $_POST["Method"];
				ConsumptionTaxUtil::saveConfig($config);

				$this->configObj->redirect("updated");
			}
		}

		$this->configObj->redirect("failed");
	}

	function execute(){

		//スケジュールオブジェクトの削除
		if(isset($_GET["id"]) && is_numeric($_GET["id"]) && isset($_GET["soy2_token"])){
			self::_remove();
		}

		parent::__construct();

		foreach(array("failed", "success", "delete_failed") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}


		//サーバに設定されている時刻の確認
		$this->addForm("time_confirm_form");

		$this->addLabel("confirm_time", array(
			"text" => (isset($_GET["time"])) ? date("Y-m-d H:i:s") : "",
			"style" => "font-size:1.4em;"
		));

		$schedules = self::_getSchedules();
		DisplayPlugin::toggle("schedule_list", (count($schedules) > 0));

		$this->addForm("form");

		$this->createAdd("schedule_list", "ScheduleListComponent", array(
			"list" => $schedules
		));

		$this->addForm("register_form");

		$this->addInput("tax_rate", array(
			"name" => "Register[taxRate]",
			"value" => ""
		));

		$this->addInput("start_date", array(
			"name" => "Register[startDate]",
			"value" => "",
			"readonly" => true
		));

		SOY2::import("module.plugins.common_consumption_tax.util.ConsumptionTaxUtil");
		$config = ConsumptionTaxUtil::getConfig();

		/** 軽減税率 **/
		$this->addForm("reduced_tax_form");
		$this->addInput("reduced_tax_rate", array(
			"name" => "Reduced[reduced_tax_rate]",
			"value" => (isset($config["reduced_tax_rate"])) ? (int)$config["reduced_tax_rate"] : 0,
			"style" => "width:80px;padding:0 3px"
		));

		//コメントアウトして使用していない
		$this->addInput("reduced_tax_rate_start_date", array(
			"name" => "Reduced[reduced_tax_rate_start_date]",
			"value" => (isset($config["reduced_tax_rate_start_date"]) && strlen($config["reduced_tax_rate_start_date"])) ? date("Y-m-d",$config["reduced_tax_rate_start_date"]) : "",
			"style" => "width:90px;padding:0 3px"
		));

		/** 小数点の扱いについて **/

		$this->addForm("method_form");

		$this->addCheckBox("method_floor", array(
			"name" => "Method",
			"value" => ConsumptionTaxUtil::METHOD_FLOOR,
			"selected" => ($config["method"] == ConsumptionTaxUtil::METHOD_FLOOR),
			"label" => "切り捨て(推奨)"
		));

		$this->addCheckBox("method_round", array(
			"name" => "Method",
			"value" => ConsumptionTaxUtil::METHOD_ROUND,
			"selected" => ($config["method"] == ConsumptionTaxUtil::METHOD_ROUND),
			"label" => "四捨五入"
		));

		$this->addCheckBox("method_ceil", array(
			"name" => "Method",
			"value" => ConsumptionTaxUtil::METHOD_CEIL,
			"selected" => ($config["method"] == ConsumptionTaxUtil::METHOD_CEIL),
			"label" => "切り上げ"
		));
	}

	//スケジュールオブジェクトの削除
	private function _remove(){
		if(soy2_check_token()){
			try{
				$this->scheduleDao->deleteById($_GET["id"]);
				SOY2PageController::jump("Config.Detail?plugin=common_consumption_tax&success");
			}catch(Exception $e){
				SOY2PageController::jump("Config.Detail?plugin=common_consumption_tax&delete_failed");
			}
		}
	}

	private function _getSchedules(){
		try{
			return $this->scheduleDao->get();
		}catch(Exception $e){
			return array();
		}
	}

	private function _convertDate(string $date){
		$array = explode("-", $date);
		return mktime(0, 0, 0, $array[1], $array[2], $array[0]);
	}

	function setConfigObj($configObj) {
		$this->configObj = $configObj;
	}
}
