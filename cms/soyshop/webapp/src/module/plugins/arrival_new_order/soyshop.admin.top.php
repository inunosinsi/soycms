<?php
class ArrivalNewOrderAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return SOY2PageController::createLink("Order");
	}

	function getLinkTitle(){
		if(SOYShopAuthUtil::getAuth() == SOYShopAuthUtil::AUTH_STORE_OWNER) return null;
		return "注文一覧";
	}

	function getTitle(){
		return "新着の注文";
	}

	function getContent(){
		if(SOYShopAuthUtil::getAuth() != SOYShopAuthUtil::AUTH_STORE_OWNER){
			SOY2::import("module.plugins.arrival_new_order.page.NewOrderAreaPage");
			$form = SOY2HTMLFactory::createInstance("NewOrderAreaPage");
			$form->setConfigObj($this);
			$form->execute();
			return $form->getObject();
		}else{	//モール出店者
			return "<div class=\"alert alert-warning\">@ToDo モール出店者用の表示</div>";
		}
	}

	function error(){
		SOY2::import("module.plugins.arrival_new_order.util.ArrivalNewOrderUtil");
		$config = ArrivalNewOrderUtil::getConfig();
		if(!isset($config["error"]) || !is_array($config["error"]) || !count($config["error"])) return null;

		$errCnf = $config["error"];
	
		$dao = soyshop_get_hash_table_dao("order");
		$sql = "SELECT tracking_number, payment_status, order_date FROM soyshop_order WHERE order_status = " . SOYShop_Order::ORDER_STATUS_INTERIM;

		// payment_status
		$subquery = array();
		if(isset($errCnf[SOYShop_Order::PAYMENT_STATUS_WAIT]) && (int)$errCnf[SOYShop_Order::PAYMENT_STATUS_WAIT] === ArrivalNewOrderUtil::ON){
			$subquery[] = "payment_status = " . SOYShop_Order::PAYMENT_STATUS_WAIT;
		}

		if(isset($errCnf[SOYShop_Order::PAYMENT_STATUS_CONFIRMED]) && (int)$errCnf[SOYShop_Order::PAYMENT_STATUS_CONFIRMED] === ArrivalNewOrderUtil::ON){
			$subquery[] = "payment_status = " . SOYShop_Order::PAYMENT_STATUS_CONFIRMED;
		}

		if(!count($subquery)) return null;
		$sql .= " AND (".implode(" OR ", $subquery).")";

		try{
			$res = $dao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res) || !isset($res[0]["tracking_number"])) return null;

		$mes = array();
		$mes[] = "クレジットカード支払いで失敗している可能性のある注文があります。";
		$mes[] = "注文の検索画面で下記の注文番号 + 注文状況を<strong>仮登録</strong>の条件で検索をして注文詳細をご確認ください。";
		$mes[] = "<ul>";
		$paymentList = SOYShop_Order::getPaymentStatusList();
		foreach($res as $v){
			if(!isset($paymentList[$v["payment_status"]])) continue;
			$mes[] = "<li><strong>" . $v["tracking_number"] . "</strong> （" . date("Y-m-d H:i:s", $v["order_date"]) . "）支払い状況：<strong>".$paymentList[$v["payment_status"]]."</strong></li>";
		}
		$mes[] = "</ul>";

		return implode("<br>", $mes);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_new_order", "ArrivalNewOrderAdminTop");
