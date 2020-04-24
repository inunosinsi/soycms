<?php

class FixedFormModuleUtil {

	const PLUGIN_ID = "fixed_form_module";

	public static function saveConfig($values){
		SOYShop_DataSets::put(self::PLUGIN_ID . ".config", $values);
	}

	public static function getConfig(){
		return SOYShop_DataSets::get(self::PLUGIN_ID . ".config", array());
	}

	public static function getAllModuleList(){
		$res = array();
		//すべてのモジュールを読み込む
		$dir = SOYSHOP_SITE_DIRECTORY . ".module/";

		$files = soy2_scanfiles($dir);

		foreach($files as $file){
			if(!preg_match('/\.php$/', $file)) continue;

			$moduleId = preg_replace('/^.*\.module\//', "", $file);

			//一個目の/より前はカテゴリ
			$moduleId = preg_replace('/\.php$/', "", $moduleId);
			$moduleId = str_replace("/", ".", $moduleId);
			$name = $moduleId;

			//ini
			$iniFilePath = preg_replace('/\.php$/', ".ini", $file);
			if(file_exists($iniFilePath)){
				$array = @parse_ini_file($iniFilePath);
				if(isset($array["name"])) $name = $array["name"];
			}

			$res[$moduleId] = $name;
		}

		return $res;
	}

	public static function getAttr($itemId){
		return self::_getAttr($itemId);
	}

	public static function save($itemId, $value){
		if(strlen($value)){
			$attr = self::_getAttr($itemId);
			$attr->setValue($value);
			try{
				self::_dao()->insert($attr);
			}catch(Exception $e){
				try{
					self::_dao()->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}else{
			self::_dao()->delete($itemId, self::PLUGIN_ID);
		}
	}

	private static function _getAttr($itemId){
		try{
			return self::_dao()->get($itemId, self::PLUGIN_ID);
		}catch(Exception $e){
			$attr = new SOYShop_ItemAttribute();
			$attr->setItemId($itemId);
			$attr->setFieldId(self::PLUGIN_ID);
			return $attr;
		}
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		return $dao;
	}
}
