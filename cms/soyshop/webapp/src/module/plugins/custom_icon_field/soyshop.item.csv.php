<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/10/03
 */

class CustomIconFieldCSV extends SOYShopItemCSVBase{

	const PLUGIN_ID = "custom_icon_field";

	function getLabel(){
		return "カスタムアイコンフィールド";
	}

	/**
	 * export
	 */
	function export($itemId){
		try{
			return self::dao()->get($itemId, self::PLUGIN_ID)->getValue();
		}catch(Exception $e){
			return "";
		}
	}

	/**
	 * import
	 */
	function import($itemId, $value){

		$dao = self::dao();

		try{
			$attr = $dao->get($itemId, self::PLUGIN_ID);
		}catch(Exception $e){
			if(strlen($value) < 1) return;

			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId(self::PLUGIN_ID);
			$dao->insert($attr);
		}

		$attr->setValue($value);

		if(strlen($value) > 0){
			$dao->update($attr);
		}else{
			$dao->delete($attr->getItemId(), $attr->getFieldId());
		}
	}

	private function dao(){
		static $dao;
		if(!$dao) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}
}

SOYShopPlugin::extension("soyshop.item.csv", "common_icon_field", "CustomIconFieldCSV");
