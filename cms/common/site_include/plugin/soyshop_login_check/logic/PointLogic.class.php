<?php

class PointLogic extends SOY2LogicBase{
	
	private $siteId;
	private $point;
	private $entry;
			
	function PointLogic(){}
	
	function addPoint(){
				
		$old = SOYShopUtil::switchShopMode($this->siteId);
		
		//データベースがあるかを調べる
		if($this->checkPointPluginInstall()){
			$userId = $this->getUserId();
			$pointLogic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");

			//既にコメントを行い、ポイントを付与されているかを調べる
			if($this->checkPointHistory($userId)){
				$pointLogic->insert($this->point, $this->getHistoryMessage(), $userId);
			}
			
		}
		
		SOYShopUtil::resetShopMode($old);
	}
	
	//ポイントプラグインがインストールされているかを調べる
	function checkPointPluginInstall(){
		$dao = new SOY2DAO();
		
		$sql = "select * from soyshop_point limit 1";
		try{
			$dao->executeQuery($sql, array());
		}catch(Exception $e){
			return false;
		}
		
		return true;
	}
	
	//既にコメントを行い、ポイントを付与されているかを調べる
	function checkPointHistory($userId){
		$historyDao = SOY2DAOFactory::create("SOYShop_PointHistoryDAO");
		try{
			$histories = $historyDao->getByUserId($userId);
		}catch(Exception $e){
			return true;
		}
		
		if(count($histories) === 0) return true;
		
		$title = $this->entry->getTitle() . "にコメントして[0-9]+pt付与";
		foreach($histories as $history){
			preg_match('/' . $title . '/', $history->getContent(), $res);
			if(isset($res[0])) return false;
		}
		
		//既にポイント付与されている場合はfalseを返す
		return true;
	}
	
	//コメント付加時に挿入されるメッセージ
	function getHistoryMessage(){
		$message = $this->entry->getTitle() . "にコメントして" . $this->point . "pt付与";
		return $message;
	}
	
	function getUserId(){
		if(!class_exists("MyPageLogic")){
			SOY2::import("domain.config.SOYShop_DataSets");
			include_once(SOY2::RootDir() . "base/func/common.php");
			if(!defined("SOYSHOP_CURRENT_MYPAGE_ID")) define("SOYSHOP_CURRENT_MYPAGE_ID", soyshop_get_mypage_id());
				
			SOY2::import("logic.mypage.MyPageLogic");
		}
		
		$mypage = MyPageLogic::getMyPage();
		return $mypage->getUserId();
	}
		
	function setSiteId($siteId){
		$this->siteId = $siteId;
	}
	function setPoint($point){
		$this->point = $point;
	}
	function setEntry($entry){
		$this->entry = $entry;
	}
}
?>