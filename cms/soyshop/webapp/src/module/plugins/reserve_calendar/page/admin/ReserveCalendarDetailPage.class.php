<?php

class ReserveCalendarDetailPage extends WebPage{

	private $schId;
	private $schedule;
	private $itemId;
	private $reservedList;
	private $reservedCount;
	private $tmpReservedList = array();	//仮登録
	private $tmpReservedCount = 0;
	private $configObj;

	private $config;	//プラグイン側の設定内容

	private $backward;
	private $component;

	private $userDao;

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");

		SOY2::import("module.plugins.reserve_calendar.component.admin.ReservedListComponent");
		SOY2::import("module.plugins.reserve_calendar.component.admin.CancelListComponent");
		SOY2::import("module.plugins.reserve_calendar.component.admin.PriceListComponent");

		/* 共通コンポーネント */
    	SOY2::import("base.site.classes.SOYShop_UserCustomfieldList");
    	SOY2::import("component.UserComponent");
    	SOY2::import("component.backward.BackwardUserComponent");
    	SOY2::import("logic.cart.CartLogic");
    	SOY2::import("logic.mypage.MyPageLogic");

		$this->backward = new BackwardUserComponent();
		$this->component = new UserComponent();

		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		//多言語
		MessageManager::addMessagePath("admin");

		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleDAO");
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
	}

	function doPost(){

		if(soy2_check_token()){

			//管理画面から予約
			if(isset($_POST["Customer"])){
				$this->userDao->begin();

				$userId = self::getUserIdAfterRegister($_POST["Customer"]);
				if(is_null($userId)) return;

				$seat = (isset($_POST["seat"]) && is_numeric($_POST["seat"])) ? (int)$_POST["seat"] : 1;

				//商品情報
				$item = soyshop_get_item_object($this->itemId);

				/** 注文する **/
				$orderId = ReserveCalendarUtil::getSessionValue("order");
				try{
					$order = soyshop_get_order_object($orderId);
					$order->setId(null);
				}catch(Exception $e){
					$order = new SOYShop_Order();
				}

				$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

				$order->setUserId($userId);
				$order->setPrice($item->getPrice() * $seat);	/** @ToDo 消費税も考慮しないと **/
				$order->setStatus(SOYShop_Order::ORDER_STATUS_REGISTERED);
				$order->setOrderDate(time());
				try{
					$orderId = $orderDao->insert($order);
					$order->setId($orderId);
				}catch(Exception $e){
					return;
				}

				/** 予約する **/
				$resDao = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO");
				$res = new SOYShopReserveCalendar_Reserve();
				$res->setScheduleId($this->schId);
				$res->setOrderId($orderId);
				$res->setSeat($seat);
				$res->setTemp(SOYShopReserveCalendar_Reserve::NO_TEMP);
				$res->setReserveDate(time());

				try{
					$resId = $resDao->insert($res);
				}catch(Exception $e){
					return;
					//var_dump($e);
				}

				/** @ToDo 注文詳細 **/
				$itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
				$itemOrder = new SOYShop_ItemOrder();
				$itemOrder->setOrderId($orderId);
				$itemOrder->setItemId($item->getId());
				$itemOrder->setItemCount($seat);
				$itemOrder->setItemPrice($item->getPrice());
				$itemOrder->setTotalPrice($item->getPrice());
				$itemOrder->setItemName($item->getName());
				$itemOrder->setAttributes(array("reserve_id" => $resId));

				try{
					$itemOrderDao->insert($itemOrder);
				}catch(Exception $e){
					var_dump($e);
				}

				$this->userDao->commit();

				//注文番号を作成して更新する
				//注文番号を作成して更新
				$trackingNumber = SOY2Logic::createInstance("logic.order.OrderLogic")->getTrackingNumber($order);
				$order->setTrackingNumber($trackingNumber);
				try{
					$orderDao->update($order);
				}catch(Exception $e){
					//
				}

				//セッションを空にする
				ReserveCalendarUtil::saveSessionValue("user", null);
				ReserveCalendarUtil::saveSessionValue("order", null);

				SOY2PageController::jump("Extension.Detail.reserve_calendar." . $this->schId . "?updated");
			}

			//残席数の変更
			if(isset($_POST["Change"]) && is_numeric($_POST["Change"]["seat"])){
				$this->schedule->setUnsoldSeat((int)$_POST["Change"]["seat"]);
				try{
					SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO")->update($this->schedule);
					SOY2PageController::jump("Extension.Detail.reserve_calendar." . $this->schId . "?updated");
				}catch(Exception $e){
					var_dump($e);
				}
			}
		}

		SOY2PageController::jump("Extension.Detail.reserve_calendar." . $this->schId . "?error");
	}

	private function getUserIdAfterRegister($values){
		try{
			$old = $this->userDao->getByMailAddress($values["mailAddress"]);
			}catch(Exception $e){
			$old = new SOYShop_User();
		}

		$user = SOY2::cast($old, $values);
		$user->setUserType(SOYShop_User::USERTYPE_REGISTER);

		if(is_null($user->getId())){
			try{
				return $this->userDao->insert($user);
			}catch(Exception $e){
				return null;
			}
		}else{
			try{
				$this->userDao->update($user);
			}catch(Exception $e){
				return null;
			}

			return $user->getId();
		}
	}

	function execute(){
		$resLogic = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic");

		$this->schedule = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->getScheduleById($this->schId);
		$this->itemId = $this->schedule->getItemId();
		$this->reservedList = $resLogic->getReservedListByScheduleId($this->schId);
		$this->reservedCount = $resLogic->getReservedCountByScheduleId($this->schId);

		//仮登録
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");
		$this->config = ReserveCalendarUtil::getConfig();
		if(isset($this->config["tmp"]) && $this->config["tmp"] == ReserveCalendarUtil::IS_TMP){
			$this->tmpReservedList = $resLogic->getReservedListByScheduleId($this->schId, true);	//trueで仮登録を取得
			$this->tmpReservedCount = $resLogic->getReservedCountByScheduleId($this->schId, true);
		}

		//キャンセル
		if(isset($_GET["cancel"]) && is_numeric($_GET["cancel"])){
			if(SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.CancelLogic")->cancel($_GET["cancel"])){
				SOY2PageController::jump("Extension.Detail.reserve_calendar." . $this->schId . "?canceled");
			}else{
				SOY2PageController::jump("Extension.Detail.reserve_calendar." . $this->schId . "?error");
			}
			exit;
		}

		//本登録
		if(isset($_GET["reserve"]) && is_numeric($_GET["reserve"])){
			if(SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->registration($_GET["reserve"])){
				SOY2PageController::jump("Extension.Detail.reserve_calendar." . $this->schId . "?registration");
			}else{
				SOY2PageController::jump("Extension.Detail.reserve_calendar." . $this->schId . "?error");
			}
			exit;
		}

		parent::__construct();

		foreach(array("canceled", "error", "registration") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		self::buildScheduleInfoArea();
		self::buildReservedList();
		self::buildTmpReservedList();	//仮予約
		self::buildCanceledList();
		self::buildReserveFormArea();
	}

	private function buildScheduleInfoArea(){

		$item = soyshop_get_item_object($this->itemId);
		$this->addLink("item_name", array(
			"link" => SOY2PageController::createLink("Item.Detail." . $this->itemId),
			"text" => $item->getName()
		));

		$this->addLabel("schedule", array(
			"text" => $this->schedule->getYear() . "-" . $this->schedule->getMonth() . "-" . $this->schedule->getDay() . " " . SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelNameById($this->schedule->getLabelId())
		));

		$this->addLabel("price", array(
			"text" => number_format($this->schedule->getPrice())
		));

		//金額に関する拡張ポイント
		$this->createAdd("price_list", "PriceListComponent", array(
			"list" => self::getExtPrices()
		));


		$this->addLabel("reserved_count", array(
			"text" => $this->reservedCount
		));

		$this->addLabel("seat", array(
			"text" => $this->schedule->getUnsoldSeat()
		));

		//残席数の変更フォーム
		$this->addForm("change_form");

		$this->addInput("unsold_seat_change", array(
			"name" => "Change[seat]",
			"value" => $this->schedule->getUnsoldSeat(),
			"style" => "width:80px;"
		));
	}

	private function getExtPrices(){
		SOYShopPlugin::load("soyshop.add.price.on.calendar");
		$array = SOYShopPlugin::invoke("soyshop.add.price.on.calendar", array(
			"mode" => "list",
			"scheduleId" => $this->schedule->getId()
		))->getList();

		if(!is_array($array) || !count($array)) return array();

		$list = array();
		foreach($array as $values){
			$list[] = $values;
		}
		return $list;
	}

	private function buildReservedList(){
		DisplayPlugin::toggle("has_reserved", ($this->reservedCount > 0));

		$this->createAdd("reserved_list", "ReservedListComponent", array(
			"list" => $this->reservedList,
			"scheduleId" => $this->schId
		));
	}

	private function buildTmpReservedList(){
		DisplayPlugin::toggle("has_tmp_reserved", ($this->tmpReservedCount > 0));

		$this->createAdd("tmp_reserved_list", "ReservedListComponent", array(
			"list" => $this->tmpReservedList,
			"scheduleId" => $this->schId,
			"tempMode" => true
		));
	}

	private function buildCanceledList(){
		$cancelList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.CancelLogic")->getCancelListByScheduleId($this->schId);
		DisplayPlugin::toggle("has_canceled", (count($cancelList) > 0));

		$this->createAdd("cancel_list", "CancelListComponent", array(
			"list" => $cancelList,
		));
	}

	private function buildReserveFormArea(){
		DisplayPlugin::toggle("display_register_form", self::checkDisplayForm());

		//共通フォーム
		$this->component->buildForm($this, self::getUser(), null, UserComponent::MODE_CUSTOM_FORM);

		//残席数
		$unsoldSeat = (isset($this->config["ignore"]) && $this->config["ignore"] == ReserveCalendarUtil::RESERVE_LIMIT_IGNORE) ? 10000 : ($this->schedule->getUnsoldSeat() - $this->reservedCount);
		$this->addInput("seat_input", array(
			"name" => "seat",
			"value" => 1,
			"attr:min" => 1,
			"attr:max" => $unsoldSeat,
			"attr:required" => "required"
		));
	}

	private function getUser(){
		try{
			return $this->userDao->getById(ReserveCalendarUtil::getSessionValue("user"));
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}

	private function checkDisplayForm(){

		//残席数の上限を超えた予約がある場合でもフォームを表示
		if(isset($this->config["ignore"]) && $this->config["ignore"] == ReserveCalendarUtil::RESERVE_LIMIT_IGNORE) return true;

		//スケジュールの日付が既に終了していないか？
		$scheduleDate = mktime(0, 0, 0, $this->schedule->getMonth(), $this->schedule->getDay(), $this->schedule->getYear());
		if($scheduleDate < time() - 24 * 60 * 60) return false;

		//残席数で調べる
		return ($this->reservedCount < $this->schedule->getUnsoldSeat());
	}

	function setDetailId($schId){
		$this->schId = $schId;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
