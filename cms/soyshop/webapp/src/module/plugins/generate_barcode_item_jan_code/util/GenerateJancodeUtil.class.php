<?php

class GenerateJancodeUtil {

	const FIELD_ID = "jancode_13";

	public static function getJancodeDirectory($itemCode){
		return self::_getJancodeDirectory($itemCode);
	}

	public static function getJancodeImagePath($filename, $itemCode){
		//画像ファイルが存在しているか？を調べてからパスを返す
		if(file_exists(self::_getJancodeDirectory($itemCode) . $filename)){
			return "/" . SOYSHOP_ID . "/files/" . $itemCode . "/jancode/" . $filename;
		}else{
			return null;
		}
	}

	private static function _getJancodeDirectory($itemCode){
		$dir = SOYSHOP_SITE_DIRECTORY . "files/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= $itemCode . "/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "jancode/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}

	public static function getJancode($itemId){
		return self::_get($itemId)->getValue();
	}

	public static function saveJancode($jancode, $itemId){
		$attr = self::_get($itemId);

		//jancodeを変更する場合は画像を削除
		if(strlen($attr->getValue()) && $attr->getValue() != $jancode){
			$dir = self::_getJancodeDirectory(soyshop_get_item_object($itemId)->getCode());
			$jpg = $dir . $attr->getValue() . ".jpg";
			if(file_exists($jpg)){
				unlink($jpg);
			}
		}

		$attr->setValue($jancode);

		$dao = self::dao();
		try{
			$dao->insert($attr);
		}catch(Exception $e){
			try{
				$dao->update($attr);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}

	private static function _get($itemId){
		$dao = self::dao();
		try{
			$attr = $dao->get($itemId, self::FIELD_ID);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId(self::FIELD_ID);
		}
		return $attr;
	}

	private static function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}
}
