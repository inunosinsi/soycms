<?php

class SendmailLogic extends SOY2LogicBase{
	
	private $config;
	private $userDao;
	private $orderDao;
	private $itemOrderDao;
	private $itemDao;
	private $pageDao;
	
	function SendmailLogic(){
		SOY2::imports("module.plugins.order_later_sendmail.util.*");
		$this->orderDao = SOY2DAOFactory::create(".order.SOYShop_OrderDAO");
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	}
	
	function execute(){
		
		$this->config = OrderLaterSendmailUtil::getConfig();
		
		//設定が無い場合は処理を終了
		if(!isset($this->config["date"])){
			echo "送信設定がないため、メールの送信を中止しました。";
			return false;
		}
		
		$users = $this->getUsersAndOrderIdInKey();
		
		if(count($users) === 0){
			echo "該当する顧客がいないため、メールの送信を中止しました。";
			return false;
		}
		
		//取得したユーザ情報を元にメール送信の処理を開始する
		$title = OrderLaterSendmailUtil::getMailTitle();
		$content = OrderLaterSendmailUtil::getMailContent();
		$content = $this->convertCompanyInfomation($content);
		
		//MailLogicの呼び出し
		$mailLogic = SOY2Logic::createInstance("logic.mail.MailLogic");
		
		$counter = 0;
		foreach($users as $orderId => $user){
			if(strlen($user->getMailAddress()) === 0) continue;
			$title = $this->convertMailTitle($title);
			$body = $this->convertMailContent($content, $user, $orderId);
	
			$order = new SOYShop_Order();
			$order->setId($orderId);
			$order->setUserId($user->getId());
			$mailLogic->sendMail($user->getMailAddress(), $title, $body, null, $order);
			$counter++;
		}
		
		//動作確認
		echo $counter . "人にメールを送信しました。";
		return true;
	}
	
	function convertMailTitle($title){
		$title = $this->convertCompanyInfomation($title);
		return trim($title);
	}
		
	/**
	 * @params String content, object SOYShop_User, object SOYShop_Item item
	 * @return String body
	 */
	function convertMailContent($content, SOYShop_User $user, $orderId = null){
		//ユーザー情報
		$content = str_replace("#NAME#", $user->getName(), $content);
		$content = str_replace("#READING#", $user->getReading(), $content);
		$content = str_replace("#MAILADDRESS#", $user->getMailAddress(), $content);
		$content = str_replace("#BIRTH_YEAR#", $user->getBirthdayYear(), $content);
		$content = str_replace("#BIRTH_MONTH#", $user->getBirthdayMonth(), $content);
		$content = str_replace("#BIRTH_DAY#", $user->getBirthdayDay(), $content);
		
		//ポイント
		if(strpos($content, "#POINT#") && isset($orderId)){
			$content = str_replace("#POINT#", $user->getPoint(), $content);
		}
		
		//商品情報
		if(strpos($content, "#ORDER_ITEMS_LIST#") && isset($orderId)){
			$items = $this->getItemsByOrderId($orderId);
			if(count($items) > 0){
				$listTextArray = array();
				foreach($items as $item){
					$listTextArray[] = $item->getName();
					$listTextArray[] = $this->getDetailPageUrl($item);
				}
				
				$content = str_replace("#ORDER_ITEMS_LIST#", implode("\n", $listTextArray), $content);
			}
		}
		
		//最初に改行が存在した場合は改行を削除する
		return trim($content);
	}
	
	function getItemsByOrderId($orderId){
		if(!$this->itemOrderDao) $this->itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		if(!$this->itemDao) $this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		
		$items = array();
		
		try{
			$itemOrders = $this->itemOrderDao->getByOrderId($orderId);
		}catch(Exception $e){
			$itemOrders = array();
		}
		
		if(count($itemOrders)){
			foreach($itemOrders as $itemOrder){
				$itemId = $itemOrder->getItemId();
				try{
					$items[] = $this->itemDao->getById($itemId);
				}catch(Exception $e){
					continue;
				}
			}
		}
		
		return $items;
	}
	
	function getDetailPageUrl($item){
		if(!$this->pageDao) $this->pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
		try{
			$page = $this->pageDao->getById($item->getDetailPageId());
		}catch(Exception $e){
			return null;
		}
		
		return soyshop_get_site_url(true) . $page->getUri() . "/" . $item->getAlias();
	}
	
	function convertCompanyInfomation($content){
		
		if(!$this->shopConfig){
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$this->shopConfig = SOYShop_ShopConfig::load();
		}
		$config = $this->shopConfig;

		$content = str_replace("#SHOP_NAME#", $config->getShopName(), $content);

		$company = $config->getCompanyInformation();
		foreach($company as $key => $value){
			$content = str_replace(strtoupper("#COMPANY_" . $key ."#"), $value, $content);
		}
		$content = str_replace("#SITE_URL#", soyshop_get_site_url(true), $content);
		
		return $content;
	}
	
