<?php
SOY2::import("domain.order.SOYShop_ItemModule");
SOYShopPlugin::load("soyshop.item.option");
class DetailPage extends WebPage{

	const CHANGE_STOCK_MODE_CANCEL = "cancel";	//キャンセルにした場合
	const CHANGE_STOCK_MODE_RETURN = "return";	//キャンセルから他のステータスに戻した場合

	private $id;

	function doPost(){
		if(!AUTH_OPERATE) return;	//操作権限がないアカウントの場合は以後のすべての動作を封じる

		if(soy2_check_token()){
			$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
			$historyDAO = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");
			$historyContents = array();

			$order = $dao->getById($this->id);
			$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");

			//確認状態
			if(isset($_POST["do_confirm"])){
				//なにもチェックがなければ、全てを確認前に戻す
				$isConfirmItemOrders = (isset($_POST["Confirm"])) ? $_POST["Confirm"] : array();

				//どの商品(idx)を確認済み or 確認前に戻したか？の履歴を残す
				$historyContents = $orderLogic->changeItemOrdersIsConfirm($order->getId(), $isConfirmItemOrders);
			}

			//状態
			if(isset($_POST["Status"]) && is_array($_POST["Status"]) && count($_POST["Status"])){
				$hists = $orderLogic->changeItemOrdersStatus($order->getId(), $_POST["Status"]);
				if(is_array($hists) && count($hists)){
					$historyContents = array_merge($historyContents, $hists);
				}
			}

			//フラグ
			if(isset($_POST["Flag"]) && is_array($_POST["Flag"]) && count($_POST["Flag"])){
				$hists = $orderLogic->changeItemOrdersFlag($order->getId(), $_POST["Flag"]);
				if(is_array($hists) && count($hists)){
					$historyContents = array_merge($historyContents, $hists);
				}
			}

			if (isset($_POST["Comment"]) && strlen($_POST["Comment"])) {
				$historyContents[] = $_POST["Comment"];
			}

			if (isset($_POST["State"])) {
				$post = (object)$_POST["State"];

				if (isset($_POST["State"]["orderStatus"]) && $order->getStatus() != $post->orderStatus) {
					$orderLogic->changeOrderStatus(array($order->getId()), $post->orderStatus);
				}
				if (isset($_POST["State"]["paymentStatus"]) && $order->getPaymentStatus() != $post->paymentStatus) {
					$orderLogic->changePaymentStatus(array($order->getId()), $post->paymentStatus);
				}

				SOYShopPlugin::load("soyshop.order.status.update");
	    		SOYShopPlugin::invoke("soyshop.order.status.update", array(
	    			"order" => $order,
	    			"mode" => "status"
	    		));
			}

			SOYShopPlugin::load("soyshop.comment.form");
			$delegate = SOYShopPlugin::invoke("soyshop.comment.form", array(
				"order" => $order
			));

			if(count($delegate->getHistories())) {
				foreach($delegate->getHistories() as $historyContent){
					if(strlen($historyContent)) $historyContents[] = $historyContent;
				}
			}

			SOYShopPlugin::load("soyshop.operate.credit");
			SOYShopPlugin::invoke("soyshop.operate.credit", array(
				"order" => $order,
				"mode" => "order_detail"
			));


			if (count($historyContents)) {
				$history = new SOYShop_OrderStateHistory();
				$history->setOrderId($this->id);
				$history->setAuthor(SOY2Logic::createInstance("logic.order.OrderHistoryLogic")->getAuthor());	//ログインしているアカウントを返すことにする
				$history->setContent(implode("\n" ,$historyContents));
				$history->setDate(time());
			}

			try{
				if (isset($history)) {
					$dao->updateStatus($order);
					$historyDAO->insert($history);
				}

				SOY2PageController::jump("Order.Detail." . $this->id . "?updated");
				exit;
			}catch(Exception $e){
				//@TODO エラー処理
				//var_dump($e);
			}
		}
	}

