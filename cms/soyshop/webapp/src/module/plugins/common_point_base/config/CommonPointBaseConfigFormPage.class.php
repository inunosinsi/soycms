<?php
class CommonPointBaseConfigFormPage extends WebPage{

	private $configObj;

    function CommonPointBaseConfigFormPage() {
    	SOY2DAOFactory::importEntity("SOYShop_DataSets");
    	SOY2::imports("module.plugins.common_point_base.util.*");
    }
    
    function doPost(){
    	
    	if(soy2_check_token() && isset($_POST["Config"])){
    		$config = $_POST["Config"];
    		$config["percentage"] = soyshop_convert_number($config["percentage"], 10);
    		$config["limit"] = soyshop_convert_number($config["limit"], null);
			$config["mail"] = soyshop_convert_number($config["mail"], null);
			
			$config["customer"] = (isset($config["customer"])) ? 1 : 0;
			$config["recalculation"] = (isset($config["recalculation"])) ? 1 : 0;
			
			SOYShop_DataSets::put("point_config", $config);
			
			if($_POST["Mail"]){
				PointBaseUtil::saveMailTitle($_POST["Mail"]["title"]);
				PointBaseUtil::saveMailContent($_POST["Mail"]["content"]);
			}
			
			$this->configObj->redirect("updated");
    	}
    }
    
    function execute(){
    	WebPage::WebPage();
    	
    	$config = PointBaseUtil::getConfig();
    	
    	DisplayPlugin::toggle("error", isset($_GET["error"]));
    	
		$this->addForm("form");
		
		$this->addInput("point_percentage", array(
			"name" => "Config[percentage]",
			"value" => (isset($config["percentage"])) ? $config["percentage"] : "",
			"style" => "text-align:right;ime-mode:inactive;"
		));
		
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
			"text" => $this->buildPath(). " " . SOYSHOP_ID
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
    
    function buildPath(){
		return dirname(dirname(__FILE__)) . "/job/exe.php";
	}

    function setConfigObj($obj) {
		$this->configObj = $obj;
	}
}
?>