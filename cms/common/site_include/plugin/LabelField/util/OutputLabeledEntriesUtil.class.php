<?php

class OutputLabeledEntriesUtil {

	const FIELD_ID = "output_labeled_entries";
	const DISPLAY_COUNT = 5;
	const SORT_ASC = 0;
	const SORT_DESC = 1;

	public static function save(int $entryId, string $fieldId, string $value=""){
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

	/**
	 * @param int, string
	 * @return int
	 */
	public static function getSelectedLabelId(int $entryId, string $postfix){
		return soycms_get_entry_attribute_value($entryId, self::FIELD_ID . "_" . $postfix, "int");
	}

	/**
	 * @param int, string
	 * @return int
	 */
	public static function getDisplayCount(int $entryId, string $postfix){
		$cnfs = self::_getConfigEachEntries($entryId, $postfix);
		return (isset($cnfs["displayCount"]) && is_numeric($cnfs["displayCount"])) ? (int)$cnfs["displayCount"] : self::DISPLAY_COUNT;
	}

	/**
	 * @param int, string
	 * @return int
	 */
	public static function getDisplaySort(int $entryId, string $postfix){
		$cnfs = self::_getConfigEachEntries($entryId, $postfix);
		return (isset($cnfs["sort"]) && is_numeric($cnfs["sort"])) ? (int)$cnfs["sort"] : self::SORT_ASC;
	}

	/**
	 * @return array
	 */
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

	/**
	 * @param int, string
	 * @return EntryEttribute
	 */
	private static function _get(int $entryId, string $fieldId){
		return soycms_get_entry_attribute_object($entryId, $fieldId);
	}

	/**
	 * @param int, string
	 * @return string
	 */
	private static function _getConfigEachEntries(int $entryId, string $postfix){
		static $cnfs;
		if(is_null($cnfs)) $cnfs = array();
		$hash = substr(md5($entryId . $postfix), 0, 3);
		if(!isset($cnfs[$hash])){
			$v = trim(soycms_get_entry_attribute_value($entryId, self::FIELD_ID . "_" . $postfix . "_config", "string"));
			$cnfs[$hash] = (is_string($v)) ? soy2_unserialize($v) : array();
		}
		return $cnfs[$hash];
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}
}
