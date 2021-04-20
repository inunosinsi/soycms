<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/02/15
 */

class CommonPointGrantCSV extends SOYShopItemCSVBase{

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
	function import($itemId, $point){
		$point = trim($point);
		$point = (is_numeric($point)) ? (int)$point : 0;

		if(!$this->itemAttributeDao) $this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");

		if($point > 0){
			try{
				$attr = $this->itemAttributeDao->get($itemId, self::PLUGIN_ID);
			}catch(Exception $e){
				$attr = new SOYShop_ItemAttribute();
				$attr->setItemId($itemId);
				$attr->setFieldId(self::PLUGIN_ID);
			}
			$attr->setValue($point);

			try{
				$this->itemAttributeDao->insert($attr);
			}catch(Exception $e){
				try{
					$this->itemAttributeDao->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}else{
			try{
				$attr = $this->itemAttributeDao->delete($itemId, self::PLUGIN_ID);
			}catch(Exception $e){
				//
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.item.csv","common_point_grant","CommonPointGrantCSV");
