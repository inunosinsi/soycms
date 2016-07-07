<?php
/*
 */
class ItemStandardField extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "item_standard_plugin";
	
	private $attrDao;

	function doPost(SOYShop_Item $item){
		
		if(isset($_POST["Standard"])){
			self::prepare();
			foreach($_POST["Standard"] as $confId => $value){
				$obj = self::get($item->getId(), $confId);
				$obj->setValue(trim($value));
				
				//新規
				if(is_null($obj->getItemId())){
					$obj->setItemId($item->getId());
					$obj->setFieldId(self::PLUGIN_ID . "_" . $confId);
					try{
						$this->attrDao->insert($obj);
						$res = true;
					}catch(Exception $e){
						//
					}
					
				//更新
				}else{
					try{
						$this->attrDao->update($obj);
					}catch(Exception $e){
						//
					}
				}
			}
			
			//登録終了した後、商品のタイプをsingleからgroupに変更　逆もある
			$res = false;
			foreach($_POST["Standard"] as $std){
				if(strlen($std)) {
					$res = true;
					break;
				}
			}
			
			$exe = false;
			$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
			if($res){
				if($item->getType() == SOYShop_Item::TYPE_SINGLE){
					$item->setType(SOYShop_Item::TYPE_GROUP);
					$exe = true;
				}
			}else{
				if($item->getType() == SOYShop_Item::TYPE_GROUP){
					$item->setType(SOYShop_Item::TYPE_SINGLE);
					$exe = true;
				}
			}
				
			if($exe){
				try{
					$itemDao->update($item);
				}catch(Exception $e){
					//
				}
			}
		}
	}

	function getForm(SOYShop_Item $item){
		//子商品の詳細は表示させない
		if(is_numeric($item->getType())) SOY2PageController::jump("Item.Detail." . $item->getId());
		
		return SOY2Logic::createInstance("module.plugins.item_standard.logic.BuildFormLogic", array("parentId" => $item->getId()))->buildCustomFieldArea();
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		SOY2::import("module.plugins.item_standard.util.ItemStandardUtil");
		self::prepare();
		
		foreach(ItemStandardUtil::getConfig() as $values){
			$obj = self::get($item->getId(), $values["id"]);
			
			
			$htmlObj->addModel("item_standard_" . $values["id"] . "_show", array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"visible" => (strlen($obj->getValue()))
			));
			
			$htmlObj->addSelect("item_standard_" . $values["id"], array(
				"soy2prefix" => SOYSHOP_SITE_PREFIX,
				"name" => "Standard[" . $values["id"] . "]",
				"options" => explode("\n", $obj->getValue())
			));
		}
		
		
		//カートを表示する場合は$obj->getValue()が1ではない		
//		$htmlObj->addModel("has_cart_link", array(
//			"soy2prefix" => SOYSHOP_SITE_PREFIX,
//			"visible" => ($obj->getValue() != self::CHECKED)
//		));
	}

	function onDelete($id){}
	
	private function get($itemId, $confId){
		try{
			return $this->attrDao->get($itemId, self::PLUGIN_ID . "_" . $confId);
		}catch(Exception $e){
			return new SOYShop_ItemAttribute();
		}
	}
	
	private function prepare(){
		if(!$this->attrDao) $this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "item_standard", "ItemStandardField");
?>