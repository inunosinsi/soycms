<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/10/03
 */

class SOYShop_CustomIconField_CSV extends SOYShopItemCSVBase{

	function getLabel(){
		return "カスタムアイコンフィールド";
	}

	/**
	 * export
	 */
	function export($itemId){

		try{
			$dao = $this->getDAO();
			$attr = $dao->get($itemId, "custom_icon_field");

			return $attr->getValue();
		}catch(Exception $e){
			return "";
		}
	}

	/**
	 * import
	 */
	function import($itemId, $value){

		$dao = $this->getDAO();

		try{
			$attr = $dao->get($itemId, "custom_icon_field");
		}catch(Exception $e){
			if(strlen($value) < 1) return;

			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId("custom_icon_field");
			$dao->insert($attr);
		}

		$attr->setValue($value);

		if(strlen($value) > 0){
			$dao->update($attr);
		}else{
			$dao->delete($attr->getItemId(), $attr->getFieldId());
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

SOYShopPlugin::extension("soyshop.item.csv", "common_icon_field", "SOYShop_CustomIconField_CSV");