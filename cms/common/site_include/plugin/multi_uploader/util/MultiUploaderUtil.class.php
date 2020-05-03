<?php

class MultiUploaderUtil {

	const FIELD_ID = "multi_uploader";

	public static function save($entryId, $path){
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

	public static function update($entryId, $pathes){
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

	public static function getImagePathes($entryId){
		$v = self::_getAttr($entryId)->getValue();
		return (strlen($v)) ? soy2_unserialize($v) : array();
	}

	private static function _getAttr($entryId){
		try{
			return self::_dao()->get($entryId, self::FIELD_ID);
		}catch(Exception $e){
			$attr = new EntryAttribute();
			$attr->setEntryId($entryId);
			$attr->setFieldId(self::FIELD_ID);
			return $attr;
		}
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}


	public static function path2Hash($filepath){
		//文字数は6文字にしておく
		return substr(md5($filepath), 0, 6);
	}
}
