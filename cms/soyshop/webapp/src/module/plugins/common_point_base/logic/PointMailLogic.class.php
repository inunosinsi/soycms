<?php
class PointMailLogic extends SOY2LogicBase{

	private $pointDao;
	private $pointHistoryDao;
	
	function PointMailLogic(){
		SOY2::imports("module.plugins.common_point_base.util.*");
		SOY2::imports("module.plugins.common_point_base.domain.*");
	}
	
	/**
	 * @param int userId, int orderId
	 */
    function getHistories($userId, $orderId){
    	if(!$this->pointHistoryDao) $this->pointHistoryDao = SOY2DAOFactory::create("SOYShop_PointHistoryDAO");
    	
    	try{
    		$histories = $this->pointHistoryDao->getByUserIdAndOrderId($userId, $orderId);
    	}catch(Exception $e){
    		$histories = array();
    	}
    	
    	return $histories;
    }
    
    function getOrderCompleteMailContent($userId){
    	$mailBody = array();
    	
    	$config = PointBaseUtil::getConfig();
    	
    	//ポイント加算処理のフラグ
		$flag = true;
		
		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
		
		if(isset($config["customer"])){
			$user = $logic->getUser($userId);
			$pass = $user->getPassword();
			if(!isset($pass)) $flag = false;
		}
		
		if($flag){
			$point = $logic->getPointByUserId($userId);
			
			$mailBody[] = "";
			$mailBody[] = "現在のポイント：" . $point->getPoint() . "ポイント";
		}
		
		return implode("\n", $mailBody);
    }
}
?>