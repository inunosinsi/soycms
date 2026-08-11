<?php
SOY2::import("module.plugins.inquiry_on_mypage.domain.SOYShop_Inquiry");
/**
 * @entity SOYShop_Inquiry
 */
abstract class SOYShop_InquiryDAO extends SOY2DAO{

	/**
	 * @return id
	 * @trigger onInsert
	 */
   	abstract function insert(SOYShop_Inquiry $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_Inquiry $bean);

	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	abstract function getByUserId($userId);

	/**
	 * @return object
	 */
	abstract function getByMailLogId($mailLogId);

	/**
	 * @return object
	 */
	abstract function getByTrackingNumber($trackingNumber);

	abstract function getByIsConfirm($isConfirm);

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(!isset($binds[":mailLogId"]) || !is_numeric($binds[":mailLogId"])) $binds[":mailLogId"] = 0;
		$binds[":isConfirm"] = 0;	//未確認
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}

	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		return array($query, $binds);
	}
}
