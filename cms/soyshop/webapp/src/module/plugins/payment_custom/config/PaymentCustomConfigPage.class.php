<?php
class PaymentCustomConfigPage extends WebPage{

	private $config;

    function __construct() {
		SOY2::import("domain.order.SOYShop_Order");
		SOY2::import("module.plugins.payment_custom.component.PaymentStatusListComponent");
		SOY2::import("module.plugins.payment_custom.util.PaymentCustomUtil");
	}

    function doPost(){
		if(soy2_check_token() && isset($_POST["payment_custom"])){
			$values = array();

			$posts = $_POST["payment_custom"];
			foreach($posts as $key => $value){
				$values[$key] = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
			}
			$values["price"] = (isset($values["price"])) ? mb_convert_kana($values["price"], "a") : 0;
			$values["price"] = (is_numeric($values["price"])) ? $values["price"] : 0;

			PaymentCustomUtil::saveConfig($values);
			$this->config->redirect("updated");
		}
		$this->config->redirect("error");
    }

    function execute(){
		parent::__construct();

		DisplayPlugin::toggle("error", isset($_GET["error"]));

    	self::_buildForm();
    }

    private function _buildForm(){

		$cnf = PaymentCustomUtil::getConfig();

		$this->addForm("config_form");

		$this->addInput("name", array(
			"name" => "payment_custom[name]",
			"value" => (isset($cnf["name"])) ? $cnf["name"] : "",
			"style" => "width:50%;"
		));

    	$this->addTextArea("description", array(
    		"name" => "payment_custom[description]",
    		"value" => (isset($cnf["description"])) ? $cnf["description"] : ""
    	));

		$this->addInput("price", array(
			"name" => "payment_custom[price]",
			"value" => (isset($cnf["price"])) ? $cnf["price"] : "",
			"style" => "text-align:right;ime-mode:inactive"
		));

    	$this->addTextArea("mail", array(
    		"name" => "payment_custom[mail]",
    		"value" => (isset($cnf["mail"])) ? $cnf["mail"] : ""
    	));

    	$this->createAdd("payment_status_list","PaymentStatusListComponent", array(
    		"list" => SOYShop_Order::getPaymentStatusList(),
    		"status" => (isset($cnf["status"])) ? $cnf["status"] : ""
    	));
    }

    function setConfigObj($obj){
    	$this->config = $obj;
    }
}
