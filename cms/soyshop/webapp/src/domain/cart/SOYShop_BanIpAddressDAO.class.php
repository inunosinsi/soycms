<?php
/**
 * @entity cart.SOYShop_BanIpAddress
 */
abstract class SOYShop_BanIpAddressDAO extends SOY2DAO{

	/**
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_BanIpAddress $bean);

	/**
	 * @return object
	 */
	abstract function getByIpAddress($ipAddress);

	abstract function deleteByIpAddress($ipAddress);

	/**
	 *	閲覧しているIPアドレスが禁止されているか調べて、期限切れの場合は禁止を解除する
	 */
	function checkBanByIpAddressAndUpdate($ipAddress){
		try{
			$res = $this->executeQuery("SELECT log_date FROM soyshop_ban_ip_address WHERE ip_address = :attr", array(":attr" => $ipAddress));
		}catch(Exception $e){
			return false;
		}

		if(!isset($res[0]["log_date"])) return false;

		//使用禁止したカートを再び使用可にする時間
		SOY2::import("domain.config.SOYShop_ShopConfig");
		if($res[0]["log_date"] + SOYShop_ShopConfig::load()->getCartBanPeriod() * 60 * 60 < time()){
			try{
				$this->executeUpdateQuery("DELETE FROM soyshop_ban_ip_address WHERE ip_address = :attr", array(":attr" => $ipAddress));
				return false;
			}catch(Exception $e){
				//
			}
		}

		return true;
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":logDate"] = time();
		return array($query, $binds);
	}
}
