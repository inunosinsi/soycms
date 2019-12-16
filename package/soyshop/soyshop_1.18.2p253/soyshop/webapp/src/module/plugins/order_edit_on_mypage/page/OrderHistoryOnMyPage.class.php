<?php

class OrderHistoryOnMyPage extends WebPage{

	private $configObj;

	function __construct(){
		parent::__construct();

		SOY2::import("module.plugins.order_edit_on_mypage.component.HistoryOnMyPageListComponent");
		$this->createAdd("history_list", "HistoryOnMyPageListComponent", array(
			"list" => self::getHistories()
		));
	}

	private function getHistories(){
		$historyDao = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");
		try{
			$results = $historyDao->executeQuery("SELECT * FROM soyshop_order_state_history WHERE author LIKE '顧客:%' AND (content LIKE '注文合計%' OR content LIKE '注文番号%') ORDER BY order_date DESC LIMIT 15");
		}catch(Exception $e){
			var_dump($e);
			return array();
		}
		if(!count($results)) return array();

		$histories = array();
		foreach($results as $res){
			$histories[] = $historyDao->getObject($res);
		}

		return $histories;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
