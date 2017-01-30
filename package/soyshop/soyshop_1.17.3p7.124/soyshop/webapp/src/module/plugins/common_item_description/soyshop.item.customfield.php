<?php

include(dirname(__FILE__) . "/common.php");
class CommonItemDescriptionField extends SOYShopItemCustomFieldBase{

	function doPost(SOYShop_Item $item){
			
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$array = $dao->getByItemId($item->getId());
		
		$configs = SOYShop_ItemAttributeConfig::load(true);
			
		$fieldKey = "item_descrption_column";
		$value = 1;
			
		try{
			$dao->delete($item->getId(),$fieldKey);
		}catch(Exception $e){
			
		}
			
		if(isset($_POST["item_descrption_column"])){
			
			$columns = $_POST["item_descrption_column"];
			
			$array = array();
			if(count($columns) > 0){
				foreach($columns as $key => $value){
					$obj = array();
					$obj["id"] = $key;
					$array[] = $obj;
				}
				
				if(count($array) > 0){
					try{
						$obj = new SOYShop_ItemAttribute();
						$obj->setItemId($item->getId());
						$obj->setFieldId($fieldKey);
						$obj->setValue(soy2_serialize($array));
		
						$dao->insert($obj);
					}catch(Exception $e){
						var_dump($e);
					}
				}
			}
				
		}
	}

	function getForm(SOYShop_Item $item){
		
		$class = new ItemDescriptionClass();
		
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$attributes = $dao->getByItemId($item->getId());
		}catch(Exception $e){
			echo $e->getPDOExceptionMessage();
		}

		$attribute = (isset($attributes["item_descrption_column"])) ? $attributes["item_descrption_column"]->getValue() : null;
		
		$obj = SOYShop_DataSets::get("item_description", null);
		
		$html = array();
		
		$html[] = "<h1>詳細情報の表示設定</h1>";
		$html[] = "<dd>";
		$html[] = "<p>表示したい内容にチェックをしてください。</p>";
		
		if(!is_null($obj)){
			$values = soy2_unserialize($obj);
			$ids = soy2_unserialize($attribute);
			
			for($i=0;$i<count($values);$i++){
				$flag = false;
				if($ids){
					foreach($ids as $id){
						if($values[$i]["column"]==$id["id"]){
							$flag =true;
							break;
						}
					}
				}
				$html[] = $class->buildCheckBox($values[$i]["name"],$values[$i]["column"],$flag);				
			}		
		}
		
		$html[] = "<p><a href=\"".SOY2PageController::createLink("Config.Detail?plugin=common_item_description")."\">説明文の追加</a>";
		$html[] = "</dd>";
		
		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		
		$class = new ItemDescriptionClass();
		
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		try{
			$attributes = $dao->getByItemId($item->getId());
		}catch(Exception $e){

		}
		
		$ids = (isset($attributes["item_descrption_column"])) ? soy2_unserialize($attributes["item_descrption_column"]->getValue()) : array();
		
		$htmlObj->createAdd("is_item_description","HTMLModel", array(
			"soy2prefix" => "block",
			"visible" => (count($ids) > 0)
		));
		
		$obj = SOYShop_DataSets::get("item_description", null);
		$html = array();
		if(!is_null($obj)){
			$values = soy2_unserialize($obj);

			for($i=0;$i<count($values);$i++){
				$flag = false;
				if($ids){
					foreach($ids as $id){
						if($values[$i]["column"]==$id["id"]){
							$html[] = $values[$i]["value"];
							break;
						}
					}
				}
			}
		}
		
		$htmlObj->createAdd("item_description","HTMLLabel", array(
			"soy2prefix" => "cms",
			"html" => implode("\n", $html)
		));
	}

	function onDelete($id){
		$attributeDAO = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attributeDAO->deleteByItemId($id);
	}

}

SOYShopPlugin::extension("soyshop.item.customfield","common_item_description","CommonItemDescriptionField");
?>