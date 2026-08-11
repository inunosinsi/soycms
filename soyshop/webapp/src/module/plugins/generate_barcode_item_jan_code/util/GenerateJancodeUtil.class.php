<?php

class GenerateJancodeUtil {

	const FIELD_ID = "jancode_13";

	public static function getJancodeDirectory(string $itemCode){
		return self::_getJancodeDirectory($itemCode);
	}

	public static function getJancodeImagePath(string $filename, string $itemCode){
		//画像ファイルが存在しているか？を調べてからパスを返す
		if(!file_exists(self::_getJancodeDirectory($itemCode) . $filename)) return "";
		return "/" . SOYSHOP_ID . "/files/" . $itemCode . "/jancode/" . $filename;
	}

	private static function _getJancodeDirectory(string $itemCode){
		$dir = SOYSHOP_SITE_DIRECTORY . "files/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= $itemCode . "/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "jancode/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}

	public static function getJancode(int $itemId){
		return soyshop_get_item_attribute_value($itemId, self::FIELD_ID, "string");
	}

	public static function saveJancode(string $jancode, int $itemId){
		$attr = soyshop_get_item_attribute_object($itemId, self::FIELD_ID);

		//jancodeを変更する場合は画像を削除
		if(is_string($attr->getValue()) && strlen($attr->getValue()) && $attr->getValue() != $jancode){
			$jpg = self::_getJancodeDirectory(soyshop_get_item_object($itemId)->getCode()) . $attr->getValue() . ".jpg";
			if(file_exists($jpg)) unlink($jpg);
		}

		$attr->setValue($jancode);
		soyshop_save_item_attribute_object($attr);
	}
}
