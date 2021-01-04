<?php

class TopicLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_TopicDAO");
	}

	function get($notDisabledGroup=false){
		if($notDisabledGroup){
			return self::_dao()->getWithNotDisabledGroup();
		}else{
			try{
				return self::_dao()->get();
			}catch(Exception $e){
				return array();
			}
		}
	}

	function getById($topicId, $notDisabledGroup=false){
		if($notDisabledGroup){
			return self::_dao()->getByIdWithNotDisabledGroup($topicId);
		}else{
			return self::_getById($topicId);
		}
	}

	function getByGroupId($groupId, $notDisabledGroup=false){
		if($notDisabledGroup){
			return self::_dao()->getByGroupIdWithNotDisabledGroup($groupId);
		}else{
			try{
				return self::_dao()->getByGroupId($groupId);
			}catch(Exception $e){
				return array();
			}
		}

	}

	function insert($values){
		$dao = self::_dao();
		$topic = SOY2::cast("SOYBoard_Topic", $values);
		if(!strlen($topic->getLabel())) return null;
		try{
			return $dao->insert($topic);
		}catch(Exception $e){
			return null;
		}
	}

	private function _getById($topicId){
		try{
			return self::_dao()->getById($topicId);
		}catch(Exception $e){
			return new SOYBoard_Topic();
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYBoard_TopicDAO");
		return $dao;
	}
}
