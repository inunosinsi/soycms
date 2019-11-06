<?php

class SelectedEntriesBlockUtil {

	const FIELD_ID = "selected_entries_block";

	public static function isCheck($entryId){
		if(is_null($entryId)) return false;

		try{
			$v = (int)SOY2DAOFactory::create("cms.EntryAttributeDAO")->get($entryId, self::FIELD_ID)->getValue();
			return ($v > 0);
		}catch(Exception $e){
			return 0;
		}
	}

	public static function save($entryId, $isCheck){
		$dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		if($isCheck){	//登録
			$obj = new EntryAttribute();
			$obj->setEntryId($entryId);
			$obj->setFieldId(self::FIELD_ID);
			$obj->setValue(1);
			try{
				$dao->insert($obj);
			}catch(Exception $e){
				//
			}
		}else{	//削除
			try{
				$dao->delete($entryId, self::FIELD_ID);
			}catch(Exception $e){
				//
			}
		}
	}
}
