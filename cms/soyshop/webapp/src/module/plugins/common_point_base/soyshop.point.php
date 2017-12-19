<?php
SOY2::imports("module.plugins.common_point_base.util.*");
class CommonPointBase extends SOYShopPointBase{

	function doPost($userId){
		if(isset($_POST["Point"])){
			$newPoint = mb_convert_kana($_POST["Point"], "a");
			if(!is_numeric($newPoint)) return;

			$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
			$oldPoint = $logic->getPointByUserId($userId)->getPoint();

			if($newPoint != $oldPoint){
				$logic->updatePoint($newPoint, $userId);
			}
		}
	}

	/**
	 * @param int userId
	 * @return int
	 */
	function getPoint($userId){
		$point = self::getPointByUserId($userId)->getPoint();
		return (isset($point)) ? (int) $point : 0;
	}

	/**
	 * @param int userId
	 * @return int
	 */
	function getTimeLimit($userId){
		$timeLimit = self::getPointByUserId($userId)->getTimeLimit();
		return (isset($timeLimit)) ? (int)$timeLimit : null;
	}

	private function getPointByUserId($userId){
		static $obj;
		if(is_null($obj)) {
			$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
			$obj = $logic->getPointByUserId($userId);
		}
		return $obj;
	}
}
SOYShopPlugin::extension("soyshop.point", "common_point_base", "CommonPointBase");
