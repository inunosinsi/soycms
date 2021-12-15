<?php

include(dirname(__FILE__) . "/common.php");
class CommonItemDescriptionField extends SOYShopItemCustomFieldBase{

	const FIELD_ID = "item_descrption_column";

	function doPost(SOYShop_Item $item){
		$attr = soyshop_get_item_attribute_object($item->getId(), self::FIELD_ID);
		if(isset($_POST[self::FIELD_ID]) is_array($_POST[self::FIELD_ID]) && count($_POST[self::FIELD_ID])){
			$arr = array();
			foreach($_POST[self::FIELD_ID] as $key => $value){
				$arr[] = array("id" => $key);
			}
			$v = soy2_serialize($array);
		}else{
			$v = null;
		}
		$attr->setValue($v);
		soyshop_save_item_attribute_object($attr);
	}

	function getForm(SOYShop_Item $item){

		$class = new ItemDescriptionClass();

		$html = array();

		$html[] = "<h1>詳細情報の表示設定</h1>";
		$html[] = "<dd>";
		$html[] = "<p>表示したい内容にチェックをしてください。</p>";

		$values = soy2_unserialize(SOYShop_DataSets::get("item_description", ""));
		$ids = soy2_unserialize(soyshop_get_item_attribute_value($item->getId(), self::FIELD_ID, "string"));

		if(count($values) && count($ids)){
			for($i = 0; $i < count($values); $i++){
				$flag = false;
				if($ids){
					foreach($ids as $id){
						if($values[$i]["column"] == $id["id"]){
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

		$ids = soy2_unserialize(soyshop_get_item_attribute_value($item->getId(), self::FIELD_ID, "string"));

		$htmlObj->createAdd("is_item_description","HTMLModel", array(
			"soy2prefix" => "block",
			"visible" => (count($ids) > 0)
		));

		$values = soy2_unserialize(SOYShop_DataSets::get("item_description", ""));
		$html = array();

		if(count($values) && count($ids)){
			for($i = 0; $i < count($values); $i++){
				$flag = false;
				foreach($ids as $id){
					if($values[$i]["column"]==$id["id"]){
						$html[] = $values[$i]["value"];
						break;
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
