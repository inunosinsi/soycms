<?php

class SOYShopColumn extends SOYInquiry_ColumnBase{

    /**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$itemName = "";

		if(SOYInquiryUtil::checkSOYShopInstall() && isset($_GET["item_id"]) && is_numeric($_GET["item_id"])){
			$itemId = (int)$_GET["item_id"];

			if(SOYINQUERY_SOYSHOP_CONNECT_SITE_ID){
				$shopId = SOYInquiryUtil::getSOYShopSiteId();
				$old = SOYInquiryUtil::switchSOYShopConfig($shopId);

				$itemName = $this->getItemName($itemId);

				SOYInquiryUtil::resetConfig($old);
			}
		}

		$html = array();
		$html[] = htmlspecialchars($itemName,ENT_QUOTES,"UTF-8");
		$html[] = "<input type=\"hidden\" name=\"data[" . $this->getColumnId() . "]\" value=\"" . htmlspecialchars($itemName,ENT_QUOTES,"UTF-8") . "\" />";

		return implode("\n",$html);

	}

	function getItemName($itemId){
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			$item = $itemDao->getById($itemId);
		}catch(Exception $e){
			return "";
		}

		return $item->getName();
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
	}

	function getConfigure(){
		$config = parent::getConfigure();
		return $config;
	}

	function validate(){
	}
}
