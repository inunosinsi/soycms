<?php

include(dirname(__FILE__) . "/common.php");
class CommonItemDescriptionField extends SOYShopItemCustomFieldBase{

	const FIELD_ID = "item_descrption_column";

	function doPost(SOYShop_Item $item){
		$attr = soyshop_get_item_attribute_object($item->getId(), self::FIELD_ID);
		if(isset($_POST[self::FIELD_ID]) && is_array($_POST[self::FIELD_ID]) && count($_POST[self::FIELD_ID])){
			$arr = array();
			foreach($_POST[self::FIELD_ID] as $key => $_dust){
				$arr[] = array("id" => $key);
			}
			$v = soy2_serialize($arr);
		}else{
			$v = null;
		}
		$attr->setValue($v);
		soyshop_save_item_attribute_object($attr);
	}

	function getForm(SOYShop_Item $item){

		$class = new ItemDescriptionClass();

		$html = array();

		$html[] = "<div class=\"alert alert-success\">詳細情報の表示設定</div>";
		$html[] = "<strong>表示したい内容にチェックをしてください。</strong><br>";

		$values = soy2_unserialize(SOYShop_DataSets::get("item_description", ""));
		$ids = soy2_unserialize(soyshop_get_item_attribute_value($item->getId(), self::FIELD_ID, "string"));
		
		if(count($values)){
			for($i = 0; $i < count($values); $i++){
				$flg = false;
				if(count($ids)){
					foreach($ids as $id){
						if($values[$i]["column"] == $id["id"]){
							$flg =true;
							break;
						}
					}
				}
				
				$html[] = $class->buildCheckBox($values[$i]["name"],$values[$i]["column"], $flg);
			}
		}

		$html[] = "<p><a href=\"".SOY2PageController::createLink("Config.Detail?plugin=common_item_description")."\" class=\"btn btn-info btn-sm\">説明文の追加</a></p>";
		$html[] = "<div class=\"alert alert-success\">詳細情報の表示設定ここまで</div><br>";

		return implode("\n", $html);
	}

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		$ids = soy2_unserialize(soyshop_get_item_attribute_value($item->getId(), self::FIELD_ID, "string"));

		$htmlObj->addModel("is_item_description", array(
			"soy2prefix" => "block",
			"visible" => (count($ids) > 0)
		));

		$values = (count($ids)) ? soy2_unserialize(SOYShop_DataSets::get("item_description", "")) : array();

		$html = array();
		if(count($values) && count($ids)){
			for($i = 0; $i < count($values); $i++){
				foreach($ids as $id){
					if($values[$i]["column"]==$id["id"]){
						$html[] = $values[$i]["value"];
						break;
					}
				}
			}
		}

		$htmlObj->addLabel("item_description", array(
			"soy2prefix" => "cms",
			"html" => implode("\n", $html)
		));
	}

	function onDelete(int $itemId){
		SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO")->deleteByItemId($itemId);
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","common_item_description","CommonItemDescriptionField");

