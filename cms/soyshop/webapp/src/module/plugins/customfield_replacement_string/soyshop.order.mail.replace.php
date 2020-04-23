<?php

class CustomfieldReplacementStringMailReplace extends SOYShopOrderMailReplace{

	function strings(){
		$strings = array();
		$strings["CUSTOMFIELD"] = "カスタムフィールド";
		return $strings;
	}

	function replace(SOYShop_Order $order, $content){
		return str_replace("#CUSTOMFIELD#", self::_buildText($order->getId()), $content);
	}

	private function _buildText($orderId){
		static $content;
		if(is_null($content)){
			$content = "";
			try{
				$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($orderId);
			}catch(Exception $e){
				return "";
			}
			if(!count($itemOrders)) return "";

			SOY2::import("module.plugins.customfield_replacement_string.util.CustomReplaceUtil");
			$cnf = CustomReplaceUtil::getConfig();

			if(!isset($cnf["fieldId"]) || !strlen($cnf["fieldId"])) return "";
			if(!isset($cnf["format"]) || !strlen($cnf["format"])) return "";

			$fieldId = $cnf["fieldId"];
			$fmt = $cnf["format"];

			$replaces = CustomReplaceUtil::getReplacementStringList();

			SOY2::import("domain.shop.SOYShop_ItemAttribute");
			$customs = SOYShop_ItemAttributeConfig::load(true);

			$attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

			$txts = array();
			foreach($itemOrders as $itemOrder){
				$item = soyshop_get_item_object($itemOrder->getItemId());
				$txt = $fmt;
				foreach($replaces as $rpl => $label){
					if(strpos($fmt, "##".$rpl."##") === false) continue;
					switch($rpl){
						case "ITEM_NAME":
							$t = $item->getName();
							break;
						case "ITEM_CODE":
							$t = $item->getCode();
							break;
						case "LABEL":
							$t = (isset($customs[$fieldId])) ? $customs[$fieldId]->getLabel() : "";
							break;
						case "FIELD_ID":
							$t = $fieldId;
							break;
						case "VALUE":
							try{
								$t = $attrDao->get($item->getId(), $fieldId)->getValue();
							}catch(Exception $e){
								$t = "";
							}
							break;
					}
					$txt = str_replace("##".$rpl."##", $t, $txt);
				}
				$txts[] = $txt;
			}
			$content = implode("\n", $txts);
		}

		return $content;
	}
}

SOYShopPlugin::extension("soyshop.order.mail.replace", "customfield_replacement_string", "CustomfieldReplacementStringMailReplace");
