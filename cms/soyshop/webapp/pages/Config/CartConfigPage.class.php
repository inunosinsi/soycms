<?php
/**
 * @class Config.CartConfigPage
 * @date 2009-07-27T16:07:18+09:00
 * @author SOY2HTMLFactory
 */
class CartConfigPage extends WebPage{

	private $charsets = array("UTF-8", "Shift_JIS", "EUC-JP");

	function doPost(){
		if(soy2_check_token()){

			$cart_id = $this->checkCartId($_POST["cart_id"]);
			$cart_url = $this->checkCartUrl($_POST["cart_url"]);
			$cart_charset = $_POST["cart_charset"];

			SOYShop_DataSets::put("config.cart.cart_title", $_POST["cart_title"]);

			SOYShop_DataSets::put("config.cart.use_ssl", (int)$_POST["cart_ssl"]);
			SOYShop_DataSets::put("config.cart.ssl_url", $this->checkSSLCartUrl($_POST["cart_ssl_url"]));

			SOYShop_DataSets::put("config.cart.cart_id", $cart_id);
			SOYShop_DataSets::put("config.cart.cart_url", $cart_url);
			SOYShop_DataSets::put("config.cart.cart_charset", $cart_charset);

			//携帯自動振り分けプラグインが有効な時だけ設定を保存する
			if( class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("util_mobile_check")) ){
				$mobile_cart_id = $this->checkCartId($_POST["mobile_cart_id"]);
				$mobile_cart_url = $this->checkCartUrl($_POST["mobile_cart_url"]);
				$mobile_cart_charset = $_POST["mobile_cart_charset"];

				$smartphone_cart_id = $this->checkCartId($_POST["smartphone_cart_id"]);
				$smartphone_cart_url = $this->checkCartUrl($_POST["smartphone_cart_url"]);
				$smartphone_cart_charset = $_POST["smartphone_cart_charset"];

				SOYShop_DataSets::put("config.cart.mobile_cart_id", $mobile_cart_id);
				SOYShop_DataSets::put("config.cart.mobile_cart_url", $mobile_cart_url);
				SOYShop_DataSets::put("config.cart.mobile_cart_charset", $mobile_cart_charset);

				SOYShop_DataSets::put("config.cart.smartphone_cart_id", $smartphone_cart_id);
				SOYShop_DataSets::put("config.cart.smartphone_cart_url", $smartphone_cart_url);
				SOYShop_DataSets::put("config.cart.smartphone_cart_charset", $smartphone_cart_charset);
			}

			//多言語化用の拡張ポイント
			SOYShopPlugin::load("soyshop.application.name");
			SOYShopPlugin::invoke("soyshop.application.name", array(
				"mode" => "cart"
			));
		}

