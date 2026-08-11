<?php

class SkipCartPageConfigFormPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.skip_cart_page.util.SkipCartPageUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			SkipCartPageUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		foreach(range(1, 3) as $n){
			switch($n){
				case 1:
					$dsp = "カートに入っている商品";
					break;
				case 2:
					$dsp = "顧客情報の登録";
					break;
				case 3:
					$dsp = "支払い・配送方法の選択";
					break;
				default:
					$dsp = "";
			}
		
			$this->addCheckBox("skip_".$n, array(
				"name" => "Config[skip][]",
				"value" => $n,
				"selected" => SkipCartPageUtil::isSkip($n),
				"label" => "Cart0".$n."Page(".$dsp.")をスキップ"
			));
		}

		$this->addSelect("payment_module", array(
			"name" => "Config[payment]",
			"options" => SkipCartPageUtil::getInstalledPaymentModuleList(),
			"selected" => SkipCartPageUtil::getPaymentModuleConfig()
		));

		$this->addSelect("delivery_module", array(
			"name" => "Config[delivery]",
			"options" => SkipCartPageUtil::getInstalledDeliveryModuleList(),
			"selected" => SkipCartPageUtil::getDeliveryModuleConfig()
		));
	}

	function setConfigObj(SkipCartPageConfig $configObj){
		$this->configObj = $configObj;
	}
}
