<?php

class ReturnsSlipNumberListPage extends WebPage {

	private $configObj;

	function __construct(){

		//リセット
		if(isset($_POST["reset"])){
			self::setParameter("search_condition", null);
			SOY2PageController::jump("Extension.returns_slip_number");
		}

		parent::__construct();

		if(isset($_GET["return"])) self::changeStatus();

		foreach(array("successed", "failed") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		self::buildSearchForm();

		$searchLogic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.SearchReturnsSlipNumberLogic");
 		$searchLogic->setLimit(100);
 		$searchLogic->setCondition(self::getParameter("search_condition"));
 		$slips = $searchLogic->get();
 		$total = $searchLogic->getTotal();

		DisplayPlugin::toggle("no_slip_number", $total === 0);
		DisplayPlugin::toggle("is_slip_number", $total > 0);

		SOY2::import("module.plugins.returns_slip_number.component.ReturnsSlipNumberListComponent");
		$this->createAdd("slip_number_list", "ReturnsSlipNumberListComponent", array(
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

		SOY2::import("module.plugins.returns_slip_number.domain.SOYShop_ReturnsSlipNumber");
		$this->addCheckBox("no_return", array(
			"name" => "search_condition[is_return][]",
			"value" => SOYShop_ReturnsSlipNumber::NO_RETURN,
			"selected" => (isset($cnd["is_return"]) && is_numeric(array_search(SOYShop_ReturnsSlipNumber::NO_RETURN, $cnd["is_return"]))),
			"label" => "未返送"
		));

		$this->addCheckBox("is_return", array(
			"name" => "search_condition[is_return][]",
			"value" => SOYShop_ReturnsSlipNumber::IS_RETURN,
			"selected" => (isset($cnd["is_return"]) && is_numeric(array_search(SOYShop_ReturnsSlipNumber::IS_RETURN, $cnd["is_return"]))),
			"label" => "返送済み(注文詳細で返却済みのものは除く)"
		));
	}

	private function changeStatus(){
		if(soy2_check_token()){
			$mode = (!isset($_GET["back"])) ? "return" : "back";
			if(SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic")->changeStatus((int)$_GET["return"], $mode)){
				SOY2PageController::jump("Extension.returns_slip_number?successed");
			}else{
				SOY2PageController::jump("Extension.returns_slip_number?failed");
			}
		}
	}

	private function getParameter($key){
		if(array_key_exists($key, $_POST)){
			$value = $_POST[$key];
			self::setParameter($key,$value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Plugin.Return.Slip:" . $key);
		}
		return $value;
	}
	private function setParameter($key,$value){
		SOY2ActionSession::getUserSession()->setAttribute("Plugin.Return.Slip:" . $key, $value);
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
