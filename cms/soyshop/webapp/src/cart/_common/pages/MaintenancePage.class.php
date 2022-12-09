<?php
/**
 * MaintenancePage
 *
 * 旧ファイル MaintenancePace.class.php, MaintenancePace.html が残っている場合は削除する
 *
 */
class MaintenancePage extends MainCartPageBase{

	function doPost(){
	}

	function __construct(){

		//常にクリア
		$cart = CartLogic::getCart();
		$cart->clear();

		parent::__construct();

		$config = SOYShop_ShopConfig::load();
		$info = $config->getCompanyInformation();

		$this->addLabel("company_telephone", array(
			"text" => (isset($info["telephone"])) ? $info["telephone"] : ""
		));

	}
}
