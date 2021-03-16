<?php

class SOYShopColumn extends SOYInquiry_ColumnBase{

    /**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){
		$itemName = null;
		if(SOYInquiryUtil::checkSOYShopInstall() && SOYINQUERY_SOYSHOP_CONNECT_SITE_ID && isset($_GET["item_id"]) && is_numeric($_GET["item_id"])){
			$itemName = self::_getItemName($itemId);
		}

		if(strlen($itemName)) $itemName = htmlspecialchars($itemName, ENT_QUOTES, "UTF-8");
		$html = array();
		$html[] = $itemName;
		$html[] = "<input type=\"hidden\" name=\"data[" . $this->getColumnId() . "]\" value=\"" . $itemName . "\" />";

		return implode("\n",$html);

	}

	private function _getItemName(){
		$old = SOYInquiryUtil::switchSOYShopConfig(SOYInquiryUtil::getSOYShopSiteId());
		$name = null;
		try{
			$item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById((int)$_GET["item_id"]);
			if($item->isPublished()) $name = trim($item->getOpenItemName());
		}catch(Exception $e){
			//
		}
		SOYInquiryUtil::resetConfig($old);
		return $name;
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
	}

	function getConfigure(){
		return parent::getConfigure();
	}

	function validate(){}
}
