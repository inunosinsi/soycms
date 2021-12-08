<?php

class NoticeLogic extends SOY2LogicBase{

	private $shopConfig;

	function __construct(){
		SOY2::import("module.plugins.common_notice_arrival.domain.SOYShop_NoticeArrivalDAO");
	}

	/**
	 * 入荷通知登録
	 * @params itemId, userId
	 */
	function register(int $itemId, int $userId){
		$obj = new SOYShop_NoticeArrival();
		$obj->setItemId($itemId);
		$obj->setUserId($userId);
		try{
			self::_dao()->insert($obj);
		}catch(Exception $e){
			//
		}
	}

	/**
	 * 入荷通知のメール送信後にデータベースの更新を行う
	 * @params itemId, userId
	 */
	function sended(int $itemId, int $userId){
		$obj = self::_get($itemId, $userId);
		$obj->setSended(SOYShop_NoticeArrival::SENDED);
		try{
			self::_dao()->update($obj);
		}catch(Exception $e){
			//
		}
	}

	function update(SOYShop_NoticeArrival $obj, int $sended=SOYShop_NoticeArrival::NOT_SENDED, int $checked=SOYShop_NoticeArrival::NOT_CHECKED){
		$obj->setSended($sended);
		$obj->setChecked($checked);
		try{
			self::_dao()->update($obj);
		}catch(Exception $e){
			//
		}
	}

	/**
	 * @params
	 * @return array(SOYShop_User)
	 */
	function getUsers(int $sended=SOYShop_NoticeArrival::NOT_SENDED, int $checked=SOYShop_NoticeArrival::NOT_CHECKED){
		return self::_dao()->getUsers($sended, $checked);
	}

	/**
	 * @params itemId
	 * @return array(SOYShop_User)
	 */
	function getUsersByItemId(int $itemId, int $sended=SOYShop_NoticeArrival::NOT_SENDED, int $checked=SOYShop_NoticeArrival::NOT_CHECKED){
		return self::_dao()->getUsersByItemId($itemId, $sended, $checked);
	}

	function getUsersForNewsPage(int $sended=SOYShop_NoticeArrival::NOT_SENDED, int $checked=SOYShop_NoticeArrival::NOT_CHECKED){
		return self::_dao()->getUsersForNewsPage($sended, $checked);
	}

	/**
	 * お気に入りに登録済みか調べる。既に登録済みの場合はtrue
	 * @param itemId, userId
	 * @return boolean
	 */
	function checkRegistered(int $itemId, int $userId=0){
		return (is_numeric(self::_get($itemId, $userId, SOYShop_NoticeArrival::NOT_SENDED)->getId()));
	}

	function checkSended(int $itemId, int $userId=0){
		return (self::_get($itemId, $userId)->getSended() == SOYShop_NoticeArrival::SENDED);
	}

	function getNoticeItem(int $itemId, int $userId=0, int $sended=-1, int $checked=-1){
		return self::_get($itemId, $userId, $sended, $checked);
	}

	/**
	 * @params itemId, userId
	 * @return SOYShop_NoticeItem
	 */
	private function _get(int $itemId, int $userId=0, int $sended=-1, int $checked=-1){
		try{
			return self::_dao()->getByItemIdAndUserId($itemId, $userId, $sended, $checked);
		}catch(Exception $e){
			$obj = new SOYShop_NoticeArrival();
			$obj->setItemId($itemId);
			$obj->setUserId($userId);
			$obj->setSended($sended);
			$obj->setChecked($checked);
			return $obj;
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_NoticeArrivalDAO");
		return $dao;
	}
}
