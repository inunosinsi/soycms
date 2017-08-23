<?php
include(dirname(__FILE__) . "/common.php");
class SOYShopPaymentCustomConfigFormPage extends WebPage{

	private $config;

    function __construct() {}

    function doPost(){
		if(soy2_check_token() && isset($_POST["payment_custom"])){
			try{
				$values = array();
				
				$posts = $_POST["payment_custom"];
				foreach($posts as $key => $value){
					$values[$key] = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
				}
				$values["price"] = (isset($values["price"])) ? mb_convert_kana($values["price"], "a") : 0;
				$values["price"] = (is_numeric($values["price"])) ? $values["price"] : 0;
				
				SOYShop_DataSets::put("payment_custom",$values);
				$this->config->redirect("updated");
			}catch(Exception $e){
				$this->config->redirect("error");
			}

		}
    }

    function execute(){

		parent::__construct();

    	$this->buildForm();

		$this->createAdd("updated", "HTMLModel", array(
			"visible" => isset($_GET["updated"])
		));
		$this->createAdd("error", "HTMLModel", array(
			"visible" => isset($_GET["error"])
		));
    }

    function buildForm(){

		$custom = PaymentCustomCommon::getCustomConfig();

		$this->createAdd("config_form","HTMLForm");
		
		$this->createAdd("name","HTMLInput", array(
			"name" => "payment_custom[name]",
			"value" => @$custom["name"],
			"style" => "width:50%;"
		));

    	$this->createAdd("description","HTMLTextArea", array(
    		"name" => "payment_custom[description]",
    		"value" => @$custom["description"]
    	));

		$this->createAdd("price","HTMLInput", array(
			"name" => "payment_custom[price]",
			"value" => @$custom["price"],
			"style" => "text-align:right;ime-mode:inactive"
		));

    	$this->createAdd("mail","HTMLTextArea", array(
    		"name" => "payment_custom[mail]",
    		"value" => @$custom["mail"]
    	));
    	
    	SOY2DAOFactory::create("order.SOYShop_OrderDAO");
    	   	    	
    	$this->createAdd("payment_status_list","PaymentStatusList", array(
    		"list" => SOYShop_Order::getPaymentStatusList(),
    		"status" => @$custom["status"]
    	));
    }
    
    function setConfigObj($obj){
    	$this->config = $obj;
    }
}

class PaymentStatusList extends HTMLList{
	
	private $status;
	
	protected function populateItem($entity,$key){
		
		$this->createAdd("status_radio","HTMLCheckBox", array(
			"name" => "payment_custom[status]",
			"value" => $key,
			"label" => $entity,
			"selected" => ($this->status == $key) ? true : false
		));
	}
	function setStatus($status){
		$this->status = $status;
	}
}

?>