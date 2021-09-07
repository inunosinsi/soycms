<?php

class AutoCompletionUtil {

	const FIELD_ID = "auto_completion_item_name";

	public static function getConfig(){
		return SOYShop_DataSets::get(self::FIELD_ID . ".config", array(
			"count" => 10	//ヒット件数
		));
	}

	public static function saveConfig($values){
		if(!isset($values["count"]) || !is_numeric($values["count"])) $values["count"] = 10;
		SOYShop_DataSets::put(self::FIELD_ID . ".config", $values);
	}

	public static function getReadings(int $itemId){
		$dao = new SOY2DAO();
		try{
			$res = $dao->executeQuery(
				"SELECT item_field_id, item_value FROM soyshop_item_attribute ".
				"WHERE item_id = :itemId ".
				"AND item_field_id LIKE :fieldId ",
				array(
					":itemId" => $itemId,
					":fieldId" => "auto_completion_item_name_%"
				)
			);
		}catch(Exception $e){
			$res = array();
		}

		$hiragana = "";
		$katakana = "";

		if(count($res)){
			foreach($res as $v){
				if(is_numeric(strpos($v["item_field_id"], "hiragana"))){
					$hiragana = $v["item_value"];
				}else{
					$katakana = $v["item_value"];
				}
			}
		}
		return array("hiragana" => $hiragana, "katakana" => $katakana);
	}

	public static function save(int $itemId, string $hiragana, string $katakana){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		if(strlen($hiragana)){
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId(self::FIELD_ID . "_hiragana");
			$attr->setValue($hiragana);
			try{
				$dao->insert($attr);
			}catch(Exception $e){
				try{
					$dao->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}else{
			try{
				$dao->delete($itemId, self::FIELD_ID . "_hiragana");
			}catch(Exception $e){
				//
			}
		}

		if(strlen($katakana)){
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId(self::FIELD_ID . "_katakana");
			$attr->setValue($katakana);
			try{
				$dao->insert($attr);
			}catch(Exception $e){
				try{
					$dao->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}else{
			try{
				$dao->delete($itemId, self::FIELD_ID . "_katakana");
			}catch(Exception $e){
				//
			}
		}
	}
}
