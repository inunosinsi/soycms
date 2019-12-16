<?php
/*
 */
class SOYInquiryConnectorCustomField extends SOYShopItemCustomFieldBase{

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		include_once(dirname(__FILE__) . "/common.php");
		$config = SOYInquiryConnectorCommon::getConfig();
		
		$url = "";
		
		if(isset($config["url"])){
			$url = $config["url"];
		}
		
		//すでにGETの値がある場合
		if(strpos($url, "?")!==false){
			
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
?>