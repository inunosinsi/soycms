<?php
/*
 */
class DisplayCartLink extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "display_cart_link_plugin";
	const CHECKED = 1;

	function doPost(SOYShop_Item $item){
			
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$configs = SOYShop_ItemAttributeConfig::load(true);

		try{
			$dao->delete($item->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			//	
		}

		if(isset($_POST[self::PLUGIN_ID])){
			
			try{
				$obj = new SOYShop_ItemAttribute();
				$obj->setItemId($item->getId());
				$obj->setFieldId(self::PLUGIN_ID);
				$obj->setValue(self::CHECKED);
	
				$dao->insert($obj);
			}catch(Exception $e){
				//
			}
		}			
	}

	function getForm(SOYShop_Item $item){

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$obj = $dao->get($item->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			$obj = new SOYShop_ItemAttribute();
		}

		$checked = ($obj->getValue() == self::CHECKED);

		$html = array();
		
		$html[] = "<dt>カートに入れるボタンの設定</dt>";

		$html[] = "<dd>";
		if($checked){
			$html[] = "<input type=\"checkbox\" name=\"display_cart_link_plugin\" value=\"1\" id=\"display_cart_link\" checked=\"checked\" />";
		}else{
			$html[] = "<input type=\"checkbox\" name=\"display_cart_link_plugin\" value=\"1\" id=\"display_cart_link\" />";
		}
		$html[] = "<label for\"display_cart_link\">カートに入れるボタンを非表示にする</label>";
		$html[] = "</dd>";
		
		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$obj = $dao->get($item->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			$obj = new SOYShop_ItemAttribute();
		}
		
		//カートを表示する場合は$obj->getValue()が1ではない		
		$htmlObj->addModel("has_cart_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($obj->getValue() != self::CHECKED)
		));
		
		$htmlObj->addModel("no_cart_link", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($obj->getValue() == self::CHECKED)
		));

	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attributeDAO->deleteByItemId($id);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "display_cart_link", "DisplayCartLink");
?>