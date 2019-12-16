<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/02/15
 */

class SOYShop_RelativeItem_CSV extends SOYShopItemCSVBase{

	function getLabel(){
		return "関連商品";
	}

	/**
	 * export
	 */
	function export($itemId){
		try{
			$dao = $this->getDAO();
			$attr = $dao->get($itemId,"_relative_items");
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
	function import($itemId,$value){

		$dao = $this->getDAO();

		try{
			$attr = $dao->get($itemId,"_relative_items");
		}catch(Exception $e){
			if(strlen($value) < 1)return;

			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId("_relative_items");
			$dao->insert($attr);

		}

		$array = explode(" ",$value);
		$attr->setValue(soy2_serialize($array));

		if(strlen($value) > 0){
			$dao->update($attr);
		}else{
			$dao->delete($attr->getItemId(),$attr->getFieldId());
		}
	}

	function getDAO(){
		static $dao;
		if(!$dao){
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		}

		return $dao;
	}

}

SOYShopPlugin::extension("soyshop.item.csv","common_relative_item","SOYShop_RelativeItem_CSV");
//SOYShopPlugin::extension("soyshop.item.csv","common_relative_item2","SOYShop_RelativeItem_CSV");
//SOYShopPlugin::extension("soyshop.item.csv","common_relative_item3","SOYShop_RelativeItem_CSV");