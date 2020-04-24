<?php
class PointMailLogic extends SOY2LogicBase{

	private $pointDao;
	private $pointHistoryDao;

	function __construct(){
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

		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");

    	//ポイント加算処理のフラグ
		$flag = true;

		$config = PointBaseUtil::getConfig();
		if(isset($config["customer"])){
			if(!strlen($logic->getUser($userId)->getPassword())) $flag = false;
		}

		if($flag){
			$mailBody[] = "";
			$mailBody[] = "現在のポイント：" . $logic->getPointByUserId($userId)->getPoint() . "ポイント";
		}

		return implode("\n", $mailBody);
    }
}