    function __construct($args) {
    	MessageManager::addMessagePath("admin");
    	$this->id = (isset($args[0])) ? $args[0] : null;

    	parent::__construct();

		//詳細ページを開いた時に何らかの処理をする
		SOYShopPlugin::load("soyshop.order");
		SOYShopPlugin::invoke("soyshop.order", array(
			"mode" => "detail",
			"orderId" => $this->id
		));

		if(!class_exists("SOYShopPluginUtil")) SOY2::import("util.SOYShopPluginUtil");

		DisplayPlugin::toggle("sended", isset($_GET["sended"]));
		DisplayPlugin::toggle("copy", isset($_GET["copy"]));

		$order = soyshop_get_order_object($this->id);
		if(!$order) SOY2PageController::jump("Order");

		$user = soyshop_get_user_object($order->getUserId());

    	$this->addLabel("order_name_text", array(
			"text" => $order->getTrackingNumber()
		));

    	$this->addLabel("order_id", array(
			"text" => $order->getTrackingNumber()
		));
    	$this->addLabel("order_raw_id", array(
			"text" => $order->getId()
		));

		/** 注文番号のバーコード **/
		if(SOYShopPluginUtil::checkIsActive("generate_barcode_tracking_number")){
			SOY2::import("module.plugins.generate_barcode_tracking_number.util.GenerateBarcodeUtil");
			$barcodeSrc = GenerateBarcodeUtil::getBarcodeImagePath($order->getTrackingNumber() . ".jpg");
		}else{
			$barcodeSrc = null;
		}

		DisplayPlugin::toggle("barcode", strlen($barcodeSrc));
		$this->addImage("barcode", array(
			"src" => $barcodeSrc
		));

		$this->addLink("barcode_download_button", array(
			"link" => $barcodeSrc,
			"attr:download" => $order->getTrackingNumber() . ".jpg"
		));
		/** 注文番号のバーコード **/


		$this->addLabel("order_date", array(
			"text" => date('Y-m-d H:i', $order->getOrderDate())
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $order->getId())
		));

		$this->addLink("edit_link", array(
			"link" => (AUTH_ADMINORDER) ? SOY2PageController::createLink("Order.Edit." . $order->getId()) : "",
			"visible" => (AUTH_ADMINORDER)
		));

		$this->addLabel("order_status", array(
			"text" => $order->getOrderStatusText()
		));

		$this->addLabel("payment_status", array(
			"text" => $order->getPaymentStatusText()
		));

    	$this->addLabel("order_price", array(
    		"text" => soy2_number_format($order->getPrice()) . " 円"
    	));

       	$this->createAdd("attribute_list", "_common.Order.AttributeListComponent", array(
    		"list" => $order->getAttributeList()
    	));

    	//ポイント履歴
		$this->createAdd("point_history_list", "_common.Order.PointHistoryListComponent", array(
			"list" => (SOYShopPluginUtil::checkIsActive("common_point_base")) ? array_reverse(self::getPointHistories($order->getId())) : array()
		));

		//チケット履歴
		$activedTicketPlugin = SOYShopPluginUtil::checkIsActive("common_ticket_base");
		if($activedTicketPlugin){
			SOY2::import("module.plugins.common_ticket_base.util.TicketBaseUtil");
			$ticketConfig = TicketBaseUtil::getConfig();
			$label = $ticketConfig["label"];
		}else{
			$label = "チケット";
		}
		$this->createAdd("ticket_history_list", "_common.Order.TicketHistoryListComponent", array(
			"list" => ($activedTicketPlugin) ? array_reverse(self::getTicketHistories($order->getId())) : array(),
			"label" => $label
		));

    	$this->createAdd("customfield_list", "_common.Order.CustomFieldListComponent", array(
    		"list" => $this->getCustomfield()
    	));

        /*** 顧客情報 ***/
        SOY2DAOFactory::importEntity("user.SOYShop_User");
        SOY2DAOFactory::importEntity("config.SOYShop_Area");

		$customer = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($order->getUserId());
		if(is_null($customer->getId())) $customer->setName("[deleted]");

