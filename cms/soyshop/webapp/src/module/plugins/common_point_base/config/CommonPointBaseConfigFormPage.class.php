<?php
class CommonPointBaseConfigFormPage extends WebPage{

	private $configObj;

    function __construct() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    	SOY2::imports("module.plugins.common_point_base.util.*");
    }

    function doPost(){

    	if(soy2_check_token() && isset($_POST["Config"])){
    		PointBaseUtil::saveConfig($_POST["Config"]);

			if($_POST["Mail"]){
				PointBaseUtil::saveMailTitle($_POST["Mail"]["title"]);
				PointBaseUtil::saveMailContent($_POST["Mail"]["content"]);
			}

			//個々の商品のポイント付与率を一括変更
			if(isset($_POST["all_change"])){
				SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic")->setPointCollective((int)$config["percentage"]);
			}

			$this->configObj->redirect("updated");
    	}
    }

    function execute(){
    	parent::__construct();

    	$config = PointBaseUtil::getConfig();

    	DisplayPlugin::toggle("error", isset($_GET["error"]));

		$this->addForm("form");

		//common_point_grantに移行
		// $this->addInput("point_percentage", array(
		// 	"name" => "Config[percentage]",
		// 	"value" => (isset($config["percentage"])) ? $config["percentage"] : "",
		// 	"style" => "text-align:right;ime-mode:inactive;"
		// ));

		$this->addCheckBox("customer", array(
			"name" => "Config[customer]",
			"value" => 1,
			"selected" => (isset($config["customer"]) && $config["customer"] == 1),
			"label" => "会員のみポイントを加算する"
		));

		$this->addCheckBox("recalculation", array(
			"name" => "Config[recalculation]",
			"value" => 1,
			"selected" => (isset($config["recalculation"]) && $config["recalculation"] == 1),
			"label" => "ポイント支払後に価格からポイントを引いた分で付与するポイントの再計算を行う"
		));

		$this->addInput("point_limit", array(
			"name" => "Config[limit]",
			"value" => (isset($config["limit"])) ? (int)$config["limit"] : "",
			"style" => "text-align:right;ime-mode:inactive;"
		));

		$this->addInput("notice_mail", array(
			"name" => "Config[mail]",
			"value" => (isset($config["mail"])) ? (int)$config["mail"] : "",
			"style" => "text-align:right;ime-mode:inactive;"
		));

		$this->addLabel("job_path", array(
			"text" => self::buildPath(). " " . SOYSHOP_ID
		));

		$this->addLabel("site_id", array(
			"text" => SOYSHOP_ID
		));

		$this->addInput("mail_title", array(
			"name" => "Mail[title]",
			"value" => PointBaseUtil::getMailTitle()
		));

		$this->addTextArea("mail_content", array(
			"name" => "Mail[content]",
			"value" => PointBaseUtil::getMailContent()
		));
    }

    private function buildPath(){
		return dirname(dirname(__FILE__)) . "/job/exe.php";
	}

    function setConfigObj($obj) {
		$this->configObj = $obj;
	}
}
