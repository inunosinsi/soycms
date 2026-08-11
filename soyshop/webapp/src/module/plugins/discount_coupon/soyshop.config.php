<?php
class SOYShopDiscountCouponConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		include_once(dirname(__FILE__) . "/common.php");
		include_once(dirname(__FILE__) . "/classes.php");
		include_once(dirname(__FILE__) . "/SOYShopDiscountCouponConfigFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("SOYShopDiscountCouponConfigFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		return "クーポンプラグイン";
	}

}
SOYShopPlugin::extension("soyshop.config","discount_coupon","SOYShopDiscountCouponConfig");

?>
