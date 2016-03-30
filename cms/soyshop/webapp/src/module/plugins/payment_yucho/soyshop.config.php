<?php
include_once(dirname(__FILE__) . "/common.php");

class PaymentYuchoConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		$form = SOY2HTMLFactory::createInstance("PaymentYuchoConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "ゆうちょ銀行の振替・払込みの設定";
	}



}
SOYShopPlugin::extension("soyshop.config","payment_yucho","PaymentYuchoConfig");


class PaymentYuchoConfigFormPage extends WebPage{
	
	private $config;
	
	function PaymentYuchoConfigFormPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");
	}
	
	function doPost(){
		
		if(isset($_POST["payment_yucho"])){
			$array = $_POST["payment_yucho"];
			SOYShop_DataSets::put("payment_yucho.text",$array);
			$this->config->redirect("updated");
		}
		
	}
	
	function execute(){
		WebPage::WebPage();

		$configText = PaymentYuchoCommon::getConfigText();

		$this->createAdd("account","HTMLTextArea", array(
			"value" => $configText["account"],
			"name"  => "payment_yucho[account]"
		));
		$this->createAdd("text","HTMLTextArea", array(
			"value" => $configText["text"],
			"name"  => "payment_yucho[text]"
		));
		$this->createAdd("mail","HTMLTextArea", array(
			"value" => $configText["mail"],
			"name"  => "payment_yucho[mail]"
		));
		
		
		$this->createAdd("updated", "HTMLModel", array(
			"visible" => isset($_GET["updated"])
		));
		
	}
	
	function getTemplateFilePath(){
		return dirname(__FILE__) . "/soyshop.config.html";
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
}