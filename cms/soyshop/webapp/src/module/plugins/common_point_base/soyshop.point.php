<?php
SOY2::imports("module.plugins.common_point_base.util.*");
class CommonPointBase extends SOYShopPointBase{

	function doPost(int $userId){
		if(isset($_POST["Point"])){
			$newPoint = mb_convert_kana($_POST["Point"], "a");
			if(!is_numeric($newPoint)) return;

			$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
			$oldPoint = (int)$logic->getPointObjByUserId($userId)->getPoint();
			
			if($newPoint != $oldPoint){
				$logic->updatePoint($newPoint, $userId);
			}
		}
	}

	/**
	 * @param int userId
	 * @return int
	 */
	function getPoint(int $userId){
		$point = self::getPointObjByUserId($userId)->getPoint();
		return (is_numeric($point)) ? (int)$point : 0;
	}

	/**
	 * @param int userId
	 * @return int
	 */
	function getTimeLimit(int $userId){
		$timeLimit = self::getPointObjByUserId($userId)->getTimeLimit();
		return (isset($timeLimit)) ? (int)$timeLimit : null;
	}

	private function getPointObjByUserId(int $userId){
		static $obj;
		if(is_null($obj)) $obj = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic")->getPointObjByUserId($userId);
		return $obj;
	}
}
SOYShopPlugin::extension("soyshop.point", "common_point_base", "CommonPointBase");
