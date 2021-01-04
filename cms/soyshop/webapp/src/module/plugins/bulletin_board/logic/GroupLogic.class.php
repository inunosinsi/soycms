<?php

class GroupLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_GroupDAO");
	}

	function get(){
		try{
			return self::_dao()->get();
		}catch(Exception $e){
			return array();
		}
	}

	function getById($groupId){
		return self::_getById($groupId);
	}

	function insert($values){
		$dao = self::_dao();
		$group = SOY2::cast("SOYBoard_Group", $values);
		if(!strlen($group->getName())) return null;
		try{
			return $dao->insert($group);
		}catch(Exception $e){
			return null;
		}
	}

	function setDisplayOrder($groupId, $displayOrder){
		$group = self::_getById($groupId);
		$displayOrder = (is_numeric($displayOrder) && $displayOrder < SOYBoard_Group::UPPER_LIMIT) ? (int)$displayOrder : SOYBoard_Group::UPPER_LIMIT;
		$group->setDisplayOrder($displayOrder);
		try{
			self::_dao()->update($group);
		}catch(Exception $e){
			//
		}
	}

	function delete($groupId){
		$group = self::_getById($groupId);
		$group->setIsDisabled(SOYBoard_Group::IS_DISABLED);
		$i = 0;
		for(;;){	//リネーム ***_delete_i
			$change = $group->getName() . "_delete_" . $i++;
			try{
				$old = self::_dao()->getByName($change);
			}catch(Exception $e){
				//
				$group->setName($change);
				break;
			}
		}
		try{
			self::_dao()->update($group);
		}catch(Exception $e){
			//
		}
	}

	private function _getById($groupId){
		try{
			return self::_dao()->getById($groupId);
		}catch(Exception $e){
			return new SOYBoard_Group();
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYBoard_GroupDAO");
		return $dao;
	}
}
