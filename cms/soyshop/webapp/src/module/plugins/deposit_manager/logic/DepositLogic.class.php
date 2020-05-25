<?php

class DepositLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.deposit_manager.domain.SOYShop_DepositManagerDepositDAO");
	}

	function getById($id){
		return self::_getById($id);
	}

	function save($id, $posts){
		$posts["depositDate"] = (isset($posts["depositDate"])) ? soyshop_convert_timestamp($posts["depositDate"]) : null;
		$deposit = SOY2::cast(self::_getById($id), $posts);

		try{
			$id = self::_dao()->insert($deposit);
		}catch(Exception $e){
			try{
				self::_dao()->update($deposit);
			}catch(Exception $e){
				$id = null;
			}
		}
		return $id;
	}

	private function _getById($id){
		try{
			return self::_dao()->getById($id);
		}catch(Exception $e){
			return new SOYShop_DepositManagerDeposit();
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_DepositManagerDepositDAO");
		return $dao;
	}
}
