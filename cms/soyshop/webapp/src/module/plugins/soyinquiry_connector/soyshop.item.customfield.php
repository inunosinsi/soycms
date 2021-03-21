<?php
/*
 */
class SOYInquiryConnectorCustomField extends SOYShopItemCustomFieldBase{

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		SOY2::import("module.plugins.soyinquiry_connector.util.SOYInquiryConnectorUtil");
		$cnf = SOYInquiryConnectorUtil::getConfig();
		$url = (isset($cnf["url"])) ? $cnf["url"] : "";

		//すでにGETの値がある場合
		if(is_numeric(strpos($url, "?"))){
			$url .= "&amp;item_id=" . $item->getId();
		//ない場合
		}else{
			$url .= "?item_id=" . $item->getId();
		}

		$htmlObj->addLink("inquiry_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"link" => $url
		));
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","soyinquiry_connector","SOYInquiryConnectorCustomField");
