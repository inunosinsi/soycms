<?php
/*
 * soyshop.item.csv.php
 * Created: 2010/02/15
 */

class DisplayCartLinkCSV extends SOYShopItemCSVBase{

	const PLUGIN_ID = "display_cart_link_plugin";
	const CHECKED = 1;

	function getLabel(){
		return "カートリンク非表示設定";
	}

	/**
	 * export
	 */
	function export($itemId){
		$dao = self::getDAO();

		try{
			$obj = $dao->get($itemId, self::PLUGIN_ID);
		}catch(Exception $e){
			$obj = new SOYShop_ItemAttribute();
		}

		return (int)$obj->getValue();
	}

	/**
	 * import
	 */
	function import($itemId, $value){

		$dao = $this->getDAO();

		try{
			$dao->delete($itemId, self::PLUGIN_ID);
		}catch(Exception $e){
			//
		}

		if((int)$value == self::CHECKED){
			try{
				$obj = new SOYShop_ItemAttribute();
				$obj->setItemId($itemId);
				$obj->setFieldId(self::PLUGIN_ID);
				$obj->setValue(self::CHECKED);

				$dao->insert($obj);
			}catch(Exception $e){
				//
			}
		}
	}

	private function getDAO(){
		static $dao;
		if(!$dao) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}

}

SOYShopPlugin::extension("soyshop.item.csv", "display_cart_lin", "DisplayCartLinkCSV");
?>
