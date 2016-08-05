<?php
class CopyPage extends WebPage{
	
	private $id;
	
	function __construct($args){
		
		$this->id = (isset($args[0])) ? (int)$args[0] : null;
		
		if(soy2_check_token()){
			$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			
			try{
				$old = $itemDao->getById($this->id);
			}catch(Exception $e){
				SOY2PageController::jump("Item.Detail." . $this->id . "?error");
			}
			
			if($old->getType() == SOYShop_Item::TYPE_SINGLE || $old->getType() == SOYShop_Item::TYPE_DOWNLOAD){
				$itemId = $old->getId();
				$try = 0;

				for(;;){
					$old->setId(null);
					$old->setName($old->getName() . "_copy");
					$old->setCode($old->getCode() . "_copy");
					$old->setAlias(null);
					$old->setIsOpen(SOYShop_Item::NO_OPEN);
					$old->setOrderPeriodStart(SOYShop_Item::PERIOD_START);
					$old->setOrderPeriodStart(SOYShop_Item::PERIOD_END);
					$old->setOpenPeriodStart(SOYShop_Item::PERIOD_START);
					$old->setOpenPeriodStart(SOYShop_Item::PERIOD_END);
									
					try{
						$newId = $itemDao->insert($old);
						break;
					}catch(Exception $e){
						$try++;
					}
					
					if($try > 10) SOY2PageController::jump("Item.Detail." . $this->id . "?error");
				}
				
					
				
				$itemAttrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
				try{
					$attrs = $itemAttrDao->getByItemId($itemId);
				}catch(Exception $e){
					$attrs = array();
				}
				
				if(count($attrs)) foreach($attrs as $attr){
					$attr->setItemId($newId);
					try{
						$itemAttrDao->insert($attr);
					}catch(Exception $e){
						//
					}
				}
				
				//カスタムサーチフィールド
				if(class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("custom_search_field"))){
					$logic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic");
					$values = $logic->getByItemId($itemId);
					unset($values["item_id"]);
					$logic->save($newId, $values);
				}
				
				SOY2PageController::jump("Item.Detail." . $newId . "?copy");
			}
		}
		
		SOY2PageController::jump("Item.Detail." . $this->id . "?error");
	}
}
?>