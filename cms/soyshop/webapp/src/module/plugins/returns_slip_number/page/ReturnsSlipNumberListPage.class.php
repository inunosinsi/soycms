<?php

class ReturnsSlipNumberListPage extends WebPage {

	private $configObj;

	function __construct(){
		$logic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.SearchReturnsSlipNumberLogic");

		parent::__construct();

		if(isset($_GET["return"])) self::changeStatus();

		foreach(array("successed", "failed") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		/**
		 * @ToDo 配送済みの検索条件を追加
		 */
		$logic->setLimit(50);
		$slips = $logic->get();
		$total = count($slips);

		DisplayPlugin::toggle("no_slip_number", $total === 0);
		DisplayPlugin::toggle("is_slip_number", $total > 0);

		SOY2::import("module.plugins.returns_slip_number.component.ReturnsSlipNumberListComponent");
		$this->createAdd("slip_number_list", "ReturnsSlipNumberListComponent", array(
			"list" => $slips
		));
	}

	private function changeStatus(){
		if(soy2_check_token()){
			if(SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic")->changeStatus((int)$_GET["return"], "return")){
				SOY2PageController::jump("Extension.returns_slip_number?successed");
			}else{
				SOY2PageController::jump("Extension.returns_slip_number?failed");
			}
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
