<?php

class OutputLabeledEntriesUtil {

	const FIELD_ID = "output_labeled_entries";
	const DISPLAY_COUNT = 5;
	const SORT_ASC = 0;
	const SORT_DESC = 1;

	public static function save($entryId, $fieldId, $value=""){
		if(is_null($value) || !strlen($value)){	//削除
			try{
				self::_dao()->delete($entryId, $fieldId);
			}catch(Exception $e){
				//
			}
		}else{
			$attr = self::_get($entryId, $fieldId);
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
		}
	}

	public static function getSelectedLabelId($entryId, $postfix){
		return (int)self::_get($entryId, self::FIELD_ID . "_" . $postfix)->getValue();
	}

	public static function getDisplayCount($entryId, $postfix){
		$cnfs = self::_getConfigEachEntries($entryId, $postfix);
		return (isset($cnfs["displayCount"]) && is_numeric($cnfs["displayCount"])) ? (int)$cnfs["displayCount"] : self::DISPLAY_COUNT;
	}

	public static function getDisplaySort($entryId, $postfix){
		$cnfs = self::_getConfigEachEntries($entryId, $postfix);
		return (isset($cnfs["sort"]) && is_numeric($cnfs["sort"])) ? (int)$cnfs["sort"] : self::SORT_ASC;
	}

	public static function getLabels(){
		static $list;
		if(is_null($list)) {
			$list = array();
			try{
				$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
			}catch(Exception $e){
				$labels = array();
			}

			if(count($labels)){
				foreach($labels as $label){
					$list[$label->getId()] = $label->getCaption();
				}
			}
		}
		return $list;
	}

	public static function getSortTypes(){
		return array(
			self::SORT_ASC => "昇順",
			self::SORT_DESC => "降順"
		);
	}

	private static function _get($entryId, $fieldId){
		try{
			return self::_dao()->get($entryId, $fieldId);
		}catch(Exception $e){
			$attr = new EntryAttribute();
			$attr->setEntryId($entryId);
			$attr->setFieldId($fieldId);
			return $attr;
		}
	}

	private static function _getConfigEachEntries($entryId, $postfix){
		static $cnfs;
		if(is_null($cnfs)) $cnfs = array();
		$hash = substr(md5($entryId . $postfix), 0, 3);
		if(!isset($cnfs[$hash])){
			$v = trim(self::_get($entryId, self::FIELD_ID . "_" . $postfix . "_config")->getValue());
			$cnfs[$hash] = (strlen($v)) ? soy2_unserialize($v) : array();
		}
		return $cnfs[$hash];
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}
}
