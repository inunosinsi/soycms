<?php

class SOYShopColumn extends SOYInquiry_ColumnBase{

    /**
	 * ユーザに表示するようのフォーム
	 * @param array
	 * @return string
	 */
	function getForm(array $attrs=array()){
		if(!defined("SOYINQUERY_SOYSHOP_CONNECT_SITE_ID")) return "";
		$itemId = (SOYInquiryUtil::checkSOYShopInstall() && SOYINQUERY_SOYSHOP_CONNECT_SITE_ID && isset($_GET["item_id"]) && is_numeric($_GET["item_id"]) && $_GET["item_id"] > 0) ? SOYInquiryUtil::getParameter("item_id") : 0;

		$itemName = ($itemId > 0) ? SOY2Logic::createInstance("logic.SOYShopConnectLogic")->getItemNameByItemId($itemId) : "";
		
		$html = array();
		$html[] = $itemName;
		$html[] = "<input type=\"hidden\" name=\"data[" . $this->getColumnId() . "]\" value=\"" . $itemName . "\" />";
		return implode("\n", $html);
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

	function validate(){
		return true;
	}
}
