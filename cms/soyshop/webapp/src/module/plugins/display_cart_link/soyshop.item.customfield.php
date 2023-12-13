<?php
/*
 */
class DisplayCartLink extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "display_cart_link_plugin";
	const CHECKED = 1;

	function doPost(SOYShop_Item $item){
		$attr = soyshop_get_item_attribute_object((int)$item->getId(), self::PLUGIN_ID);
		if(isset($_POST[self::PLUGIN_ID])){
			$attr->setValue(self::CHECKED);
		}else{
			$attr->setValue(null);
		}
		soyshop_save_item_attribute_object($attr);
	}

	function getForm(SOYShop_Item $item){
		$checked = (soyshop_get_item_attribute_value((int)$item->getId(), self::PLUGIN_ID, "int") == self::CHECKED);

		$html = array();		
		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>カートに入れるボタンの設定</label><br>";
		$html[] = "<label>";
		if($checked){
			$html[] = "<input type=\"checkbox\" name=\"display_cart_link_plugin\" value=\"1\" id=\"display_cart_link\" checked=\"checked\" />";
		}else{
			$html[] = "<input type=\"checkbox\" name=\"display_cart_link_plugin\" value=\"1\" id=\"display_cart_link\" />";
		}
		$html[] = "カートに入れるボタンを非表示にする</label>";
		$html[] = "</div>";
		
		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		$attrValue = soyshop_get_item_attribute_value((int)$item->getId(), self::PLUGIN_ID, "int");
		
		//カートを表示する場合は$obj->getValue()が1ではない		
		$htmlObj->addModel("has_cart_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($attrValue != self::CHECKED)
		));
		
		$htmlObj->addModel("no_cart_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($attrValue == self::CHECKED)
		));
	}

	function onDelete(int $itemId){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($itemId);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "display_cart_link", "DisplayCartLink");
