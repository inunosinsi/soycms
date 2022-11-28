<?php

class AutoCompleteDictionaryLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.auto_completion_item_name.util.AutoCompletionUtil");
		SOY2::import("module.plugins.auto_completion_item_name.domain.SOYShop_AutoComplete_DictionaryDAO");
		SOY2::import("module.plugins.auto_completion_item_name.domain.SOYShop_AutoComplete_DictionaryCategoryDAO");
	}

	function getReadingsByItemId(int $itemId){
		$dic = self::_getByItemId($itemId);
		return array(
			AutoCompletionUtil::TYPE_HIRAGANA => $dic->getHiragana(),
			AutoCompletionUtil::TYPE_KATAKANA => $dic->getKatakana(),
			AutoCompletionUtil::TYPE_OTHER => $dic->getOther()
		);
	}

	function saveItemReadings(int $itemId, array $arr){
		$isValue = false;
		foreach(AutoCompletionUtil::getItemTypes() as $t => $label){
			$arr[$t] = trim($arr[$t]);
			if(!$isValue && strlen($arr[$t])) $isValue = true;
		}

		try{
			self::_dao()->deleteByItemId($itemId);
		}catch(Exception $e){
			//
		}

		if($isValue){
			$dic = self::_getByItemId($itemId);
			$dic->setHiragana($arr[AutoCompletionUtil::TYPE_HIRAGANA]);
			$dic->setKatakana($arr[AutoCompletionUtil::TYPE_KATAKANA]);
			$dic->setOther($arr[AutoCompletionUtil::TYPE_OTHER]);
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

	function getReadingsByCategoryId(int $categoryId){
		$dic = self::_getByCategoryId($categoryId);
		return array(
			AutoCompletionUtil::TYPE_HIRAGANA => $dic->getHiragana(),
			AutoCompletionUtil::TYPE_KATAKANA => $dic->getKatakana(),
			AutoCompletionUtil::TYPE_OTHER => $dic->getOther()
		);
	}

	function saveCategoryReadings(int $categoryId, array $arr){
		$isValue = false;
		foreach(AutoCompletionUtil::getItemTypes() as $t => $label){
			$arr[$t] = trim($arr[$t]);
			if(!$isValue && strlen($arr[$t])) $isValue = true;
		}

		try{
			self::_categoryDicDao()->deleteByCategoryId($categoryId);
		}catch(Exception $e){
			//
		}

		if($isValue){
			$dic = self::_getBycategoryId($categoryId);
			$dic->setHiragana($arr[AutoCompletionUtil::TYPE_HIRAGANA]);
			$dic->setKatakana($arr[AutoCompletionUtil::TYPE_KATAKANA]);
			$dic->setOther($arr[AutoCompletionUtil::TYPE_OTHER]);
			try{
				self::_categoryDicDao()->insert($dic);
			}catch(Exception $e){
				try{
					self::_categoryDicDao()->update($dic);
				}catch(Exception $e){
					//
				}
			}
		}
	}

	private function _getByCategoryId(int $categoryId){
		try{
			return self::_categoryDicDao()->getByCategoryId($categoryId);
		}catch(Exception $e){
			$dic = new SOYShop_AutoComplete_DictionaryCategory();
			$dic->setCategoryId($categoryId);
			return $dic;
		}
	}

	private function _categoryDicDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_AutoComplete_DictionaryCategoryDAO");
		return $dao;
	}
}
