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
		$value = trim(trim($value, ","));

		$dao = self::dao();

		if(strlen($value)){
			try{
				$attr = $dao->get($itemId, self::PLUGIN_ID);
			}catch(Exception $e){
				$attr = new SOYShop_ItemAttribute();
				$attr->setItemId($itemId);
				$attr->setFieldId(self::PLUGIN_ID);
			}
			$attr->setValue($value);

			try{
				$dao->insert($attr);
			}catch(Exception $e){
				try{
					$dao->update($attr);
				}catch(Exception $e){
					//
				}
			}

		}else{	//削除
			try{
				$dao->delete($itemId, self::PLUGIN_ID);
			}catch(Exception $e){
				//
			}
		}
	}

	private function dao(){
		static $dao;
		if(!$dao) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}
}

SOYShopPlugin::extension("soyshop.item.csv", "common_icon_field", "CustomIconFieldCSV");
