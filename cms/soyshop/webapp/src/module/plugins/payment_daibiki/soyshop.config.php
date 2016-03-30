<?php
class PaymentDaibikiConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2DAOFactory::importEntity("SOYShop_DataSets");

		include_once(dirname(__FILE__) . "/config/PaymentDaibikiConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("PaymentDaibikiConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "代引き手数料の設定";
	}

	/**
	 * 料金の取得
	 * @param Boolean appendBlank trueだと管理画面用に１つ空の値を追加する
	 */
	function getPrices($appendBlank = false){
		$price = SOYShop_DataSets::get("payment_daibiki.price", array());

		//初期設定
		if(!is_array($price) || empty($price)){
			$price = array(
				0 => 300
			);
		}

		if($appendBlank){
			$price[""] = "";
		}

		return $price;
	}

	/**
	 * カートで表示する説明文
	 */
	function getDescription(){
		return SOYShop_DataSets::get("payment_daibiki.description","代引きでのお支払いです。手数料は#PRICE#円です。");
	}

	/**
	 * メールで表示する説明文
	 */
	function getMailText(){
		return SOYShop_DataSets::get("payment_daibiki.mail","支払方法：代金引換");
	}

	/**
	 * 代引き不可商品の商品コードの配列を返す
	 * @param Boolean appendBlank trueだと管理画面用に１つ空の値を追加する
	 * @return Array
	 */
	function getItems($appendBlank = false){
		$items = SOYShop_DataSets::get("payment_daibiki.forbidden", array());
		if($appendBlank){
			$items[] = "";
		}
		return $items;
	}

}
SOYShopPlugin::extension("soyshop.config","payment_daibiki","PaymentDaibikiConfig");
?>