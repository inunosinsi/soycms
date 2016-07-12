<?php
class SOYMailConnectorOrderComplete extends SOYShopOrderComplete{

	private $dao;

	function execute(SOYShop_Order $order){
		
		//ポイントプラグインがインストールされていない場合は実行しない
		if(!class_exists("SOYShopPluginUtil") || !SOYShopPluginUtil::checkIsActive("common_point_base")) return;
		
		//初回購入時にメルマガ会員になればポイント贈与
		$attrs = $order->getAttributeList();
		if(!array_key_exists("soymail_connector.value", $attrs) || !array_key_exists("order_first_order", $attrs)) return;
		if($attrs["soymail_connector.value"]["value"] !== "希望する") return;
		
		//パスワードの登録があるか調べる
		if(!self::checkPasswordRegister($order->getUserId())) return;
		
		//ポイント履歴があれば初回ではない。マイページの方で登録している可能性がある
		SOY2::imports("module.plugins.common_point_base.domain.*");
		if(!self::checkNoPointHistories($order->getUserId())) return;
		
		//ここまで通過したらポイント登録の処理に入る
		SOY2::import("module.plugins.soymail_connector.util.SOYMailConnectorUtil");
		$config = SOYMailConnectorUtil::getConfig();
		
		if(isset($config["first_order_add_point"]) && (int)$config["first_order_add_point"] > 0){
			$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
			$logic->insert((int)$config["first_order_add_point"], "メールマガジン会員登録プレゼント：" . $config["first_order_add_point"] . "ポイント", $order->getUserId());
		}
	}
	
	private function checkPasswordRegister($userId){
		try{
			$user = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($userId);
		}catch(Exception $e){
			return false;
		}
		
		return (strlen($user->getPassword()));
	}
	
	private function checkNoPointHistories($userId){
		try{
			$histories = SOY2DAOFactory::create("SOYShop_PointHistoryDAO")->getByUserId($userId);
		}catch(Exception $e){
			$histories = array();
		}
		
		if(is_null($histories) || !count($histories)) return true;
		
		//履歴の中にすでにメルマガ会員登録プレゼントがある場合は処理をやめる
		foreach($histories as $his){
			if(is_null($his->getOrderId())){
				if(strpos($his->getContent(), "メールマガジン会員登録") === 0) return false;
			}
		}
		
		return true;
	}
}
SOYShopPlugin::extension("soyshop.order.complete", "soymail_connector", "SOYMailConnectorOrderComplete");
?>