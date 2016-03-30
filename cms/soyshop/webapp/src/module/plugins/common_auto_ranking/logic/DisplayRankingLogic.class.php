<?php

class DisplayRankingLogic extends SOY2LogicBase{
	
	private $rankingDao;
	private $ranking;
	
	function DisplayRankingLogic(){
		SOY2::imports("module.plugins.common_auto_ranking.domain.*");
		$this->rankingDao = SOY2DAOFactory::create("SOYShop_AutoRankingDAO");	
	}
	
	function getLatestCalcDate(){
		$this->rankingDao->setLimit(1);
		try{
			$obj = $this->rankingDao->get();
		}catch(Exception $e){
			return null;
		}
		
		if(!isset($obj[0])) return null;
		
		return $obj[0]->getCreateDate();
	}
	
	function getItems(){
		$ranking = $this->getRanking();
		
		if(!count($ranking)) return array();
		
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		
		//順位を壊さないように丁寧に一つずつ取得
		$items = array();
		foreach($ranking as $itemId){
			try{
				$items[] = $itemDao->getById($itemId);
			}catch(Exception $e){
				continue;
			}
		}
		
		return $items;
	}
	
	function getRanking(){
		if(!$this->ranking){
			$this->ranking = $this->rankingDao->getRankingList();
		}
		return $this->ranking;
	}
}
?>