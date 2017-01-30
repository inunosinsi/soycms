<?php
class TwitterProductCardsCustomField extends SOYShopItemCustomFieldBase{
	
	private $dao;

	function doPost(SOYShop_Item $item){
		
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$array = $dao->getByItemId($item->getId());
		
		$configs = SOYShop_ItemAttributeConfig::load(true);
			
		$key = "twitter_product_cards";
		
		try{
			$dao->delete($item->getId(),$key);
		}catch(Exception $e){
			
		}
		
		if(isset($_POST["twitter_product_cards"])){
			$values = soy2_serialize($_POST["twitter_product_cards"]);
			try{
				$obj = new SOYShop_ItemAttribute();
				$obj->setItemId($item->getId());
				$obj->setFieldId($key);
				$obj->setValue($values);
	
				$dao->insert($obj);
			}catch(Exception $e){
					//
			}
		}
	}

	function getForm(SOYShop_Item $item){
		
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$array = $dao->getByItemId($item->getId());
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
		}

		if(isset($array["twitter_product_cards"])){
			$values = soy2_unserialize($array["twitter_product_cards"]->getValue());
		}else{
			$values = array();
		}
		
		$label = (isset($values["label"])) ? $values["label"] : "";
		$value = (isset($values["value"])) ? $values["value"] : "";
		
		$html[] = "<h1>Twitter Cards:Product Card設定</h1>";
		$html[] = "<dt><label for=\"twitter_product_cards\">Twitter Product Cardsのオプション値の追加</label><br />";
		$html[] = "<span style=\"font-size:0.9em;\"></span></dt>";
		$html[] = "<dd>";
		$html[] = "ラベル：<input type=\"text\" name=\"twitter_product_cards[label]\" value=\"" . $label . "\">";
		$html[] = "</dd>";
		$html[] = "<dd>";
		$html[] = "値：<input type=\"text\" name=\"twitter_product_cards[value]\" value=\"" . $value . "\">";
		$html[] = "</dd>";
				
		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
	}

	function onDelete($id){
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "twitter_product_cards", "TwitterProductCardsCustomField");
?>
