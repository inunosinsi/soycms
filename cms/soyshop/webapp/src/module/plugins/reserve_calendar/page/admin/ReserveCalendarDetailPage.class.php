<?php

class ReserveCalendarDetailPage extends WebPage{

	private $schId;
	private $schedule;
	private $itemId;
	private $reservedList;
	private $reservedCount;
	private $configObj;

	private $backward;
	private $component;

	private $userDao;

	function __construct(){
		SOY2::import("module.plugins.reserve_calendar.util.ReserveCalendarUtil");

		SOY2::import("module.plugins.reserve_calendar.component.admin.ReservedListComponent");
		SOY2::import("module.plugins.reserve_calendar.component.admin.CancelListComponent");

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

		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_Reserve");
		SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
	}

	function doPost(){

		//if(soy2_check_token()){
			$this->userDao->begin();

			$userId = self::getUserIdAfterRegister($_POST["Customer"]);
			if(is_null($userId)) return;

			//商品情報
			$item = self::getItemById($this->itemId);

			/** 注文する **/
			$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
			$order = new SOYShop_Order();
			$order->setUserId($userId);
			$order->setPrice($item->getPrice());	/** @ToDo 消費税も考慮しないと **/
			try{
				$orderId = $orderDao->insert($order);
			}catch(Exception $e){
				return;
			}

			/** 予約する **/
			$resDao = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO");
			$res = new SOYShopReserveCalendar_Reserve();
			$res->setScheduleId($this->schId);
			$res->setOrderId($orderId);
			$res->setTemp(SOYShopReserveCalendar_Reserve::NO_TEMP);
			$res->setReserveDate(time());

			try{
				$resId = $resDao->insert($res);
			}catch(Exception $e){
				var_dump($e);
			}

			/** @ToDo 注文詳細 **/
			$itemOrderDao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
			$itemOrder = new SOYShop_ItemOrder();
			$itemOrder->setOrderId($orderId);
			$itemOrder->setItemId($item->getId());
			$itemOrder->setItemCount(1);
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

			//セッションを空にする
			ReserveCalendarUtil::saveSessionValue("user", null);

			SOY2PageController::jump("Extension.Detail.reserve_calendar." . $this->schId . "?updated");
		//}

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
		$this->schedule = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Schedule.ScheduleLogic")->getScheduleById($this->schId);
		$this->itemId = $this->schedule->getItemId();
		$this->reservedList = SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.ReserveLogic")->getReservedListByScheduleId($this->schId);
		$this->reservedCount = count($this->reservedList);

		parent::__construct();

		//キャンセル
		if(isset($_GET["cancel"]) && is_numeric($_GET["cancel"])){
			SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Reserve.CancelLogic")->cancel($_GET["cancel"]);
			SOY2PageController::jump("Extension.Detail.reserve_calendar." . $this->schId . "?canceled");
			exit;
		}

		DisplayPlugin::toggle("canceled", isset($_GET["canceled"]));
		DisplayPlugin::toggle("error", isset($_GET["error"]));

		self::buildScheduleInfoArea();
		self::buildReservedList();
		self::buildCanceledList();
		self::buildReserveFormArea();
	}

	private function buildScheduleInfoArea(){

		$this->addLabel("item_name", array(
			"text" => self::getItemById($this->itemId)->getName()
		));

		$this->addLabel("schedule", array(
			"text" => $this->schedule->getYear() . "-" . $this->schedule->getMonth() . "-" . $this->schedule->getDay() . " " . SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelNameById($this->schedule->getLabelId())
		));

		$this->addLabel("reserved_count", array(
			"text" => $this->reservedCount
		));

		$this->addLabel("seat", array(
			"text" => $this->schedule->getUnsoldSeat()
		));
	}

	private function getItemById($itemId){
		try{
			return SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($itemId);
		}catch(Exception $e){
			return new SOYShop_Item();
		}
	}

	private function buildReservedList(){
		DisplayPlugin::toggle("has_reserved", ($this->reservedCount > 0));

		$this->createAdd("reserved_list", "ReservedListComponent", array(
			"list" => $this->reservedList,
			"scheduleId" => $this->schId
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
	}

	private function getUser(){
		try{
			return $this->userDao->getById(ReserveCalendarUtil::getSessionValue("user"));
		}catch(Exception $e){
			return new SOYShop_User();
		}
	}

	private function checkDisplayForm(){

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