	/**
	 * 管理画面で設定した条件にマッチする顧客情報を取得する
	 * @return array(orderId => SOYShop_User);
	 */
	function getUsersAndOrderIdInKey(){
		
		$mode = (isset($this->config["mode"])) ? (int)$this->config["mode"] : 0;
		
		try{
			$results = $this->userDao->executeQuery($this->buildSql($mode), array());
		}catch(Exception $e){
			return array();
		}
		
		$users = array();
		foreach($results as $result){
			//modeが支払確認済みか発送済みの場合は取得したデータが最新であるかを調べる
			if($mode != OrderLaterSendmailUtil::MODE_REGISTER && !$this->checkHistoryOrderDate($result, $mode)) continue;
			
			$orderId = $result["id"];
			$result["id"] = $result["user_id"];
			$users[$orderId] = $this->userDao->getObject($result);
		}
		
		return $users;
	}
	
	function buildSql($mode){
		
		$noticeDate = (int)$this->config["date"];
		
		$start = $this->convertDateByCuttedTime(time() - $noticeDate * 24 * 60 * 60);
		$end = $this->convertDateByCuttedTime(time() - ($noticeDate - 1) * 24 * 60 * 60);
		
		switch($mode){
			//新規注文モード
			case OrderLaterSendmailUtil::MODE_REGISTER:
				$sql = "SELECT DISTINCT(o.id), o.user_id, user.mail_address, user.name, user.reading, user.birthday, o.order_date ".
						"FROM soyshop_order o ".
						"INNER JOIN soyshop_user user ".
						"ON o.user_id = user.id ".
						"WHERE o.order_date > " . $start . " ".
						"AND o.order_date < " . $end . " ".
						"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED;
				break;
			//支払確認済みモード
			case OrderLaterSendmailUtil::MODE_PAYMENT:
				$sql = "SELECT DISTINCT(o.id), o.user_id, user.mail_address, user.name, user.reading, user.birthday, history.order_date ".
						"FROM soyshop_order o ".
						"INNER JOIN soyshop_user user ".
						"ON o.user_id = user.id ".
						"INNER JOIN soyshop_order_state_history history ".
						"ON o.id = history.order_id ".						
						"WHERE history.order_date > " . $start . " ".
						"AND history.order_date < " . $end . " ".
						"AND (history.content LIKE '%支払%確認%' OR history.content LIKE '%直接支払%') ".
						"AND (o.payment_status = " . SOYShop_Order::PAYMENT_STATUS_CONFIRMED . " OR o.payment_status = " . SOYShop_Order::PAYMENT_STATUS_DIRECT . ") ".
						"AND o.order_status < " . SOYShop_Order::ORDER_STATUS_CANCELED . " ".
						"ORDER BY o.order_date DESC";
				break;
			//発送済みモード
			case OrderLaterSendmailUtil::MODE_SEND:
				$sql = "SELECT DISTINCT(o.id), o.user_id, user.mail_address, user.name, user.reading, user.birthday, history.order_date ".
						"FROM soyshop_order o ".
						"INNER JOIN soyshop_user user ".
						"ON o.user_id = user.id ".
						"INNER JOIN soyshop_order_state_history history ".
						"ON o.id = history.order_id ".
						"WHERE history.order_date > " . $start . " ".
						"AND history.order_date < " . $end . " ".
						"AND (history.content LIKE '%配送連絡%' OR history.content LIKE '%発送済み%') ".
						"AND o.order_status = " . SOYShop_Order::ORDER_STATUS_SENDED . " ";
						"ORDER BY o.order_date DESC";
				break;
			default:
				//何もしない
				$sql = "";
		}
				
		return $sql;
	}
	
	function checkHistoryOrderDate($values, $mode){
		switch($mode){
			//支払確認済みモード
			case OrderLaterSendmailUtil::MODE_PAYMENT:
				$sql = "SELECT history.order_date ".
						"FROM soyshop_order o ".
						"INNER JOIN soyshop_order_state_history history ".
						"ON o.id = history.order_id ".						
						"WHERE history.order_id = :orderId ".
						"AND o.user_id = :userId ".
						"AND (history.content LIKE '%支払%確認%' OR history.content LIKE '%直接支払%') ".
						"ORDER BY history.order_date DESC ".
						"LIMIT 1";
				break;
			//発送済みモード
			case OrderLaterSendmailUtil::MODE_SEND:
				$sql = "SELECT history.order_date ".
						"FROM soyshop_order o ".
						"INNER JOIN soyshop_order_state_history history ".
						"ON o.id = history.order_id ".						
						"WHERE history.order_id = :orderId ".
						"AND o.user_id = :userId ".
						"AND (history.content LIKE '%配送連絡%' OR history.content LIKE '%発送済み%') ".
						"ORDER BY history.order_date DESC ".
						"LIMIT 1";
				break;
			default:
				//何もしない
				$sql = "";
		}
		
		try{
			$results = $this->orderDao->executeQuery($sql, array("orderId" => $values["id"], "userId" => $values["user_id"]));
		}catch(Exception $e){
			return false;
		}
		
		return ($values["order_date"] == $results[0]["order_date"]);		
	}
	
	function convertDateByCuttedTime($time){
		$dateArray = explode("-", date("Y-m-d", $time));
		return mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
	}
}
?>