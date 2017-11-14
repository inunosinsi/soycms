<?php
/**
 * @entity order.SOYShop_OrderStateHistory
 */
abstract class SOYShop_OrderStateHistoryDAO extends SOY2DAO{

    /**
	 * @return id
	 * @trigger onInsert
	 */
    abstract function insert(SOYShop_OrderStateHistory $bean);

    /**
     * @order id desc
     */
    abstract function getByOrderId($orderId);

    /**
     * @return list
     * @query order_id = :orderId AND order_date > :startDate AND order_date <= :endDate
     * @order id asc
     */
    abstract function getByOrderIdBetweenDate($orderId, $startDate, $endDate = 2147483647);

	/**
     * @final
     */
    function onInsert($query, $binds){
    	if(!isset($binds[":date"])) $binds[":date"] = time();
    	if(!isset($binds[":author"])){
    		/*
    		 * 管理画面では管理者情報をを登録する
    		 * SOY ShopでUserInfoUtilが使えることはないのでは？
    		 */
    		if(class_exists("UserInfoUtil")){
    			$author = UserInfoUtil::getUserName()." (".UserInfoUtil::getUserId().")";
    		}else{
    			$author = SOY2ActionSession::getUserSession()->getAttribute("username")." (".SOY2ActionSession::getUserSession()->getAttribute("userid").")";
    		}

    		$binds[":author"] = $author;
    	}
    	return array($query, $binds);
    }
}
