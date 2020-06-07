<?php
class DiscountItemStockCustomField extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "discount_item_stock";
	private $discountLogic;

	function doPost(SOYShop_Item $item){

		$check = (isset($_POST[self::PLUGIN_ID])) ? (int)$_POST[self::PLUGIN_ID] : 0;

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$array = $dao->getByItemId($item->getId());

		if(isset($array[self::PLUGIN_ID])){
			$obj = $array[self::PLUGIN_ID];
			$obj->setValue($check);
			$dao->update($obj);
		}else{
			$obj = new SOYShop_ItemAttribute();
			$obj->setItemId($item->getId());
			$obj->setFieldId(self::PLUGIN_ID);
			$obj->setValue($check);

			$dao->insert($obj);
		}
	}

	function getForm(SOYShop_Item $item){
		$attributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

		try{
			$obj = $attributeDao->get($item->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			$obj = new SOYShop_ItemAttribute();
		}

		$check = ((int)$obj->getValue());

		$html[] = "<div class=\"form-group\">";
		$html[] = "<label>在庫数割引</label>";
		$html[] = "<div class=\"form-inline\">";
		$html[] = "<label>";
		if($check){
			$html[] = "<input type=\"checkbox\" name=\"" . self::PLUGIN_ID . "\" value=\"1\" checked=\"checked\">";
		}else{
			$html[] = "<input type=\"checkbox\" name=\"" . self::PLUGIN_ID . "\" value=\"1\">";
		}

		$html[] = "在庫数割引対象商品にする";
		$html[] = "</label>";
		$html[] = "</div>";
		$html[] = "</div>";

		return implode("\n", $html);
	}

	function onOutput($htmlObj, SOYShop_Item $item){
		$this->prepare();

		//値引き後の価格
		$htmlObj->addLabel("item_stock_discount_price", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => (!is_null($item->getId())) ? $this->discountLogic->getDiscountPrice($item) : 0
		));

		$rate = (!is_null($item->getId())) ? $this->discountLogic->getDiscountRate($item) : 0;

		$htmlObj->addModel("item_stock_discount_rate_visible", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => ($rate > 0 && $item->getStock())
		));

		$htmlObj->addLabel("item_stock_discount_rate", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $rate
		));
	}

	function onDelete($id){
		try{
			SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($id);
		}catch(Exception $e){
			//
		}

	}

	function prepare(){
		if(!$this->discountLogic) $this->discountLogic = SOY2Logic::CreateInstance("module.plugins.discount_item_stock.logic.DiscountLogic");
	}
}
SOYShopPlugin::extension("soyshop.item.customfield", "discount_item_stock", "DiscountItemStockCustomField");
