<?php

class AutoCompleteDictionaryLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.auto_completion_item_name.domain.SOYShop_AutoComplete_DictionaryDAO");
	}

	function getReadingsByItemId(int $itemId){
		$dic = self::_getByItemId($itemId);
		return array("hiragana" => $dic->getHiragana(), "katakana" => $dic->getKatakana());
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
			$dic = self::_getByItemId($itemId);
			$dic->setHiragana($hiragana);
			$dic->setKatakana($katakana);
			try{
				self::_dao()->insert($dic);
			}catch(Exception $e){
				try{
					self::_dao()->update($dic);
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
			$dic = new SOYShop_AutoComplete_Dictionary();
			$dic->setItemId($itemId);
			return $dic;
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_AutoComplete_DictionaryDAO");
		return $dao;
	}
}
