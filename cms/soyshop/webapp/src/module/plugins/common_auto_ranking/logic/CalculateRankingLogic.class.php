<?php

class CalculateRankingLogic extends SOY2LogicBase{
	
	private $rankingDao;
	
	function CalculateRankingLogic(){
		SOY2::import("module.plugins.common_auto_ranking.util.AutoRankingUtil");
		SOY2::imports("module.plugins.common_auto_ranking.domain.*");
		$this->rankingDao = SOY2DAOFactory::create("SOYShop_AutoRankingDAO");
	}
	
	//ランキングの集計を行う
	function execute(){
		$config = AutoRankingUtil::getConfig();
		$start = time() - (int)$config["period"] * 24 * 60 * 60;
		$end = time();
		$limit = (int)$config["count"];
		
		$itemIds = $this->rankingDao->getRanking($start, $end, $limit);
		
		//商品IDを取得できなかったらfalseを返す
		if(count($itemIds) === 0) return false;
		
		$obj = new SOYShop_AutoRanking();
		$obj->setContent(implode(",", $itemIds));
		$obj->setStartDate($start);
		$obj->setCreateDate($end);
		
		try{
			$this->rankingDao->insert($obj);
			$res = "集計が終わりました。このタブを閉じてください。";
		}catch(Exception $e){
			$res = $e;
		}
		
		return $res;
	}
}
?>