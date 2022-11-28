<?php

class SOYShopColumn extends SOYInquiry_ColumnBase{

    /**
	 * ユーザに表示するようのフォーム
	 * @param array
	 * @return string
	 */
	function getForm(array $attrs=array()){
		if(!defined("SOYINQUERY_SOYSHOP_CONNECT_SITE_ID")) return "";
		$itemName = (SOYInquiryUtil::checkSOYShopInstall() && SOYINQUERY_SOYSHOP_CONNECT_SITE_ID && isset($_GET["item_id"]) && is_numeric($_GET["item_id"])) ? self::_getItemName($_GET["item_id"]) : "";

		$html = array();
		$html[] = $itemName;
		$html[] = "<input type=\"hidden\" name=\"data[" . $this->getColumnId() . "]\" value=\"" . $itemName . "\" />";
		return implode("\n", $html);
	}

	/**
	 * @param int
	 * @return string
	 */
	private function _getItemName(int $itemId){
		$old = SOYInquiryUtil::switchSOYShopConfig(SOYInquiryUtil::getSOYShopSiteId());
		$name = "";
		try{
			$item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($itemId);
			if($item->isPublished()) $name = trim($item->getOpenItemName());
		}catch(Exception $e){
			//
		}
		SOYInquiryUtil::resetConfig($old);
		return htmlspecialchars($name, ENT_QUOTES, "UTF-8");
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure(array $config){
		SOYInquiry_ColumnBase::setConfigure($config);
	}

	function getConfigure(){
		return parent::getConfigure();
	}

	function validate(){}
}
