<?php
class PointMailLogic extends SOY2LogicBase{

	private $pointDao;

	function __construct(){}

	/**
	 * @param int userId, int orderId
	 */
    function getHistories(int $userId, int $orderId){
		SOY2::import("module.plugins.common_point_base.domain.SOYShop_PointHistoryDAO");
    	try{
    		return SOY2DAOFactory::create("SOYShop_PointHistoryDAO")->getByUserIdAndOrderId($userId, $orderId);
    	}catch(Exception $e){
    		return array();
    	}
    }

    function getOrderCompleteMailContent(int $userId){
    	$mailBody = array();

		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");

    	//ポイント加算処理のフラグ
		$flag = true;

		SOY2::import("module.plugins.common_point_base.util.PointBaseUtil");
		$cnf = PointBaseUtil::getConfig();
		if(isset($cnf["customer"])){
			if(!strlen(soyshop_get_user_object($userId)->getPassword())) $flag = false;
		}

		if($flag){
			$mailBody[] = "";
			$mailBody[] = "現在のポイント：" . $logic->getPointObjByUserId($userId)->getPoint() . "ポイント";
		}

		return implode("\n", $mailBody);
    }
}
