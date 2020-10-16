<?php

class SOYShop_CartPage extends SOYShopPageBase{

	private $cartId;

	function __construct($args){
		$this->setCartId($args[0]);

		parent::__construct();
	}

	function doOperation(){
		//カートの処理の直前に何らかの処理を行いたい場合
		SOYShopPlugin::load("soyshop.cart");
		SOYShopPlugin::invoke("soyshop.cart", array(
			"mode" => "doOperation"
		));

		$this->checkSSL();

		SOY2::import("base.cart.cart", ".php");
		header("Location: ". $this->getCartURL());
		exit;
	}

	function checkSSL(){
		$isUseSSL = SOYShop_DataSets::get("config.cart.use_ssl", 0);

		if($isUseSSL && !isset($_SERVER["HTTPS"])){
			//携帯の端末がDoCoMoだった場合セッションIDを入れる
			//auはCookieのセッションIDの有無にかかわらず常にセッションIDを付ける
			$param = "";
			if(
				defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE
				&& defined("SOYSHOP_MOBILE_CARRIER")
				&&
				(
				  SOYSHOP_MOBILE_CARRIER == "DoCoMo" && !isset($_COOKIE[session_name()])
				  ||
				  SOYSHOP_MOBILE_CARRIER == "KDDI"
				)
				&& isset($_GET[session_name()])
			){
				$param = "?".session_name() . "=" . session_id();
			}

			soyshop_redirect_cart($param);
			exit;
		}
	}

	function common_execute(){
		$this->checkSSL();
		$this->buildModules();
		$this->setTitle(soyshop_get_cart_page_title());
	}

	function display(){
		ob_start();
    	parent::display();
    	$html = ob_get_contents();
    	ob_end_clean();

    	if(defined("SOYSHOP_IS_MOBILE") && SOYSHOP_IS_MOBILE){
    		$charset = SOYShop_DataSets::get("config.cart.mobile_cart_charset", "Shift_JIS");
    	}elseif(defined("SOYSHOP_IS_SMARTPHONE") && SOYSHOP_IS_SMARTPHONE){
    		$charset = SOYShop_DataSets::get("config.cart.smartphone_cart_charset", "UTF-8");
    	}else{
    		$charset = SOYShop_DataSets::get("config.cart.cart_charset", "UTF-8");
    	}

    	echo mb_convert_encoding($html, $charset, "UTF-8");
	}

	function getTemplateFilePath(){
		$templateDir = SOYSHOP_SITE_DIRECTORY . ".template/";
		return $templateDir . "cart/" . $this->getCartId() . ".html";
    }

	/**
	 * キャッシュファイルのパス
	 *
	 * @return キャッシュファイルのパス
	 */
	function getCacheFilePath($extension = ".html.php"){
		return
			SOY2HTMLConfig::CacheDir(). SOY2HTMLConfig::getOption("cache_prefix") .
			"cache_" . get_class($this) . '_' . $this->cartId . '_' . $this->getId() .'_' . $this->getParentPageParam() . md5($this->getClassPath() . $this->getTemplateFilePath()) . SOY2HTMLConfig::Language() . $extension;
	}

    function getCartId() {
    	return $this->cartId;
    }
    function setCartId($cartId) {
    	$this->cartId = $cartId;
    }

    function getCartURL(){
    	$this->checkSSL();
    	return soyshop_get_cart_url(false, true);
    }
}
