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
		$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$sql = "SELECT tracking_number, order_date FROM soyshop_order WHERE order_status = " . SOYShop_Order::ORDER_STATUS_INTERIM . " AND payment_status = " . SOYShop_Order::PAYMENT_STATUS_CONFIRMED;
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
		foreach($res as $v){
			$mes[] = "<li><strong>" . $v["tracking_number"] . "</strong> （" . date("Y-m-d H:i:s", $v["order_date"]) . "）</li>";
		}
		$mes[] = "</ul>";

		return implode("<br>", $mes);
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "arrival_new_order", "ArrivalNewOrderAdminTop");
