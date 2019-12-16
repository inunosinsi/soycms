<?php

class SearchReturnsSlipNumberLogic extends SOY2LogicBase {

	private $slipDao;
	private $where = array();
	private $binds = array();
	private $limit = 15;

	function __construct(){
		SOY2::import("domain.order.SOYShop_Order");
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
		SOY2::import("module.plugins.returns_slip_number.domain.SOYShop_ReturnsSlipNumberDAO");
		$this->slipDao = SOY2DAOFactory::create("SOYShop_ReturnsSlipNumberDAO");
	}

	function get(){
		$sql = "SELECT slip.* FROM soyshop_returns_slip_number slip ".
				"INNER JOIN soyshop_order o ".
				"ON slip.order_id = o.id ";
		$sql .= self::buildWhere();
		$sql .= " LIMIT " . $this->limit;

		try{
			$results = $this->slipDao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			return array();
		}

		if(!count($results)) return array();

		$list = array();
		foreach($results as $v){
			$list[] = $this->slipDao->getObject($v);
		}
		return $list;
	}

	function getOnlySlipNumbers(){
		$sql = "SELECT slip.slip_number FROM soyshop_returns_slip_number slip ".
				"INNER JOIN soyshop_order o ".
				"ON slip.order_id = o.id ";
		$sql .= self::buildWhere();
		$sql .= " LIMIT " . $this->limit;

		try{
		 	$results = $this->slipDao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			return array();
		}

		if(!count($results)) return array();

		$list = array();
		foreach($results as $v){
			if(!isset($v["slip_number"])) continue;
			$list[] = trim($v["slip_number"]) . ",,,0";
		}
		return $list;
	}

	function getTotal(){
		$sql = "SELECT COUNT(slip.id) as count FROM soyshop_returns_slip_number slip ".
				"INNER JOIN soyshop_order o ".
				"ON slip.order_id = o.id ";
		$sql .= self::buildWhere();

		try{
			$results = $this->slipDao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			return 0;
		}

		return (isset($results[0]["count"])) ? (int)$results[0]["count"] : 0;
	}

	private function buildWhere(){
		//キャンセル、仮登録、返却済み(21)は除く
		$where = " WHERE o.order_status NOT IN (" . SOYShop_Order::ORDER_STATUS_CANCELED . ", " . SOYShop_Order::ORDER_STATUS_INTERIM . "," . ReturnsSlipNumberUtil::STATUS_CODE . ")";

		if(count($this->where)){
			foreach($this->where as $key => $w){
	            if(!strlen($w)) continue;
	            $where .= " AND " . $w;
	        }
		}
        return $where;
	}

	function setCondition($conditions){

		if(is_array($conditions) && count($conditions)){
			foreach($conditions as $key => $cnd){
				switch($key){
					case "is_return":
						$this->where[":is_return"] = "slip.is_return IN (" . implode(",", $cnd) . ")";
						break;
				}
			}
		}

		if(!isset($conditions["is_return"])){
			$this->where[":is_return"] = "slip.is_return = " . SOYShop_ReturnsSlipNumber::NO_RETURN;
		}
	}

	function setLimit($limit){
		$this->limit = $limit;
	}
}
