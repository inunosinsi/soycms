<?php

class SlipNumberListPage extends WebPage {

	private $configObj;

	function __construct(){
		$logic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SearchSlipNumberLogic");

		parent::__construct();

		if(isset($_GET["delivery"])) self::changeStatus();

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

		SOY2::import("module.plugins.slip_number.component.SlipNumberListComponent");
		$this->createAdd("slip_number_list", "SlipNumberListComponent", array(
			"list" => $slips
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}

	private function changeStatus(){
		if(soy2_check_token()){
			if(SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic")->changeStatus((int)$_GET["delivery"], "delivery")){
				SOY2PageController::jump("Extension.slip_number?successed");
			}else{
				SOY2PageController::jump("Extension.slip_number?failed");
			}
		}
	}
}
