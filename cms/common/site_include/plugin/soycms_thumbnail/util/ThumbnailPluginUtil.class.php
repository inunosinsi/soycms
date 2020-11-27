<?php

class ThumbnailPluginUtil {

	public static function getThumbnailObjectsByEntryId($entryId){
		static $objects;
		if(is_null($objects)) $objects = array();
		$entryId = (is_numeric($entryId)) ? (int)$entryId : 0;
		if(!isset($objects[$entryId])){
			$dao = self::_dao();
			try{
				$thumbs = $dao->executeQuery("SELECT entry_field_id, entry_value FROM EntryAttribute WHERE entry_id = :entryId AND entry_field_id LIKE 'soycms_thumbnail_plugin_%' AND entry_field_id NOT LIKE '%config'", array(":entryId" => $entryId));
				if(!count($thumbs)) return array();
			}catch(Exception $e){
				$thumbs = array();
			}

			if(count($thumbs)){
				foreach($thumbs as $thumb){
					$objects[$entryId][$thumb["entry_field_id"]] = $dao->getObject($thumb);
				}
			}
		}
		return $objects[$entryId];
	}

	private static function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		return $dao;
	}
}
