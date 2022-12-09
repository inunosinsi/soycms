<?php
function execute(){	
	set_time_limit(0);
	$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
	
	if(defined("SOYSHOP_SITE_DSN")&&preg_match('/mysql/',SOYSHOP_SITE_DSN)){
		$sqlAdd = sqlMySQLAdd();
	}else{
		$sqlAdd = sqlSQLiteAdd();
	}
	
	_echob("<br />[オーダー(カラム追加)]");
	$sqlAdd = explode(";",$sqlAdd);
	$flg = false;
	foreach($sqlAdd as $query){
		$query = trim($query);
		if(!$query)continue;

		try{
			$orderDao->executeUpdateQuery($query);
			
		}catch(Exception $e){
			$flg = true;
		}
	}
	
	if($flg){
		_echo("・請求書カラム追加は失敗しました。");
	}else{
		_echo("・請求書カラム追加を行いました。");
	}
	
	
	//注文の全データを取得
	try{
		$orders = $orderDao->get();
		$orderFlag = true;
	}catch(Exception $e){
		$orderFlag = false;
	}
	
	if($orderFlag){
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		
		foreach($orders as $order){
			$userId = $order->getUserId();
			
			try{
				$user = $userDao->getById($userId);
				$userFlag = true;
			}catch(Exception $e){
				$userFlag = false;
			}
			
			if($userFlag){
				$claimedAddress = getClaimedAddress($user);
				$order->setClaimedAddress($claimedAddress);
				
				try{
					$orderDao->update($order);
				}catch(Exception $e){
					
				}
			}
		}
	}			
	
	_echo();
	_echo();
	$link = SOY2PageController::createLink("");
	_echo("アップグレードバッチは終了しました。");
//	_echo("続いてファイルの上書きを実行してください。");
	_echo("<a href='$link'>SOY Shop管理画面に戻る</a>");
	exit;

}


function sqlSQLiteAdd(){
	$sql = <<<SQL
ALTER TABLE soyshop_order ADD COLUMN claimed_address VARCHAR;

SQL;

	return $sql;
}

function sqlMySQLAdd(){
	$sql = <<<SQL

ALTER TABLE soyshop_order ADD COLUMN claimed_address TEXT AFTER address;

SQL;

	return $sql;
}


function _echo($str=""){
	echo $str."<br />";
}

function _echob($str=""){
	_echo("<b>" . $str."</b>");
}

function getClaimedAddress($user){
   	return array(
		"name" => $user->getName(),
		"reading" => $user->getReading(),
		"zipCode" => $user->getZipCode(),
		"area" => $user->getArea(),
		"address1" => $user->getAddress1(),
		"address2" => $user->getAddress2(),
		"telephoneNumber" => $user->getTelephoneNumber(),
		"office" => $user->getJobName(),
	);
}
?>