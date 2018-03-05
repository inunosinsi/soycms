<?php

class SlipNumberListPage extends WebPage {

	private $configObj;

	function __construct(){

		//リセット
		if(isset($_POST["reset"])){
			self::setParameter("search_condition", null);
			SOY2PageController::jump("Extension.slip_number");
		}

		parent::__construct();

		if(isset($_GET["delivery"])) self::changeStatus();

		foreach(array("successed", "failed") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		self::buildSearchForm();

		$searchLogic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SearchSlipNumberLogic");
		$searchLogic->setLimit(100);
		$searchLogic->setCondition(self::getParameter("search_condition"));
		$slips = $searchLogic->get();
		$total = $searchLogic->getTotal();

		DisplayPlugin::toggle("no_slip_number", $total === 0);
		DisplayPlugin::toggle("is_slip_number", $total > 0);

		SOY2::import("module.plugins.slip_number.component.SlipNumberListComponent");
		$this->createAdd("slip_number_list", "SlipNumberListComponent", array(
			"list" => $slips
		));
	}

	private function buildSearchForm(){

		//POSTのリセット
		if(isset($_POST["search_condition"])){
			foreach($_POST["search_condition"] as $key => $value){
				if(is_array($value)){
					//
				}else{
					if(!strlen($value)){
						unset($_POST["search_condition"][$key]);
					}
				}
			}
		}

		if(isset($_POST["search"]) && !isset($_POST["search_condition"])){
			self::setParameter("search_condition", null);
			$cnd = array();
		}else{
			$cnd = self::getParameter("search_condition");
		}
		//リセットここまで

		$this->addModel("search_area", array(
			"style" => (isset($cnd) && count($cnd)) ? "display:inline;" : "display:none;"
		));

		$this->addForm("search_form");

		SOY2::import("module.plugins.slip_number.domain.SOYShop_SlipNumber");
		$this->addCheckBox("no_delivery", array(
			"name" => "search_condition[is_delivery][]",
			"value" => SOYShop_SlipNumber::NO_DELIVERY,
			"selected" => (isset($cnd["is_delivery"]) && is_numeric(array_search(SOYShop_SlipNumber::NO_DELIVERY, $cnd["is_delivery"]))),
			"label" => "未発送"
		));

		$this->addCheckBox("is_delivery", array(
			"name" => "search_condition[is_delivery][]",
			"value" => SOYShop_SlipNumber::IS_DELIVERY,
			"selected" => (isset($cnd["is_delivery"]) && is_numeric(array_search(SOYShop_SlipNumber::IS_DELIVERY, $cnd["is_delivery"]))),
			"label" => "発送済み(注文詳細で発送済みのものは除く)"
		));
	}

	private function changeStatus(){
		if(soy2_check_token()){
			$mode = (!isset($_GET["back"])) ? "delivery" : "back";
			if(SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic")->changeStatus((int)$_GET["delivery"], $mode)){
				SOY2PageController::jump("Extension.slip_number?successed");
			}else{
				SOY2PageController::jump("Extension.slip_number?failed");
			}
		}
	}

	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Plugin.Slip:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Plugin.Slip:" . $key, $value);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
