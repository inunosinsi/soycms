<?php

class CommonPointBaseCustomField extends SOYShopItemCustomFieldBase{
	
	const PLUGIN_ID = "common_point_base"; 
	
	private $itemAttributeDao;
	private $percentage;

	function doPost(SOYShop_Item $item){
		
		if(isset($_POST[self::PLUGIN_ID])){
			$price = soyshop_convert_number($_POST[self::PLUGIN_ID], 0);

			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$array = $dao->getByItemId($item->getId());
						
			if(isset($array[self::PLUGIN_ID])){
				$obj = $array[self::PLUGIN_ID];
				$obj->setValue($price);
				$dao->update($obj);
			}else{
				$obj = new SOYShop_ItemAttribute();
				$obj->setItemId($item->getId());
				$obj->setFieldId(self::PLUGIN_ID);
				$obj->setValue($price);
	
				$dao->insert($obj);
			}
		}
	}

	function getForm(SOYShop_Item $item){
				
		$html = array();
		$html[] = "<dt>ポイント</dt>";
		$html[] = "<dd>";
		$html[] = "<input type=\"text\" name=\"" . self::PLUGIN_ID . "\" value=\"" . $this->getPercentage($item) . "\" style=\"width:40px;ime-mode:inactive;\">&nbsp;%";
		$html[] = "</dd>";

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$htmlObj->addLabel("item_point_percentage", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $this->getPercentage($item)
		));
	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attributeDAO->deleteByItemId($id);
	}
	
	function getPercentage(SOYShop_Item $item){
		$this->prepare();
		
		try{
			$obj = $this->itemAttributeDao->get($item->getId(), self::PLUGIN_ID);
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
			$obj = new SOYShop_ItemAttribute();
		}
		
		return (!is_null($obj->getValue())) ? (int)$obj->getValue() : $this->percentage;
	}
	
	function prepare(){
		if(!$this->itemAttributeDao) $this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		if(!$this->percentage){
			SOY2::imports("module.plugins.common_point_base.util.*");
			$config = PointBaseUtil::getConfig();
			$this->percentage = (int)$config["percentage"];
		}
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_point_base","CommonPointBaseCustomField");
?>