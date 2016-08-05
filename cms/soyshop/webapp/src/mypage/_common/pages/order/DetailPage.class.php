<?php
SOY2::imports("module.plugins.download_assistant.domain.*");
SOYShopPlugin::load("soyshop.item.option");
class DetailPage extends MainMyPagePageBase{
	
	private $orderId;
	private $userId;
	private $itemDao;
	
	function doPost(){

	}
	
	function __construct($args){
		
		$mypage = MyPageLogic::getMyPage();
		
		//ログインチェック
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}
		
		//orderIdがない場合はorderトップへ戻す
		if(!isset($args[0])){
			$this->jump("order");
		}
		
		WebPage::WebPage();
		
		$user = $this->getUser();
		
		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));
		
		$this->orderId = $args[0];
		$this->userId = $user->getId();
		
		$this->buildOrder();		
	}
	
	function buildOrder(){
		
		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		try{
			$order = $orderDAO->getForOrderDisplay($this->orderId, $this->userId);
			$logic = SOY2Logic::createInstance("logic.order.OrderLogic");
			
			if(!$order->isOrderDisplay())throw new Exception;
			
		}catch(Exception $e){
			$this->jump("order");
		}
		
		//注文番号
		$this->addLabel("order_number", array(
			"text" => $order->getTrackingNumber()
		));
		
		//注文日時
		$this->addLabel("order_date", array(
			"text" => date("Y年m月d日 H時i分s秒", $order->getOrderDate())
		));
		
		//合計金額
		$this->addLabel("order_price", array(
			"text" => number_format($order->getPrice())
		));
		
		$attributes = $order->getAttributeList();
		
		//備考、支払方法、配送方法、配達時間
    	$this->createAdd("attribute_list", "_common.order.AttributeListComponent", array(
    		"list" => $order->getAttributeList()
    	));
    	
    	$this->addLabel("payment_status", array(
    		"text" => $order->getPaymentStatusText()
    	));
    	
    	//オーダーカスタムフィールド
    	$this->createAdd("customfield_list", "_common.order.OrderCustomfieldListComponent", array(
    		"list" => $this->getCustomfield()
    	));
    	
		//備考
    	$this->addLabel("order_memo", array(
    		"html" => (isset($attributes["memo"]["value"])) ? nl2br(htmlspecialchars($attributes["memo"]["value"], ENT_QUOTES, "UTF-8")) : ""
    	));
    	
    	//送付先と請求先のsoy:idを生成する
    	$this->getAddressList($order, "send");
    	$this->getAddressList($order, "claimed");
		
		//注文の内訳
		try{
			$itemOrders = $logic->getItemsByOrderId($this->orderId);
		}catch(Exception $e){
			$itemOrders = array();
		}
		
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		
    	$this->createAdd("item_list", "_common.order.ItemOrderListComponent", array(
    		"list" => $itemOrders,
    		"itemDao" => $this->itemDao
    	));
    	
    	//ダウンロード商品関連
    	$activedDownloadPlugin = (class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("download_assistant"));
    	if($activedDownloadPlugin){
    		$files = $this->getDownloadFiles($itemOrders);
    	}else{
    		$files = array();
    	}
    	
    	//ボーナス
    	$activeBonusPlugin = (class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("bonus_download"));
    	if($activeBonusPlugin){
    		$bonuses = $this->getBonusFiles($order);
    	}else{
    		$bonuses = array();
    	}
    	
    	$this->addModel("is_download_files", array(
    		"visible" => (count($files) > 0 || count($bonuses))
    	));
    	
    	$this->createAdd("download_list", "_common.order.DownloadListComponent", array(
    		"list" => $files,
    		"order" => $order
    	));
    	
    	$this->createAdd("bonus_list", "_common.order.BonusListComponent", array(
    		"list" => $bonuses
    	));
    	    	
    	$this->addModel("is_subtotal", array(
    		"visible" => $this->checkTaxModule($order->getModuleList())
    	));
    	
    	$this->addLabel("subtotal_item_count", array(
    		"text" => $this->getSubtotalItemCount($itemOrders)
    	));
    	
    	$this->addLabel("subtotal_price", array(
    		"text" => $this->getSubtotalPrice($itemOrders)
    	));
    	
    	$this->createAdd("module_list", "_common.order.ModuleListComponent", array(
    		"list" => $order->getModuleList()
    	));
    	
    	//送料も含めたトータルの金額
    	$this->addLabel("order_total_price", array(
    		"text" => number_format($order->getPrice())
    	));
    	
    	$this->addLink("top_link", array(
    		"link" => soyshop_get_mypage_top_url()
    	));
		
		
		SOYShopPlugin::load("soyshop.order.createadd");
    	$delegate = SOYShopPlugin::invoke("soyshop.order.createadd", array(
    		"order" => $order,
    		"orders" => $itemOrders,
    		"page" => $this
    	));
		
	}
	
	function getAddressList(SOYShop_Order $order, $mode = "send"){
		
		//送付先の場合
		if($mode == "send"){
			$prefix = "user_";
			$address = $order->getAddressArray();
		//請求先の場合
		}else{
			$prefix = "claimed_";
			$address = $order->getClaimedAddressArray();
		}
		
		$this->addLabel($prefix . "name", array(
    		"text" => (isset($address["name"])) ? $address["name"] : ""
    	));
    	$this->addLabel($prefix . "zipcode", array(
    		"text" => (isset($address["zipCode"])) ? $address["zipCode"] : ""
    	));
    	$this->addLabel($prefix . "area", array(
    		"text" => (isset($address["area"])) ? SOYShop_Area::getAreaText($address["area"]) : ""
    	));
    	$this->addLabel($prefix . "address1", array(
    		"text" => (isset($address["address1"])) ? $address["address1"] : ""
    	));
    	$this->addLabel($prefix . "address2", array(
    		"text" => (isset($address["address2"])) ? $address["address2"] : ""
    	));
    	$this->addLabel($prefix . "tel", array(
    		"text" => (isset($address["telephoneNumber"])) ? $address["telephoneNumber"] : ""
    	));
	}
	
	//taxモジュールが登録されているか？をチェックする
    function checkTaxModule($modules){
    	
    	if(count($modules) === 0) return false;
    	
    	$res = false;
    	foreach($modules as $module){
    		if($module->getType() == SOYShop_ItemModule::TYPE_TAX){
    			$res = true;
    			break;
    		}
    	}
    	
    	return $res;
    }
    
    //小計時のアイテムの総個数
    function getSubtotalItemCount($itemOrders){
    	$total = 0;
    	
    	if(count($itemOrders) === 0) return $total;
    	
    	foreach($itemOrders as $itemOrder){
    		$total += $itemOrder->getItemCount();
    	}
    	
    	return $total;
    }
    
    function getSubtotalPrice($itemOrders){
    	$total = 0;
    	
    	if(count($itemOrders) === 0) return $total;
    	
    	foreach($itemOrders as $itemOrder){
    		$total += $itemOrder->getTotalPrice();
    	}
    	
    	return $total;
    }
	
	function getCustomfield(){
    	SOYShopPlugin::load("soyshop.order.customfield");
    	$delegate = SOYShopPlugin::invoke("soyshop.order.customfield", array(
    		"mode" => "admin",
    		"orderId" => $this->orderId
    	));

    	$array = array();
    	foreach($delegate->getDisplay() as $obj){
    		if(is_array($obj)){
    			foreach($obj as $value){
    				$array[] = $value;
    			}
    		}
    	}

    	return $array;
    }

	function getDownloadFiles($itemOrders){
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
			$downloadDao = SOY2DAOFactory::create("SOYShop_DownloadDAO");
		
			foreach($items as $item){
				try{
					$array = $downloadDao->getFilesByOrderIdAndItemIdAndUserId($this->orderId, $item->getId(), $this->userId);
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
		
		return $files;
	}
	
	/**
	 * ボーナスダウンロードリストを作成
	 * @param object SOYShop_Order
	 * @return array
	 */
	function getBonusFiles(SOYShop_Order $order){
		$paymentFlag = (int)$order->getPaymentStatus();
		
		$attributes = $order->getAttributeList();
		
		$nameList = array();
		$timelimitList = array();
		$urlList = array();
		
		foreach($attributes as $key => $array){
			if(strpos($key, "bonus_download.filename.") === 0){
				$nameList[] = $array["value"];
			}
			if(strpos($key, "bonus_download.timelimit.") === 0){
				$timelimitList[] = $array["value"];
			}
			if(strpos($key, "bonus_download.url_list") === 0){
				$urlList = explode("\n", $array["value"]);
			}
		}
		
		$list = array();
		if(count($nameList) > 0 && count($urlList) > 0){
			for($i = 0; $i < count($urlList); $i++){
				$array = array();
				$array["filename"] = $nameList[$i];
				$array["timelimit"] = $timelimitList[$i];
				$array["url"] = $urlList[$i];
				$array["payment"] = $paymentFlag;
				$list[] = $array;
			}
		}
		
		return $list;
	}
}
?>