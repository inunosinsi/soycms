<?php
/*
 */
class ItemStandardField extends SOYShopItemCustomFieldBase{

	const PLUGIN_ID = "item_standard_plugin";
	
	private $attrDao;

	function doPost(SOYShop_Item $item){
		
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		
		if(isset($_POST["Standard"])){
			self::prepare();
			foreach($_POST["Standard"] as $confId => $value){
				if(!strlen($value)) $value = null;

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
				
				//SINGLEに戻すとき、小商品をすべて削除したい
				if($item->getType() == SOYShop_Item::TYPE_SINGLE){
					try{
						$children = $itemDao->getByType($item->getId());
					}catch(Exception $e){
						return;
					}
					
					if(!count($children)) return;
					
					//データベース高速化のために完全削除
					foreach($children as $child){
						try{
							$itemDao->delete($child->getId());
						}catch(Exception $e){
							
						}
					}
				}
			}
			
			//セールの一括設定
			try{
				$children = $itemDao->getByType($item->getId());
			}catch(Exception $e){
				return;
			}
			
			$saleFlag = (int)$item->getSaleFlag();
			if(count($children)) foreach($children as $child){
				$child->setSaleFlag($saleFlag);
				try{
					$itemDao->update($child);
				}catch(Exception $e){
					//
				}
			}
		}
	}

	function getForm(SOYShop_Item $item){
		//子商品の詳細は表示させない
		if(is_numeric($item->getType())) SOY2PageController::jump("Item.Detail." . $item->getType());
		
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
		
		//小商品に在庫切れのものがあるか？
		$htmlObj->addModel("has_no_stock_child", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"visible" => (self::checkIsChildItemStock($item->getId(), $item->getType()))
		));
		
		
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
	
	private function checkIsChildItemStock($parentId, $type){
		if($type != "group") return false;
		
		$sql = "SELECT COUNT(*) FROM soyshop_item ".
				"WHERE item_type = :parentId ".
				"AND item_stock = 0 ".
				"AND is_disabled != 1";
				
		try{
			$res = $this->attrDao->executeQuery($sql, array(":parentId" => $parentId));
		}catch(Exception $e){
			$res = array();
		}
		
		return (isset($res[0]["COUNT(*)"]) && $res[0]["COUNT(*)"] > 0);
	}
	
	private function prepare(){
		if(!$this->attrDao) $this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	}
}

SOYShopPlugin::extension("soyshop.item.customfield", "item_standard", "ItemStandardField");
?>