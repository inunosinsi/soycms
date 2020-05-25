<?php

class DepositManagerUtil{

	function getSubjectList($isArray=false){
		$dao = self::_dao();
		$dao->setOrder("display_order ASC");
		try{
			$subjects = $dao->get();
		}catch(Exception $e){
			return array();
		}
		if(!count($subjects)) return array();

		//オブジェクトとして返す
		if(!$isArray) return $subjects;

		$list = array();
		foreach($subjects as $subject){
			$list[$subject->getId()] = $subject->getSubject();
		}
		return $list;
	}

	function addSubject($subject){
		$dao = self::_dao();
		$obj = new SOYShop_DepositManagerSubject();
		$obj->setSubject($subject);
		try{
			$dao->insert($obj);
			return true;
		}catch(Exception $e){
			return false;
		}
	}

	function changeDisplayOrder($sorts){
		if(!is_array($sorts) || !count($sorts)) return;
		$dao = self::_dao();
		foreach($sorts as $subjectId => $displayOrder){
			if(!strlen($displayOrder) || !is_numeric($displayOrder)) $displayOrder = SOYShop_DepositManagerSubject::DISPLAY_ORDER_LIMIT;
			try{
				$obj = $dao->getById($subjectId);
				$obj->setDisplayOrder($displayOrder);
				$dao->update($obj);
			}catch(Exception $e){
				//
			}
		}
	}

	function removeSubjectById($subjectId){
		try{
			self::_dao()->deleteById($subjectId);
			return true;
		}catch(Exception $e){
			return false;
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.deposit_manager.domain.SOYShop_DepositManagerSubjectDAO");
			$dao = SOY2DAOFactory::create("SOYShop_DepositManagerSubjectDAO");
		}
		return $dao;
	}

	public static function getParameter($key){
		$session = SOY2ActionSession::getUserSession();
		if(isset($_GET[$key])){
			$session->setAttribute("deposit_manager_search:" . $key, $_GET[$key]);
			$params = $_GET[$key];
		}else if(isset($_GET["reset"])){
			$session->setAttribute("deposit_manager_search:" . $key, array());
			$params = array();
		}else{
			$params = $session->getAttribute("deposit_manager_search:" . $key);
			if(is_null($params)) $params = array();
		}

		return $params;
	}
}
