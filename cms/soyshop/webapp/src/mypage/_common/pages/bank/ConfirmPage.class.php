<?php
SOY2HTMLFactory::importWebPage("bank.IndexPage");
class ConfirmPage extends IndexPage{

	function doPost(){
		if(soy2_check_token() && soy2_check_referer()){
			if(isset($_POST["next"])){
				SOY2::import("module.plugins.transfer_information.util.TransferInfoUtil");
				$values = $this->getMypage()->getAttribute(TransferInfoUtil::BANK_INFO);

				$isEmpty = true;
				foreach($values as $v){
					if(strlen($v)) {
						$isEmpty = false;
						break;
					}
				}

				$attr = soyshop_get_user_attribute_object($this->getUser()->getId(), TransferInfoUtil::BANK_INFO);
				$v = ($isEmpty) ? "" : soy2_serialize($values);
				$attr->setValue($v);
				soyshop_save_user_attribute_object($attr);

				$this->jump("bank/complete");
			}else if(isset($_POST["back"])){
				$this->jump("bank");
			}
		}
	}

	function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("transfer_information")){
			$this->jump("top");
		}

		SOY2::import("module.plugins.transfer_information.util.TransferInfoUtil");

		parent::__construct();

		parent::buildForm();
	}
}
