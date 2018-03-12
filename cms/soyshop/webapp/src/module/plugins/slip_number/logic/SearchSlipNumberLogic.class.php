<?php

class SearchSlipNumberLogic extends SOY2LogicBase {

	private $slipDao;
	private $where = array();
	private $binds = array();
	private $limit = 15;

	function __construct(){
		SOY2::import("domain.order.SOYShop_Order");
		SOY2::import("module.plugins.slip_number.domain.SOYShop_SlipNumberDAO");
		$this->slipDao = SOY2DAOFactory::create("SOYShop_SlipNumberDAO");
	}

	function get(){
		$sql = "SELECT slip.* FROM soyshop_slip_number slip ".
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
		$sql = "SELECT slip.slip_number FROM soyshop_slip_number slip ".
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
			$list[] = trim($v["slip_number"]);
		}
		return $list;
	}

	function getTotal(){
		$sql = "SELECT COUNT(slip.id) as count FROM soyshop_slip_number slip ".
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
		//キャンセル、仮登録と発送済みを除く
		$where = " WHERE o.order_status NOT IN (" . SOYShop_Order::ORDER_STATUS_CANCELED . ", " . SOYShop_Order::ORDER_STATUS_INTERIM . "," . SOYShop_Order::ORDER_STATUS_SENDED . ")";

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
					case "is_delivery":
						$this->where[":is_delivery"] = "slip.is_delivery IN (" . implode(",", $cnd) . ")";
						break;
				}
			}
		}

		if(!isset($conditions["is_delivery"])){
			$this->where[":is_delivery"] = "slip.is_delivery = " . SOYShop_SlipNumber::NO_DELIVERY;
		}
	}

	function setLimit($limit){
		$this->limit = $limit;
	}
}
