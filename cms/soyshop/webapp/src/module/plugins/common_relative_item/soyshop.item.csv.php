<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/02/15
 */

class SOYShop_RelativeItem_CSV extends SOYShopItemCSVBase{

	const PLUGIN_ID = "_relative_items";

	function getLabel(){
		return "関連商品";
	}

	/**
	 * export
	 */
	function export($itemId){
		try{
			$dao = $this->getDAO();
			$attr = $dao->get($itemId, self::PLUGIN_ID);
			$array = soy2_unserialize($attr->getValue());
			$value = (is_array($array)) ? implode(" ",$array) : "";

			return $value;
		}catch(Exception $e){
			return "";
		}
	}

	/**
	 * import
	 */
	function import($itemId, $value){
		$value = trim($value);

		$dao = $this->getDAO();

		if(strlen($value)){
			try{
				$attr = $dao->get($itemId, self::PLUGIN_ID);
			}catch(Exception $e){
				$attr = new SOYShop_ItemAttribute();
				$attr->setItemId($itemId);
				$attr->setFieldId(self::PLUGIN_ID);
			}
			$attr->setValue(soy2_serialize(explode(" ",$value)));

			try{
				$dao->insert($attr);
			}catch(Exception $e){
				try{
					$dao->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}else{
			try{
				$dao->delete($itemId, self::PLUGIN_ID);
			}catch(Exception $e){
				//
			}
		}
	}

	function getDAO(){
		static $dao;
		if(!$dao) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}

}

SOYShopPlugin::extension("soyshop.item.csv","common_relative_item","SOYShop_RelativeItem_CSV");
//SOYShopPlugin::extension("soyshop.item.csv","common_relative_item2","SOYShop_RelativeItem_CSV");
//SOYShopPlugin::extension("soyshop.item.csv","common_relative_item3","SOYShop_RelativeItem_CSV");
