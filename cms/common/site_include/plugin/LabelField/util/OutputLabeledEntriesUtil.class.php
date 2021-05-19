<?php

class OutputLabeledEntriesUtil {

	const FIELD_ID = "output_labeled_entries";

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

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}
}
