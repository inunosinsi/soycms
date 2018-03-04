<?php

class SearchReturnsSlipNumberLogic extends SOY2LogicBase {

	private $slipDao;
	private $limit;

	function __construct(){
		SOY2::import("module.plugins.returns_slip_number.domain.SOYShop_ReturnsSlipNumberDAO");
		$this->slipDao = SOY2DAOFactory::create("SOYShop_ReturnsSlipNumberDAO");
	}

	function get(){
		/**
		 * @ToDo 検索条件を追加したい
		 */
		$this->slipDao->setLimit($this->limit);
		try{
			return $this->slipDao->getByIsReturn(SOYShop_ReturnsSlipNumber::NO_RETURN);
		}catch(Exception $e){
			return array();
		}
	}

	function setLimit($limit){
		$this->limit = $limit;
	}
}
