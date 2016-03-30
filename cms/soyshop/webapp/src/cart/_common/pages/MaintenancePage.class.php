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

	function MaintenancePage(){

		//常にクリア
		$cart = CartLogic::getCart();
		$cart->clear();

		WebPage::WebPage();

		$config = SOYShop_ShopConfig::load();
		$info = $config->getCompanyInformation();

		$this->addLabel("company_telephone", array(
			"text" => (isset($info["telephone"])) ? $info["telephone"] : ""
		));

	}
}
?>