<?php
class ErrorMessageLabel extends HTMLLabel{

    function getVisible(){
    	return (strlen($this->getText()) > 0);
    }
}

class NumberFormatLabel extends HTMLLabel{

	function getObject(){
		return soy2_number_format(parent::getObject());
	}
}

function tstrlen($str){
	return strlen(trim($str));
}

function isValidEmail($email){
	$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
	$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
	$d3     = '\d{1,3}';
	$ip     = $d3 . '\.' . $d3 . '\.' . $d3 . '\.' . $d3;
	$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])$";

	if(! preg_match('/' . $validEmail . '/i', $email) ) {
		return false;
	}

	return true;
}

/**
 * Cart Base Class
 */
class MainCartPageBase extends WebPage{

	function __construct(){
		parent::__construct();

		$this->addModel("has_delivery_or_payment_modules", array(
			"visible" => self::getInstalledModulesCount() > 0
		));
	}

    /**
     * @override HTMLPage::getTemplateFilePath()
     */
    function getTemplateFilePath(){
		if(file_exists(SOYSHOP_MAIN_CART_TEMPLATE_DIR . get_class($this) . ".html")){
			return SOYSHOP_MAIN_CART_TEMPLATE_DIR . get_class($this) . ".html";
		}

		if(DEBUG_MODE){
			echo "<p>Custom Template Not Found: " . SOYSHOP_MAIN_CART_TEMPLATE_DIR . get_class($this) . ".html</p>";
		}

		return SOYSHOP_DEFAULT_CART_TEMPLATE_DIR . get_class($this) . ".html";
    }

    /* convert */

	function _trim($str){
		return trim($str);
	}

	function convertKana($str){
		$str = trim($str);
		return mb_convert_kana($str, "CK", "UTF-8");
	}

	function buildModules(){
		$plugin = new SOYShopPageModulePlugin();

		while(true){
			list($tag, $line, $innerHTML, $outerHTML, $value, $suffix, $skipendtag) =
				$plugin->parse("module", "[a-zA-Z0-9\.\_]+", $this->_soy2_content);

			if(!strlen($tag)) break;

			$plugin->_attribute = array();

			$plugin->setTag($tag);
			$plugin->parseAttributes($line);
			$plugin->setInnerHTML($innerHTML);
			$plugin->setOuterHTML($outerHTML);
			$plugin->setParent($this);
			$plugin->setSkipEndTag($skipendtag);
			$plugin->setSoyValue($value);
			$plugin->execute();

			$this->_soy2_content = $this->getContent($plugin, $this->_soy2_content);
		}
	}

	function getInstalledModulesCount(){
		static $cnt;
		if(is_null($cnt)){
			$cart = CartLogic::getCart();
			$cnt = count(self::getPaymentMethod($cart));
			$cnt += count(self::getDeliveryMethod($cart));
			$cnt += count(self::getCustomfieldMethod($cart));
		}
		return $cnt;
	}

	/** モジュールのインストール状況 **/
	function getPaymentMethod(CartLogic $cart){
		static $list;
		if(is_null($list)){
			//アクティブなプラグインをすべて読み込む
	    	SOYShopPlugin::load("soyshop.payment");
			$list = SOYShopPlugin::invoke("soyshop.payment", array(
				"mode" => "list",
				"cart" => $cart
			))->getList();
		}
		return $list;
	}

	function getDeliveryMethod(CartLogic $cart){
		static $list;
		if(is_null($list)){
	    	//アクティブなプラグインをすべて読み込む
			SOYShopPlugin::load("soyshop.delivery");
			$list = SOYShopPlugin::invoke("soyshop.delivery", array(
				"mode" => "list",
				"cart" => $cart
			))->getList();
		}
		return $list;
	}

	function getDiscountMethod(CartLogic $cart){
		static $list;
		if(is_null($list)){
			//アクティブなプラグインをすべて読み込む
			SOYShopPlugin::load("soyshop.discount");
			$list = SOYShopPlugin::invoke("soyshop.discount", array(
				"mode" => "list",
				"cart" => $cart
			))->getList();
		}
		return $list;
	}

	function getPointMethod(CartLogic $cart, $userId){
		static $list;
		if(is_null($list)){
			//アクティブなプラグインをすべて読み込む
			SOYShopPlugin::load("soyshop.point.payment");
			$list = SOYShopPlugin::invoke("soyshop.point.payment", array(
				"mode" => "list",
				"cart" => $cart,
				"userId" => $userId
			))->getList();
		}
		return $list;
	}

	function getCustomfieldMethod(CartLogic $cart){
		static $list;
		if(is_null($list)){
			$list = array();

			//アクティブなプラグインをすべて読み込む
			SOYShopPlugin::load("soyshop.order.customfield");
			$values = SOYShopPlugin::invoke("soyshop.order.customfield", array(
				"mode" => "list",
				"cart" => $cart
			))->getList();

			if(!count($values)) return $list;

			$list = array();
			foreach($values as $v){
				if(!is_array($v)) continue;
				foreach($v as $key => $obj){
					$list[$key] = $obj;
				}
			}
		}

		return $list;
	}
}
