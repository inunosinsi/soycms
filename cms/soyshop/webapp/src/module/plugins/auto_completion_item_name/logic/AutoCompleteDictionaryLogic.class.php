<?php

class AutoCompleteDictionaryLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.auto_completion_item_name.domain.SOYShop_AutoComplete_DictionaryDAO");
	}

	function getReadingsByItemId(int $itemId){
		$obj = self::_getByItemId($itemId);
		return array("hiragana" => $obj->getHiragana(), "katakana" => $obj->getKatakana());
	}

	function save(int $itemId, string $hiragana, string $katakana){
		$hiragana = trim($hiragana);
		$katakana = trim($katakana);

		if(!strlen($hiragana) && !strlen($katakana)){
			try{
				self::_dao()->deleteByItemId($itemId);
			}catch(Exception $e){
				//
			}
		}else{
			$obj = self::_getByItemId($itemId);
			$obj->setHiragana($hiragana);
			$obj->setKatakana($katakana);
			try{
				self::_dao()->insert($obj);
			}catch(Exception $e){
				try{
					self::_dao()->update($obj);
				}catch(Exception $e){
					//
				}
			}
		}
	}

	private function _getByItemId(int $itemId){
		try{
			return self::_dao()->getByItemId($itemId);
		}catch(Exception $e){
			$obj = new SOYShop_AutoComplete_Dictionary();
			$obj->setItemId($itemId);
			return $obj;
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_AutoComplete_DictionaryDAO");
		return $dao;
	}
}
