<?php

class OSMLogic extends SOY2LogicBase {

	/**
	 * @param int
	 * @param array
	 */
	function save(int $entryId, array $posts){
		foreach($posts as $idx => $_p){
			if($idx == "entry_id") continue;
			$posts[$idx] = trim(trim(trim((string)$posts[$idx], "/"), ","));
			if(!strlen($posts[$idx])) $posts[$idx] = null;
		}

		// entry_id以外の値がすべてnullだった場合は削除
		$isDelete = true;
		foreach($posts as $idx => $_post){
			if(!is_null($_post)) {
				$isDelete = false;
				break;
			}
		}

		if($isDelete){
			try{
				self::_dao()->deleteByEntryId($entryId);
			}catch(Exception $e){
				//
			}
		}else{
			$obj = SOY2::cast(self::_get($entryId), $posts);

			try{
				self::_dao()->insert($obj);
			}catch(Exception $e){
				try{
					self::_dao()->update($obj);
				}catch(Exception $e){
					
				}
			}
		}
	}

	/**
	 * @param int
	 * @return OpenStreetMap
	 */
	function getByEntryId(int $entryId){
		return self::_get($entryId);
	}

	function registerOSMValuesByEntryIds(array $entryIds){
		if(!count($entryIds)) return;
		try{
			$res = self::_dao()->executeQuery(
				"SELECT * FROM OpenStreetMap WHERE entry_id IN (".implode(",", $entryIds).")"
			);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return;

		SOY2::import("site_include.plugin.openstreetmap.util.OSMUtil");
		if(!isset($GLOBALS[OSMUtil::OSM_KEY])) $GLOBALS[OSMUtil::OSM_KEY] = array();
		
		foreach($res as $v){
			$GLOBALS[OSMUtil::OSM_KEY][(int)$v["entry_id"]] = $v;
		}

		return;
	}

	/**
	 * @param int
	 * @return OpenStreetMap
	 */
	private function _get(int $entryId){
		try{
			return self::_dao()->getByEntryId($entryId);
		}catch(Exception $e){
			$obj = new OpenStreetMap();
			$obj->setEntryId($entryId);
			return $obj;
		}
	}

	private function _dao(){
		static $d;
		if(is_null($d)){
			SOY2::import("site_include.plugin.openstreetmap.domain.OpenStreetMapDAO");
			$d = SOY2DAOFactory::create("OpenStreetMapDAO");
		}
		return $d;
	}
}
