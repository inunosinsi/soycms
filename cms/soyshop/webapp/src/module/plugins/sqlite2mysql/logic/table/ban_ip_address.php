<?php

function register_ban_ip_address($stmt){
	$dao = SOY2DAOFactory::create("cart.SOYShop_BanIpAddressDAO");

	try{
		$bans = $dao->get();
		if(!count($bans)) return;
	}catch(Exception $e){
		return;
	}

	foreach($bans as $ban){
		$stmt->execute(array(
			":ip_address" => $ban->getIpAddress(),
			":plugin_id" => $ban->getPluginId(),
			":log_date" => $ban->getLogDate()
		));
	}
}