		SOY2PageController::jump("Config.CartConfig?updated");
	}

	function __construct(){
		parent::__construct();

		$this->addForm("update_form");

		$this->addInput("cart_title", array(
			"name" => "cart_title",
			"value" => $this->getCartTitle()
		));

		//多言語化用の拡張ポイント
		SOYShopPlugin::load("soyshop.application.name");
		$nameForm = SOYShopPlugin::display("soyshop.application.name", array(
			"mode" => "cart"
		));

		$this->addLabel("extension_cart_name_input", array(
			"html" => $nameForm
		));

		//携帯自動振り分けプラグインが有効かどうか
		$isEnabledUtilMobileCheck = class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("util_mobile_check"));
		DisplayPlugin::toggle("is_enabled_util_mobile_check", $isEnabledUtilMobileCheck);
		DisplayPlugin::toggle("is_enabled_util_mobile_check_1", $isEnabledUtilMobileCheck);
		DisplayPlugin::toggle("is_not_enabled_util_mobile_check", !$isEnabledUtilMobileCheck);

		$this->addInput("cart_url", array(
			"name" => "cart_url",
			"value" => $this->getCartUrl()
		));

		$this->addSelect("cart_application", array(
			"name" => "cart_id",
			"selected" => $this->getCartApplicationId(),
			"options" => $this->getCartApplications()
		));

		$this->addSelect("cart_charset", array(
			"name" => "cart_charset",
			"selected" => $this->getCartCharset(),
			"options" => $this->charsets
		));

		$this->addInput("mobile_cart_url", array(
			"name" => "mobile_cart_url",
			"value" => $this->getMobileCartUrl()
		));

		$this->addSelect("mobile_cart_application", array(
			"name" => "mobile_cart_id",
			"selected" => $this->getMobileCartApplicationId(),
			"options" => $this->getCartApplications()
		));

		$this->addSelect("mobile_cart_charset", array(
			"name" => "mobile_cart_charset",
			"selected" => $this->getMobileCartCharset(),
			"options" => $this->charsets
		));

		$this->addInput("smartphone_cart_url", array(
			"name" => "smartphone_cart_url",
			"value" => $this->getSmartphoneCartUrl()
		));

		$this->addSelect("smartphone_cart_application", array(
			"name" => "smartphone_cart_id",
			"selected" => $this->getSmartphoneCartApplicationId(),
			"options" => $this->getCartApplications()
		));

		$this->addSelect("smartphone_cart_charset", array(
			"name" => "smartphone_cart_charset",
			"selected" => $this->getSmartphoneCartCharset(),
			"options" => $this->charsets
		));

		$use_ssl = SOYShop_DataSets::get("config.cart.use_ssl", 0);
		$this->addCheckBox("cart_is_ssl", array(
			"name" => "cart_ssl",
			"value" => 1,
			"selected" => $use_ssl,
			"label" => "SSLを使用する"
		));

		$this->addInput("cart_ssl_url", array(
			"name" => "cart_ssl_url",
			"value" => $this->getSSLCartUrl()
		));

		$this->addModel("cart_ssl_url_input", array(
			"style" => ($use_ssl) ? "" : "display:none;"
		));

	}

	function getCartUrl(){
		return SOYShop_DataSets::get("config.cart.cart_url", "cart");
	}

	function getMobileCartUrl(){
		return SOYShop_DataSets::get("config.cart.mobile_cart_url", "mb/cart");
	}

	function getSmartphoneCartUrl(){
		return SOYShop_DataSets::get("config.cart.smartphone_cart_url", "i/cart");
	}

	function getSSLCartUrl(){
		$sslUrl = SOYShop_DataSets::get("config.cart.ssl_url", null);
		if(is_null($sslUrl)){
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$sslUrl = SOYShop_ShopConfig::load()->getSiteUrl();
		}
		if(is_numeric(strpos($sslUrl, "httpss"))) $sslUrl = str_replace("httpss", "https", $sslUrl);
		return $sslUrl;
	}

	function getCartApplicationId(){
		return SOYShop_DataSets::get("config.cart.cart_id", "bryon");
	}

	function getCartCharset(){
		return SOYShop_DataSets::get("config.cart.cart_charset", "UTF-8");
	}

	function getMobileCartApplicationId(){
		return SOYShop_DataSets::get("config.cart.mobile_cart_id", "mobile");
	}

	function getMobileCartCharset(){
		return SOYShop_DataSets::get("config.cart.mobile_cart_charset", "Shift_JIS");
	}

	function getSmartphoneCartApplicationId(){
		return SOYShop_DataSets::get("config.cart.smartphone_cart_id", "smart");
	}

	function getSmartphoneCartCharset(){
		return SOYShop_DataSets::get("config.cart.smartphone_cart_charset", "UTF-8");
	}

	function getCartTitle(){
		return SOYShop_DataSets::get("config.cart.cart_title", "ショッピングカート");
	}

	function getCartApplications(){
		$dir = SOY2::RootDir() . "cart/";

		$files = scandir($dir);

		foreach($files as $file){
			if($file[0] == ".") continue;
			if($file[0] == "_") continue;

			$res[] = $file;
		}

		return $res;
	}

	private function checkCartId($value){
		//対応するテンプレートが存在しない場合はここで作成する
		self::makeTemplate($value);

		$values = $this->getCartApplications();
		return (in_array($value, $values)) ? $value : $this->getCartApplicationId();
	}

	private function makeTemplate($value){
		$dir = SOYSHOP_SITE_DIRECTORY . ".template/cart/";
		$iniFile = $dir . $value . ".ini";
		if(!file_exists($iniFile)){
			file_put_contents($iniFile, "name= \"" . $value . "\"");
			if($value == "none"){
				file_put_contents($dir . $value . ".html", "<!-- shop:module=\"common.cart_application\" /-->");
			}else{
				file_put_contents($dir . $value . ".html", "テンプレートの記述がありません。");
			}
		}
	}

	private function checkCartUrl($url){
		if(preg_match('/\/$/', $url)) $url = substr($url, 0, strlen($url) - 1);
		if(strlen($url) < 1) return $this->getCartUrl();
		return $url;
	}
	private function checkSSLCartUrl($url){
		if(strlen($url) < 1) return $this->getSSLCartUrl();
		return $url;
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カートの設定", array("Config" => "設定"));
	}
}
