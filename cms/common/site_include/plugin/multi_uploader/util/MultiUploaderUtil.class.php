<?php

class MultiUploaderUtil {

	const FIELD_ID = "multi_uploader";

	public static function save(int $entryId, string $path){
		$path = trim($path);
		if(!strlen($path)) return;

		$attr = self::_getAttr($entryId);
		$v = $attr->getValue();

		$pathes = (strlen($v)) ? soy2_unserialize($v) : array();
		$pathes[] = $path;

		$attr->setValue(soy2_serialize(array_unique($pathes)));	//画像が重複している場合は一つにする

		try{
			self::_dao()->insert($attr);
		}catch(Exception $e){
			try{
				self::_dao()->update($attr);
			}catch(Exception $e){
				//
			}
		}
	}

	public static function update(int $entryId, array $pathes){
		if(!count($pathes)){
			try{
				self::_dao()->delete($entryId, self::FIELD_ID);
			}catch(Exception $e){
				//
			}
		}else{
			$attr = self::_getAttr($entryId);
			$attr->setValue(soy2_serialize($pathes));

			try{
				self::_dao()->insert($attr);
			}catch(Exception $e){
				try{
					self::_dao()->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}

	}

	public static function getImagePathes(int $entryId){
		$v = self::_getAttr($entryId)->getValue();
		return (strlen($v)) ? soy2_unserialize($v) : array();
	}

	public static function updateAlt(int $entryId, string $hash, string $alt){
		$alt = trim($alt);
		if(!strlen($alt)){
			try{
				self::_dao()->delete($entryId, self::FIELD_ID . "_alt_" . $hash);
			}catch(Exception $e){
				//
			}
		}else{
			$attr = self::_getAttr($entryId, "alt_" . $hash);
			$attr->setValue($alt);

			try{
				self::_dao()->insert($attr);
			}catch(Exception $e){
				try{
					self::_dao()->update($attr);
				}catch(Exception $e){
					//
				}
			}
		}
	}

	public static function getAltList(int $entryId){
		try{
			$res = self::_dao()->executeQuery(
				"SELECT entry_field_id, entry_value FROM EntryAttribute WHERE entry_id = :entryId AND entry_field_id LIKE :fieldId",
				array(
					":entryId" => $entryId,
					":fieldId" => self::FIELD_ID . "_alt_%"
				)
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$alt = trim($v["entry_value"]);
			if(!strlen($alt)) continue;
			$hash = str_replace(self::FIELD_ID . "_alt_", "", $v["entry_field_id"]);
			$list[$hash] = $alt;
		}
		return $list;
	}

	private static function _getAttr(int $entryId, string $postfix=""){
		$fieldId = self::FIELD_ID;
		if(strlen($postfix)) $fieldId .= "_" . $postfix;
		try{
			return self::_dao()->get($entryId, $fieldId);
		}catch(Exception $e){
			$attr = new EntryAttribute();
			$attr->setEntryId($entryId);
			$attr->setFieldId($fieldId);
			return $attr;
		}
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}


	public static function path2Hash(string $filepath){
		//文字数は6文字にしておく
		return substr(md5($filepath), 0, 6);
	}
}
