<?php
SOY2::import("domain.order.SOYShop_ItemModule");
SOYShopPlugin::load("soyshop.item.option");
class DetailPage extends WebPage{

	private $id;
	private $itemDao;

	function doPost(){
		if(soy2_check_token()){
			$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
			$historyDAO = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");
			$historyContents = array();

			$order = $dao->getById($this->id);

			if (isset($_POST["Comment"]) && strlen($_POST["Comment"])) {
				$historyContents[] = $_POST["Comment"];
			}

			if (isset($_POST["State"])) {
				$post = (object)$_POST["State"];
				

				if (isset($_POST["State"]["orderStatus"]) && $order->getStatus() != $post->orderStatus) {
					$order->setStatus($post->orderStatus);
					$historyContents[] = "注文状態を<strong>「" . $order->getOrderStatusText() . "」</strong>に変更しました。";
				}
				if (isset($_POST["State"]["paymentStatus"]) && $order->getPaymentStatus() != $post->paymentStatus) {
					$order->setPaymentStatus($post->paymentStatus);
					$historyContents[] = "支払い状態を<strong>「" . $order->getPaymentStatusText() . "」</strong>に変更しました。";
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
				//ログインしているアカウントを返すことにする
				$session = SOY2ActionSession::getUserSession();
				$author = (!is_null($session->getAttribute("loginid"))) ? $session->getAttribute("loginid") :  "管理人";
				
				$history = new SOYShop_OrderStateHistory();
				$history->setOrderId($this->id);
				$history->setAuthor($author);
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

    	WebPage::__construct();

    	$this->addModel("sended", array(
    		"visible" => (isset($_GET["sended"]))
    	));

		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
		$order = $logic->getById($this->id);
		if(!$order){
			SOY2PageController::jump("Order");
		}
		
    	$this->addLabel("order_name_text", array(
			"text" => $order->getTrackingNumber()
		));

    	$this->addLabel("order_id", array(
			"text" => $order->getTrackingNumber()
		));
    	$this->addLabel("order_raw_id", array(
			"text" => $order->getId()
		));

		$this->addLabel("order_date", array(
			"text" => date('Y-m-d H:i', $order->getOrderDate())
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Order.Detail." . $order->getId())
		));

		$this->addLink("edit_link", array(
			"link" => SOY2PageController::createLink("Order.Edit." . $order->getId())
		));

		$this->addLabel("order_status", array(
			"text" => $order->getOrderStatusText()
		));

		$this->addLabel("payment_status", array(
			"text" => $order->getPaymentStatusText()
		));

    	$this->addLabel("order_price", array(
    		"text" => number_format($order->getPrice()) . " 円"
    	));

       	$this->createAdd("attribute_list", "_common.Order.AttributeListComponent", array(
    		"list" => $order->getAttributeList()
    	));
    	
    	//ポイント履歴
    	$activedPointPlugin = (class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("common_point_base"));
		if($activedPointPlugin){
			SOY2::imports("module.plugins.common_point_base.domain.*");
			$histories = $this->getPointHistories($order->getId());
		}else{
			$histories = array();
		}
		
		$this->createAdd("point_history_list", "_common.Order.PointHistoryListComponent", array(
			"list" => $histories
		));

    	$this->createAdd("customfield_list", "_common.Order.CustomFieldListComponent", array(
    		"list" => $this->getCustomfield()
    	));

        /*** 顧客情報 ***/
        SOY2DAOFactory::importEntity("user.SOYShop_User");
        SOY2DAOFactory::importEntity("config.SOYShop_Area");

		try{
    		$customer = SOY2DAOFactory::create("user.SOYShop_UserDAO")->getById($order->getUserId());
		}catch(Exception $e){
			$customer = new SOYShop_User();
			$customer->setName("[deleted]");
		}
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
    	$customerHTML.= SOYShop_Area::getAreaText($claimedAddress["area"]) . $claimedAddress["address1"] . $claimedAddress["address2"] . "\n";
    	if(isset($claimedAddress["telephoneNumber"])){
    		$customerHTML.= $claimedAddress["telephoneNumber"] . "\n";
    	}

    	$this->addLabel("claimed_customerinfo", array(
    		"html" => nl2br(htmlspecialchars($customerHTML, ENT_QUOTES, "UTF-8"))
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
    	$customerHTML.= SOYShop_Area::getAreaText($address["area"]) . $address["address1"] . $address["address2"] . "\n";
    	if(isset($address["telephoneNumber"])){
    		$customerHTML.= $address["telephoneNumber"] . "\n";
    	}

    	$this->addLabel("order_customerinfo", array(
    		"html" => nl2br(htmlspecialchars($customerHTML, ENT_QUOTES, "UTF-8"))
    	));

		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$itemOrders = $logic->getItemsByOrderId($this->id);

        /*** 注文商品 ***/
    	$this->createAdd("item_list", "_common.Order.ItemOrderListComponent", array(
    		"list" => $itemOrders,
    		"itemDao" => $this->itemDao
    	));

    	$this->addLabel("order_total_price", array(
    		"text" => number_format($order->getPrice())
    	));


    	/** 注文状況の変更に関して **/
    	$session = SOY2ActionSession::getUserSession();
		$appLimit = $session->getAttribute("app_shop_auth_limit");

    	//管理制限の権限を取得し、権限がない場合は表示しない
		$this->addModel("app_limit_function", array(
			"visible" => $appLimit
		));
		
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

		/*** 注文状態変更の履歴 ***/
		$historyDAO = SOY2DAOFactory::create("order.SOYShop_OrderStateHistoryDAO");
		try{
			$histories = $logic->getOrderHistories($order->getId());
		}catch(Exception $e){
			$histories = array();
		}

    	$this->createAdd("history_list", "_common.Order.HistoryListComponent", array(
    		"list" => $histories
    	));
    	
    	/*** メールの送信履歴 ***/
    	$mailLogDAO = SOY2DAOFactory::create("logging.SOYShop_MailLogDAO");
		try{
			$mailLogs = $mailLogDAO->getByOrderId($order->getId());
		}catch(Exception $e){
			$mailLogs = array();
		}
		
		$this->createAdd("mail_history_list", "_common.Order.MailHistoryListComponent", array(
    		"list" => $mailLogs
    	));
		
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

    	/*** メール送信フォームの生成 ***/
    	$mailStatus = $order->getMailStatusList();
    	$mailTypes = SOYShop_Order::getMailTypes();
    	foreach($mailTypes as $type){
	    	$this->addLabel($type . "_mail_status", array(
	    		"text" => (isset($mailStatus[$type])) ? date("Y-m-d H:i:s", $mailStatus[$type]) : "未送信"
	    	));

	    	$this->addLink($type . "_mail_link", array(
	    		"link" => SOY2PageController::createLink("Order.Mail." . $order->getId() . "?type=" . $type)
	    	));
    	}
    	
    	$this->createAdd("mail_plugin_list", "_common.Plugin.MailPluginListComponent", array(
    		"list" => self::getMailPluginList(),
    		"status" => $mailStatus,
    		"orderId" => $order->getId()
    	));

    	/*** Output Action　***/
    	$this->outputActions();

		/*** カード決済操作 ***/
		SOYShopPlugin::load("soyshop.operate.credit");
		$delegate = SOYShopPlugin::invoke("soyshop.operate.credit", array(
			"order" => $order,
			"mode" => "order_detail",
		));
		$list = $delegate->getList();
		DisplayPlugin::toggle("operate_credit_menu", count($list) > 0);
		
		$this->createAdd("operate_list", "_common.Order.OperateListComponent", array(
			"list" => $list
		));
    }

    /**
     * Action
     */
    function outputActions(){
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
    
    function getPointHistories($orderId){
    	$pointHistoryDao = SOY2DAOFactory::create("SOYShop_PointHistoryDAO");
    	try{
    		$histories = $pointHistoryDao->getByOrderId($orderId);
    	}catch(Exception $e){
    		$histories = array();
    	}
    	return $histories;
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
    
    private function getMailPluginList(){
    	SOYShopPlugin::load("soyshop.order.detail.mail");
    	$delegate = SOYShopPlugin::invoke("soyshop.order.detail.mail", array(
    	
    	));
    	
    	if(!count($delegate->getList())) return array();
    	
    	$list = array();
    	foreach($delegate->getList() as $values){
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
				$item = $this->itemDao->getById($itemOrder->getItemId());
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
}
?>