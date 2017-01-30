<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/02/15
 */

class CommonPointBaesCSV extends SOYShopItemCSVBase{

	const PLUGIN_ID = "common_point_base";
	private $itemAttributeDao;

	function getLabel(){
		return "ポイント";
	}

	/**
	 * export
	 * @param integer item_id
	 * @return value
	 */
	function export($itemId){
		if(!$this->itemAttributeDao) $this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$obj = $this->itemAttributeDao->get($itemId, self::PLUGIN_ID);
		}catch(Exception $e){
			$obj = new SOYShop_ItemAttribute();
		}

		return (!is_null($obj->getValue())) ? (int)$obj->getValue() : "";
	}

	/**
	 * import
	 * void
	 */
	function import($itemId, $value){
		$point = (int)$value;
		if($point > 0){
			if(!$this->itemAttributeDao) $this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			
			try{
				$array = $this->itemAttributeDao->getByItemId($itemId);
			}catch(Exception $e){
				$array = array();
			}
						
			if(isset($array[self::PLUGIN_ID])){
				$obj = $array[self::PLUGIN_ID];
				$obj->setValue($point);
				$this->itemAttributeDao->update($obj);
			}else{
				$obj = new SOYShop_ItemAttribute();
				$obj->setItemId($itemId);
				$obj->setFieldId(self::PLUGIN_ID);
				$obj->setValue($point);
	
				$this->itemAttributeDao->insert($obj);
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.item.csv","common_point_base","CommonPointBaesCSV");