    	$this->addLink("customer", array(
    		"text" => $customer->getName(),
    		"link" => SOY2PageController::createLink("User.Detail." . $customer->getId())
    	));
    	$this->addLabel("customer_name", array(
    		"text" => $customer->getName(),
    	));
    	$this->addModel("show_customer_area", array(
    		"visible" => strlen(SOYShop_Area::getAreaText($customer->getArea())),
    	));
    	$this->addLabel("customer_area", array(
    		"text" => SOYShop_Area::getAreaText($customer->getArea()),
    	));
    	$this->addLink("customer_email", array(
    		"text" => "<" . $customer->getMailAddress() . ">",
    		"link" => strlen($customer->getMailAddress()) ? "mailto:" . $customer->getMailAddress() : ""
    	));
    	$this->addLink("customer_link", array(
    		"link" => SOY2PageController::createLink("User.Detail." . $customer->getId())
    	));

		$claimedAddress = $order->getClaimedAddressArray();

    	$customerHTML = "";
    	if(isset($claimedAddress["office"])){
    		$customerHTML.= $claimedAddress["office"] ."\n";
    	}
    	$customerHTML.= $claimedAddress["name"];
    	if(isset($claimedAddress["reading"]) && strlen($claimedAddress["reading"])){
    		$customerHTML.= " (" . $claimedAddress["reading"] . ")";
    	}
    	$customerHTML.= "\n";
    	$customerHTML.= $claimedAddress["zipCode"]. "\n";
		$addr = "";
		for($i = 1; $i <= 3; $i++){
			if(isset($claimedAddress["address" . $i])) $addr .= $claimedAddress["address" . $i];
		}
    	$customerHTML.= SOYShop_Area::getAreaText($claimedAddress["area"]) . $addr . "\n";
    	if(isset($claimedAddress["telephoneNumber"])){
    		$customerHTML.= $claimedAddress["telephoneNumber"] . "\n";
    	}

    	$this->addLabel("claimed_customerinfo", array(
    		"html" => nl2br(htmlspecialchars(trim($customerHTML), ENT_QUOTES, "UTF-8"))
    	));

    	$address = $order->getAddressArray();

    	$customerHTML = ""; //customerHTML変数の初期化
    	if(isset($address["office"])){
    		$customerHTML.= $address["office"] . "\n";
    	}
    	$customerHTML.= $address["name"];
    	if(isset($address["reading"]) && strlen($address["reading"])){
    		$customerHTML.= " (" . $address["reading"] . ")";
    	}
    	$customerHTML.= "\n";
    	$customerHTML.= $address["zipCode"] . "\n";
		$addr = "";
		for($i = 1; $i <= 3; $i++){
			if(isset($address["address" . $i])) $addr .= $address["address" . $i];
		}
    	$customerHTML.= SOYShop_Area::getAreaText($address["area"]) . $addr . "\n";
    	if(isset($address["telephoneNumber"])){
    		$customerHTML.= $address["telephoneNumber"] . "\n";
    	}

    	$this->addLabel("order_customerinfo", array(
    		"html" => nl2br(htmlspecialchars(trim($customerHTML), ENT_QUOTES, "UTF-8"))
    	));

		$this->addLink("order_link", array(
			"link" => SOY2PageController::createLink("Order.Order." . $order->getId()),
			"style" => "font-weight:normal !important;"
		));

		$itemOrders = SOY2Logic::createInstance("logic.order.OrderLogic")->getItemsByOrderId($this->id);

        /*** 注文商品 ***/
		$this->addForm("confirm_form");

		//仕入値を出力するか？
		$this->addModel("is_purchase_price", array(
			"visible" => (SOYShop_ShopConfig::load()->getDisplayPurchasePriceOnAdmin())
		));

    	$this->createAdd("item_list", "_common.Order.ItemOrderListComponent", array(
    		"list" => $itemOrders,
    	));

    	$this->addLabel("order_total_price", array(
    		"text" => soy2_number_format($order->getPrice())
    	));


    	/** 注文状況の変更に関して **/

    	$this->createAdd("module_list", "_common.Order.ModuleListComponent", array(
    		"list" => $order->getModuleList()
    	));

    	/** ダウンロード詳細 **/
		$activedDownloadPlugin = (class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("download_assistant"));
		$this->addModel("actived_download_plugin", array(
			"visible" => ($activedDownloadPlugin)
		));

		//ダウンロード補助プラグインがアクティブの場合
		if($activedDownloadPlugin){
			$this->buildFileList($itemOrders, $order);
		}

		/*** 状態変更フォームの生成 ***/
    	$this->addForm("update_form");

