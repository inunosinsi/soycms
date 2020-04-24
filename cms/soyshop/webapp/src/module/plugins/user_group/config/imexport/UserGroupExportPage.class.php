<?php

class UserGroupExportPage extends WebPage{

	private $configObj;

	function __construct(){}

	function doPost(){
		if(soy2_check_token()){
			$logic = SOY2Logic::createInstance("module.plugins.user_group.logic.UserGroupCSVLogic");
			$lines = $logic->getLines();
			if(!count($lines)) {
				echo "エクスポートを失敗しました。";
				exit;
			}

			$charset = (isset($_POST["charset"])) ? $_POST["charset"] : "Shift-JIS";

			set_time_limit(0);

			header("Cache-Control: public");
			header("Pragma: public");
			header("Content-Disposition: attachment; filename=soyshop_user_group_" . date("YmdHis", time()) . ".csv");
			header("Content-Type: text/csv; charset=" . htmlspecialchars($charset).";");

			ob_start();
			echo implode(",", $logic->getLabels());
			echo "\n";
			echo implode("\n", $lines);
			$csv = ob_get_contents();
			ob_end_clean();

			echo mb_convert_encoding($csv, $charset, "UTF-8");
			exit;
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("csv_form");
	}

	function setConfigObj($configObj){
        $this->configObj = $configObj;
	}
}
