<?php

class AutoCompletionConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["update"])){
				AutoCompletionUtil::saveConfig($_POST["Config"]);
				$this->configObj->redirect("updated");
			}
			if(isset($_POST["reading"])){
				SOY2Logic::createInstance("module.plugins.auto_completion_item_name.logic.MecabReadingLogic")->setReadingEachItems();
				$this->configObj->redirect("finished");
			}
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("finished", isset($_GET["finished"]));

		$cnf = AutoCompletionUtil::getConfig();

		$this->addForm("form");

		$this->addInput("candidate_output_count", array(
			"name" => "Config[count]",
			"value" => (isset($cnf["count"])) ? (int)$cnf["count"] : 10,
			"style" => "width:80px;"
		));

		self::_mecab();
	}

	// サーバにMecabがあれば、Mecabで自動的に読み方を取得する
	private function _mecab(){
		exec("mecab -v", $res);
		$isMecab = (isset($res[0]) && is_numeric(strpos($res[0], "mecab of")));

		DisplayPlugin::toggle("mecab", $isMecab);
		DisplayPlugin::toggle("no_mecab", !$isMecab);

		$ids = array();
		$logs = array();
		if($isMecab){
			$logic = SOY2Logic::createInstance("module.plugins.auto_completion_item_name.logic.MecabReadingLogic");
			$ids = $logic->getUnacquiredReadingItemIds();
			$logs = $logic->getLogs();
		}
		$cnt = count($ids);

		//未取得分の商品件数
		$this->addLabel("unacquired_count", array(
			"text" => $cnt
		));

		DisplayPlugin::toggle("unacquired", $cnt > 0);

		$this->addForm("unacquired_form");

		//ログ
		DisplayPlugin::toggle("log", count($logs));
		SOY2::import("module.plugins.auto_completion_item_name.component.MecabLogListComponent");
		$this->createAdd("log_list", "MecabLogListComponent", array(
			"list" => $logs
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