    	$this->createAdd("order_status_radio_list", "_common.Order.RadioButtonListComponent", array(
    		"list" => SOYShop_Order::getOrderStatusList(),
    		"selected" => $order->getStatus(),
    		"name" => "State[orderStatus]"
    	));

    	$this->createAdd("payment_status_radio_list", "_common.Order.RadioButtonListComponent", array(
    		"list" => SOYShop_Order::getPaymentStatusList(),
    		"selected" => $order->getPaymentStatus(),
    		"name" => "State[paymentStatus]"
    	));


    	/*** コメントフォームの生成 ***/
    	$this->addForm("comment_form");

    	$this->addInput("state_comment", array(
    		"name" => "Comment",
    		"size" => 70
    	));

    	SOYShopPlugin::load("soyshop.comment.form");
		$this->addLabel("extension_comment_form", array(
			"html" => SOYShopPlugin::display("soyshop.comment.form", array("order" => $order))
		));

    	/*** Output Action　***/
    	self::_outputActions();

		//HTMLの自由記述
		SOYShopPlugin::load("soyshop.order.edit");
		$this->addLabel("extension_html", array(
			"html" => SOYShopPlugin::invoke("soyshop.order.edit", array(
				"mode" => "html_on_detail",
				"orderId" => $order->getId()
			))->getHTML()
		));
    }

    /**
     * Action
     */
    private function _outputActions(){
    	SOYShopPlugin::load("soyshop.order.function");
    	$delegate = SOYShopPlugin::invoke("soyshop.order.function", array(
    		"orderId" => $this->id
    	));

    	$list = $delegate->getList();
    	if(!is_array($list)) $list = array();

    	$this->createAdd("action_list", "_common.Order.ActionListComponent", array(
    		"orderId" => $this->id,
    		"list" => $list
    	));

    	$this->addModel("has_action", array(
    		"visible" => (count($list) > 0)
    	));
    }

    private function getPointHistories($orderId){
		SOY2::imports("module.plugins.common_point_base.domain.*");
    	try{
    		return SOY2DAOFactory::create("SOYShop_PointHistoryDAO")->getByOrderId($orderId);
    	}catch(Exception $e){
    		return array();
    	}
    }

	private function getTicketHistories($orderId){
		SOY2::imports("module.plugins.common_ticket_base.domain.*");
    	try{
    		return SOY2DAOFactory::create("SOYShop_TicketHistoryDAO")->getByOrderId($orderId);
    	}catch(Exception $e){
    		return array();
    	}
    }

    function getCustomfield(){
    	SOYShopPlugin::load("soyshop.order.customfield");
    	$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
    		"mode" => "admin",
    		"orderId" => $this->id
    	));

    	$list = array();
    	foreach($delegate->getDisplay() as $values){
    		if(!is_array($values)) continue;
   			foreach($values as $value){
   				$list[] = $value;
   			}
    	}

    	return $list;
    }

    //ダウンロードファイルリストを取得
    function buildFileList($itemOrders, SOYShop_Order $order){
    	$files = array();
		$items = array();
		foreach($itemOrders as $itemOrder){
			try{
				$item = soyshop_get_item_object($itemOrder->getItemId());
			}catch(Exception $e){
				continue;
			}

			if($item->getType() === SOYShop_Item::TYPE_DOWNLOAD){
				$items[] = $item;
			}
		}

		if(count($items) > 0){
			SOY2::imports("module.plugins.download_assistant.domain.*");
			$downloadDao = SOY2DAOFactory::create("SOYShop_DownloadDAO");

			foreach($items as $item){
				try{
					$array = $downloadDao->getFilesByOrderIdAndItemIdAndUserId($order->getId(), $item->getId(), $order->getUserId());
				}catch(Exception $e){
					continue;
				}
				if(count($array) > 0){
					foreach($array as $file){
						$files[] = $file;
					}
				}
			}
		}

		$this->createAdd("file_list", "_common.Order.DownloadFileListComponent", array(
			"list" => $files,
			"order" => $order
		));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("注文詳細", array("Order" => "注文管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Order.FooterMenu.DetailFooterMenuPage", array(
				"arguments" => array($this->id)
			))->getObject();
		}catch(Exception $e){
			var_dump($e);
			//
			return null;
		}
	}
}
