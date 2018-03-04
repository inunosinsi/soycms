<?php

class SearchSlipNumberLogic extends SOY2LogicBase {

	private $slipDao;
	private $limit;

	function __construct(){
		SOY2::import("module.plugins.slip_number.domain.SOYShop_SlipNumberDAO");
		$this->slipDao = SOY2DAOFactory::create("SOYShop_SlipNumberDAO");
	}

	function get(){
		/**
		 * @ToDo 検索条件を追加したい
		 */
		$this->slipDao->setLimit($this->limit);
		try{
			return $this->slipDao->getByIsDelivery(SOYShop_SlipNumber::NO_DELIVERY);
		}catch(Exception $e){
			return array();
		}
	}

	function setLimit($limit){
		$this->limit = $limit;
	}
}
