<?php

class PaymentPage extends MainMyPagePageBase{

	function doPost(){
		if(soy2_check_token() && soy2_check_referer() && isset($_POST["Payment"])){
			//前の支払いデータと変更がある場合のみ入れ替え
			$order = $this->getOrderByIdAndUserId($this->orderId, $this->userId);
			$moduleList = $order->getModuleList();
			$attrList = $order->getAttributeList();

			$old = self::getSelectedPaymentMethod($moduleList);

			$new = $_POST["Payment"];
			if($old != $new){
				unset($moduleList[$old]);
				unset($attrList[$old]);

				$list = self::getPaymentMethodList();

				$module = new SOYShop_ItemModule();
				$module->setId($new);
				$module->setType("payment_module");//typeを指定しておくといいことがある
				$module->setName($list[$new]["name"]);
				$module->setPrice(0);	//@ToDo 金額の指定もしたいところ
				$module->setIsVisible(false);

				$moduleList[$new] = $module;
				$order->setModules($moduleList);

				//attributeの方も変更
				$attrList[$new] = array("name" => "支払方法", "value" => $list[$new]["name"]);
				$order->setAttributes($attrList);

				try{
					SOY2DAOFactory::create("order.SOYShop_OrderDAO")->update($order);
				}catch(Exception $e){
					$this->jump("order/edit/payment/" . $this->orderId . "?failed");
				}
			}

			//キャッシュの削除
			SOY2Logic::createInstance("module.plugins.order_edit_on_mypage.logic.HistoryIdCacheLogic")->removeCache();

			$this->jump("order/edit/payment/" . $this->orderId . "?updated");
		}
		$this->jump("order/edit/payment/" . $this->orderId . "?failed");
	}

	function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック

        //orderIdがない場合はorderトップへ戻す
        if(!isset($args[0]) || !SOYShopPluginUtil::checkIsActive("order_edit_on_mypage")) $this->jump("order");
		$this->orderId = (int)$args[0];
		$this->userId = (int)$this->getUser()->getId();

		//@ToDo すでに支払い済み場合は表示しない

		//この注文が指定した顧客のものであるか？
		$order = $this->getOrderByIdAndUserId($this->orderId, $this->userId);
		if(!$order->isOrderDisplay()) $this->jump("order");

		parent::__construct();

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));

		$this->addLabel("order_number", array(
			"text" => $order->getTrackingNumber()
		));

		$this->addForm("form");

		//paymentの拡張ポイントでhasOptionがないものを選ぶ
		$this->createAdd("form_list", "_common.order.PaymentSelectListComponent", array(
			"list" => self::getPaymentMethodList(),
			"selected" => self::getSelectedPaymentMethod($order->getModuleList())
		));

		$this->addLink("back_link", array(
			"link" => soyshop_get_mypage_url() . "/order/detail/" . $this->orderId . "?edit=reset"
		));
	}

	private function getPaymentMethodList(){
		SOYShopPlugin::load("soyshop.payment");
		return SOYShopPlugin::invoke("soyshop.payment", array(
			"mode" => "mypage",
			"cart" => new CartLogic()
		))->getList();
	}

	private function getSelectedPaymentMethod($modules){
		if(!count($modules)) return null;

		foreach($modules as $moduleId => $module){
			if(strpos($moduleId, "payment_") !== false) return $moduleId;
		}
		return null;
	}
}
