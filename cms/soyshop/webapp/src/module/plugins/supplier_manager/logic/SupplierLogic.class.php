<?php

class SupplierLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.supplier_manager.domain.SOYShop_SupplierDAO");
	}

	function getById($id){
		try{
			return self::_dao()->getById($id);
		}catch(Exception $e){
			return new SOYShop_Supplier();
		}
	}

	function save(SOYShop_Supplier $supplier){
		if(is_null($supplier->getId())){
			try{
				return self::_dao()->insert($supplier);
			}catch(Exception $e){
				//
			}
		}else{
			try{
				self::_dao()->update($supplier);
				return $supplier->getId();
			}catch(Exception $e){
				//
			}
		}
		return null;
	}

	function getSupplierList(){
		$suppliers = self::_get();
		if(!count($suppliers)) return array();

		$list = array();
		foreach($suppliers as $supplier){
			$list[$supplier->getId()] = $supplier->getName();
		}
		return $list;
	}


	function getPaidTotalBySupplierId($supplierId){
		if(!is_numeric($supplierId) || (int)$supplierId === 0) return 0;
		SOY2::import("module.plugins.withdrawals_manager.domain.SOYShop_WithdrawalsDAO");
		return SOY2DAOFactory::create("SOYShop_WithdrawalsDAO")->getTotalPriceBySupplierId($supplierId);
	}

	private function _get(){
		try{
			return self::_dao()->get();
		}catch(Exception $e){
			return array();
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_SupplierDAO");
		return $dao;
	}
}
