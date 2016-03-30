<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class SOYMailConnectorOrderCustomfield extends SOYShopOrderCustomfield{
	
	const PLUGIN_ID = "soymail_connector";
	
	//読み込み準備
	function prepare(){
		if(!class_exists("SOYMailConnectoryUtil")){
			SOY2::import("module.plugins." . self::PLUGIN_ID . ".util.SOYMailConnectorUtil");
		}
	}
	
	function clear(CartLogic $cart){
		$this->prepare();
		$cart->clearAttribute(self::PLUGIN_ID . ".check");
		$cart->clearOrderAttribute(self::PLUGIN_ID . ".value");
	}
	
	function doPost($param){
		
		$this->prepare();
		$cart = $this->getCart();
		$config = SOYMailConnectorUtil::getConfig();
		
		$isCheck = (isset($_POST["customfield_module"][self::PLUGIN_ID]) && $_POST["customfield_module"][self::PLUGIN_ID] == SOYMailConnectorUtil::SEND);
		$label = (isset($config["label"])) ? $config["label"] : "メールマガジン";
		$value = ($isCheck) ? "希望する" : "希望しない";

		//属性の登録
		$cart->setAttribute(self::PLUGIN_ID . ".check", $isCheck);
		$cart->setOrderAttribute(self::PLUGIN_ID . ".value", $label, $value, true);
	}
	
	/**
	 * @param object CartLogic
	 */
	function order(CartLogic $cart){
		
		$isCheck = $cart->getAttribute(self::PLUGIN_ID . ".check");
		if(!is_null($isCheck)){
			$notSend = ($isCheck) ? SOYShop_User::USER_SEND : SOYShop_User::USER_NOT_SEND;			
			$user = $cart->getCustomerInformation();
			$user->setNotSend($notSend);
			$cart->setCustomerInformation($user);
		}
	}

	function getForm(CartLogic $cart){
		
		$this->prepare();
		$value = $cart->getAttribute(self::PLUGIN_ID . ".check");
		
		$config = SOYMailConnectorUtil::getConfig();
		
		$obj = array();
		$obj["name"] = (isset($config["label"])) ? $config["label"] : "メールマガジン";
		
		//Cart03を初めて開いた時
		if(is_null($value)){
			$isLoggedIn = $cart->getAttribute("logined");
			
			//ログインしているかチェック。
			if($isLoggedIn){
				$isCheck = ($cart->getCustomerInformation()->getNotSend() == SOYShop_User::USER_SEND);

			//ログインしていなければディフォルトの値をセット
			}else{
				$isCheck = (isset($config["isCheck"]) && $config["isCheck"] == SOYMailConnectorUtil::SEND);
			}
		//次のページから戻ってきた時
		}else{
			$isCheck = $value;
		}
		
		$description = (isset($config["description"])) ? $config["description"] : "メールマガジン希望する";
		
		$html = array();
		$html[] = "<input type=\"hidden\" name=\"customfield_module[" . self::PLUGIN_ID ."]\" value=\"" . SOYMailConnectorUtil::NOT_SEND .  "\">";
		if($isCheck){
			$html[] = "<label><input type=\"checkbox\" name=\"customfield_module[" . self::PLUGIN_ID ."]\" value=\"" . SOYMailConnectorUtil::SEND . "\" checked>" . $description . "</label>";
		}else{
			$html[] = "<label><input type=\"checkbox\" name=\"customfield_module[" . self::PLUGIN_ID ."]\" value=\"" . SOYMailConnectorUtil::SEND . "\">" . $description . "</label>";
		}
				
		$obj["description"] = implode("\n", $html);
		$obj["error"] = "";			
		
		return array(self::PLUGIN_ID => $obj);
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "soymail_connector", "SOYMailConnectorOrderCustomfield");
?>