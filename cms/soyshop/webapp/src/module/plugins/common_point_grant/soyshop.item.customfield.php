<?php

class CommonPointGrantCustomField extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "common_point_base";

	function doPost(SOYShop_Item $item){

		if(isset($_POST[self::PLUGIN_ID])){
			$point = (strlen($_POST[self::PLUGIN_ID])) ? soyshop_convert_number($_POST[self::PLUGIN_ID], 0) : null;
			if($point === self::_getDefaultSetPointPercentage()) $point = null;	//データの軽量化
			$attr = soyshop_get_item_attribute_object($item->getId(), self::PLUGIN_ID);
			$attr->setValue($point);
			soyshop_save_item_attribute_object($attr);
		}
	}

	function getForm(SOYShop_Item $item){
		$p = (is_numeric($item->getId())) ? self::_getPercentage($item->getId()) : 0;

		$html = array();
		$html[] = "<div class=\"form-group\">";
		$html[] = "	<label>ポイント</label>";
		$html[] = "	<div class=\"form-inline\">";
		$html[] = "		<input type=\"number\" name=\"" . self::PLUGIN_ID . "\" class=\"form-control\" value=\"" . $p . "\" style=\"width:80px;ime-mode:inactive;\">&nbsp;%";
		$html[] = "	</div>";
		$html[] = "</div>";
		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		$itemId = (is_numeric($item->getId())) ? (int)$item->getId() : 0;

		$htmlObj->addLabel("item_point_grant_percentage", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => ($itemId > 0) ? SOY2Logic::createInstance("module.plugins.common_point_grant.logic.PointGrantLogic")->getPercentage($item) : 0
		));

		//common_point_baseから持ってきた
		$htmlObj->addLabel("item_point_percentage", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => ($itemId > 0) ? self::_getPercentage($item->getId()) : 0
		));
	}

	function onDelete(int $itemId){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($itemId);
	}

	private function _getPercentage(int $itemId){
		$v = soyshop_get_item_attribute_value($itemId, self::PLUGIN_ID, "int");
		if(is_numeric($v)) return (int)$v;
		return self::_getDefaultSetPointPercentage();
	}

	private function _getDefaultSetPointPercentage(){
		static $p;
		if(is_null($p)){
			SOY2::import("module.plugins.common_point_grant.util.PointGrantUtil");
			$cnf = PointGrantUtil::getConfig();
			$p = (int)$cnf["percentage"];
		}
		return $p;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "common_point_grant", "CommonPointGrantCustomField");
