<?php
/**
 * @class IndexPage
 * @date 2008-10-29T18:46:55+09:00
 * @author SOY2HTMLFactory
 */
SOY2::import("domain.config.SOYShop_ShopConfig");
class IndexPage extends WebPage{

	function doPost(){

		if(!soy2_check_token())SOY2PageController::jump("Order");

		$orders = $_POST["orders"];
		$logic = SOY2Logic::createInstance("logic.order.OrderLogic");

		if(isset($_POST["do_change_order_status"])){
			$status = $_POST["do_change_order_status"];
			$logic->changeOrderStatus($orders, $status);
		}

		if(isset($_POST["do_change_payment_status"])){
			$status = $_POST["do_change_payment_status"];
			$logic->changePaymentStatus($orders, $status);
		}

		SOY2PageController::jump("Order?updated");
		exit;
	}

	function __construct($args){
		parent::__construct();

		//検索条件のリセット
		if(isset($_GET["reset"])){
			$this->setParameter("page", 1);
			$this->setParameter("sort", null);
			$this->setParameter("search", array());
		}

		/*引数など取得*/
		//表示件数
		$limit = 15;
		$page = (isset($args[0])) ? (int)$args[0] : $this->getParameter("page");
		if(array_key_exists("page", $_GET)) $page = $_GET["page"];
		if(array_key_exists("sort", $_GET) OR array_key_exists("search", $_GET)) $page = 1;
		$page = max(1, $page);

		$offset = ($page - 1) * $limit;

		//表示順
		$sort = $this->getParameter("sort");
		$this->setParameter("page", $page);

		//検索条件
		$search = $this->getParameter("search");
		//$search = (isset($_GET["search"])) ? $_GET["search"] : array();
		//検索用のロジック作成
		$searchLogic = SOY2Logic::createInstance("logic.order.SearchOrderLogic");

		//フォームの作成
		$form = $this->buildSearchForm($search);
		$form = (array)SOY2::cast("object",$form);//再変換をかける

		//検索条件の投入と検索実行
		$searchLogic->setSearchCondition($form);
		$searchLogic->setLimit($limit);
		$searchLogic->setOffset($offset);
		$searchLogic->setOrder($sort);
		$total = $searchLogic->getTotalCount();
		$orders = $searchLogic->getOrders();

		//表示順リンク
		$this->buildSortLink($searchLogic,$sort);

		//ページャーの作成
		$start = $offset + 1;
		$end = $offset + count($orders);
		if($end > 0 && $start == 0) $start = 1;

		$pager = SOY2Logic::createInstance("logic.pager.PagerLogic");
		$pager->setPageURL("Order");
		$pager->setPage($page);
		$pager->setStart($start);
		$pager->setEnd($end);
		$pager->setTotal($total);
		$pager->setLimit($limit);
		//$pager->setQuery(array("search" => $search));

		$pager->buildPager($this);

		//操作周り
		$this->addForm("order_form");

		//管理画面から注文(argsがある場合は、自動でユーザ番号を入れておきたい)
		$this->addLink("order_link", array(
			"link" => SOY2PageController::createLink("Order.Register")
		));

		//項目の表示に関して
		$items = SOYShop_ShopConfig::load()->getOrderItemConfig();
		$itmCnt = 0;
		foreach($items as $key => $b){
			if($b) $itmCnt++;

			$this->addModel($key . "_show", array(
				"visible" => $b
			));

			$this->addModel($key . "_form_show", array(
				"visible" => $b
			));
		}

		foreach(range(0,1) as $i){
			$this->addModel("col_count_" . $i, array(
				"attr:colspan" => $itmCnt + 2
			));
		}


		//注文結果を出力
		$this->createAdd("order_list", "_common.Order.OrderListComponent", array(
			"list" => $orders
		));

		$orderCnt = count($orders);
		$this->addModel("order_exists", array(
			"visible" => ($orderCnt > 0)
		));

		$this->addModel("no_result", array(
			"visible" => ($orderCnt === 0 && !empty($search))
		));

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Order") . "?reset",
			"visible" => (!empty($search))
		));

		/* 出力用 */
		$moduleList = $this->getExportModuleList();
		$this->createAdd("module_list", "_common.Order.ExportModuleListComponent", array(
			"list" => $moduleList
		));

		$this->addForm("export_form", array(
			"action" => SOY2PageController::createLink("Order.Export")
		));

		$this->addInput("query", array(
			"name" => "search",
			"value" => (isset($_GET["search"])) ? http_build_query($_GET["search"]) : ""
		));
	}

	/**
	 * 検索フォームを作成する
	 */
	function buildSearchForm($search){

		$obj = (object)$search;

		$form = $this->create("search_form", "SearchForm");

		SOY2::cast($form, $obj);

		$this->add("search_form", $form);

		return $form;
	}

	function getParameter($key){
		if(array_key_exists($key, $_GET)){
			$value = $_GET[$key];
			$this->setParameter($key, $value);
		}else{
			$value = SOY2ActionSession::getUserSession()->getAttribute("Order.Search:" . $key);
		}
		return $value;
	}
	function setParameter($key, $value){
		SOY2ActionSession::getUserSession()->setAttribute("Order.Search:" . $key, $value);
	}

	function buildSortLink(SearchOrderLogic $logic, $sort){

		$link = SOY2PageController::createLink("Order");

		$sorts = $logic->getSorts();

		foreach($sorts as $key => $value){

			$text = (!strpos($key,"_desc")) ? "▲" : "▼";
			$title = (!strpos($key,"_desc")) ? "昇順" : "降順";

			$this->addLink("sort_${key}", array(
				"text" => $text,
				"link" => $link . "?sort=" . $key,
				"title" => $title,
				"class" => ($sort === $key) ? "sorter_selected" : "sorter"
			));
		}
	}

	function getExportModuleList(){
		SOYShopPlugin::load("soyshop.order.export");

		$delegate = SOYShopPlugin::invoke("soyshop.order.export", array(
			"mode" => "list"
		));

		$list = $delegate->getList();
		DisplayPlugin::toggle("export_module_menu", (count($list) > 0));

		return $list;
	}

	function getCSS(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "tools/soy2_date_picker.css"
		);
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "tools/soy2_date_picker.pack.js"
		);
	}
}

/**
 * フォームを受け取るクラス
 */
class SearchForm extends SOYBodyComponentBase{

	private $userId;
	private $userArea;
	private $orderStatus;
	private $paymentStatus;

	private $noDelivery;
	private $noPayment;

	private $totalPriceMin;
	private $totalPriceMax;

	private $orderDateStart;
	private $orderDateEnd;

	private $updateDateStart;
	private $updateDateEnd;

	private $trackingNumber;
	private $orderId;
	private $orderIdStart;
	private $orderIdEnd;

	private $userName;
	private $userReading;
	private $userMailAddress;
	private $userGender = array();
	private $userBirthday = array();

	private $itemName;
	private $itemCode;

	private $paymentMethod = array();

	/**
	 * フォームの作成
	 */
	function execute(){

		$config = SOYShop_ShopConfig::load();

		//お客様の住所
		$this->addSelect("order_user_area", array(
			"name" => "search[userArea]",
			"options" => SOYShop_Area::getAreas(),
			"selected" => $this->getUserArea()
		));

		//仮登録(登録エラー)の注文を検索できるようにする
		$checkPreOrder = ($config->getCheckPreOrder() == 1) ? true : false;
		$this->addSelect("status_list", array(
			"name" => "search[orderStatus]",
			"options" => SOYShop_Order::getOrderStatusList($checkPreOrder),
			"selected" => (is_null($this->getNoDelivery()) || $this->getNoDelivery() != 1) ? $this->getSOYShop_OrderStatus() : false
		));


		$this->addCheckBox("no_delivery", array(
			"name" => "search[noDelivery]",
			"value" => 1,
			"selected" => ($this->getNoDelivery() == 1),
			"label" => "未発送の注文"
		));

		$this->addSelect("payment_status_list", array(
			"name" => "search[paymentStatus]",
			"options" => SOYShop_Order::getPaymentStatusList(),
			"selected" => (is_null($this->getNoPayment()) || $this->getNoPayment() != 1) ? $this->getPaymentStatus() : false
		));

		$this->addCheckBox("no_payment", array(
			"name" => "search[noPayment]",
			"value" => 1,
			"selected" => ($this->getNoPayment() == 1),
			"label" => "未支払の注文"
		));

		$this->addInput("total_price_min", array(
			"name" => "search[totalPriceMin]",
			"value" => $this->getTotalPriceMin()
		));

		$this->addInput("total_price_max", array(
			"name" => "search[totalPriceMax]",
			"value" => $this->getTotalPriceMax()
		));

		$this->addInput("order_date_start", array(
			"name" => "search[orderDateStart]",
			"value" => $this->getOrderDateStart(),
		));

		$this->addInput("order_date_end", array(
			"name" => "search[orderDateEnd]",
			"value" => $this->getOrderDateEnd(),
		));

		$this->addInput("update_date_start", array(
			"name" => "search[updateDateStart]",
			"value" => $this->getUpdateDateStart(),
		));

		$this->addInput("update_date_end", array(
			"name" => "search[updateDateEnd]",
			"value" => $this->getUpdateDateEnd(),
		));

		$this->addInput("order_id", array(
			"name" => "search[orderId]",
			"value" => $this->getOrderId()
		));

		$this->addInput("order_id_start", array(
			"name" => "search[orderIdStart]",
			"value" => $this->getOrderIdStart(),
			"style" => "width:80px;"
		));

		$this->addInput("order_id_end", array(
			"name" => "search[orderIdEnd]",
			"value" => $this->getOrderIdEnd(),
			"style" => "width:80px;"
		));

		$this->addInput("order_tracking_number", array(
			"name" => "search[trackingNumber]",
			"value" => $this->getTrackingNumber()
		));

		$this->addInput("order_user_name", array(
			"name" => "search[userName]",
			"value" => $this->getUserName()
		));

		$this->addInput("order_user_reading", array(
			"name" => "search[userReading]",
			"value" => $this->getUserReading()
		));

		$this->addInput("order_user_mail_address", array(
			"name" => "search[userMailAddress]",
			"value" => $this->getUserMailAddress()
		));

		SOY2::import("domain.user.SOYShop_User");
		$this->addCheckBox("order_user_gender_male", array(
			"name" => "search[userGender][]",
			"value" => SOYShop_User::USER_SEX_MALE,
			"selected" => (array_search(SOYShop_User::USER_SEX_MALE, $this->getUserGender()) !== false),
			"label" => "男性"
		));

		$this->addCheckBox("order_user_gender_female", array(
			"name" => "search[userGender][]",
			"value" => SOYShop_User::USER_SEX_FEMALE,
			"selected" => (array_search(SOYShop_User::USER_SEX_FEMALE, $this->getUserGender()) !== false),
			"label" => "女性"
		));

		$birthArray = $this->getUserBirthday();
		$this->addInput("order_user_birth_date_year", array(
			"name" => "search[userBirthday][]",
			"value" => (isset($birthArray[0])) ? $birthArray[0] : "",
			"size" => "5"
		));
		$this->addInput("order_user_birth_date_month", array(
			"name" => "search[userBirthday][]",
			"value" => (isset($birthArray[1])) ? $birthArray[1] : "",
			"size" => "3",
		));
		$this->addInput("order_user_birth_date_day", array(
			"name" => "search[userBirthday][]",
			"value" => (isset($birthArray[2])) ? $birthArray[2] : "",
			"size" => "3",
		));

		$this->addInput("order_item_name", array(
			"name" => "search[itemName]",
			"value" => $this->getItemName()
		));

		$this->addInput("order_item_code", array(
			"name" => "search[itemCode]",
			"value" => $this->getItemCode()
		));

		//支払い方法のチェックボックス
		$this->addLabel("order_payment_checkboxes", array(
			"html" => self::getPaymentCheckboxesHTML()
		));

		parent::execute();
	}

	private function getPaymentCheckboxesHTML(){
		SOYShopPlugin::load("soyshop.payment");
		SOY2::import("logic.cart.CartLogic");

		//実行
		$paymentList = SOYShopPlugin::invoke("soyshop.payment", array(
			"mode" => "search",
			"cart" => CartLogic::getCart()
		))->getList();

		if(!count($paymentList)) return "";

		$html = array();
		foreach($paymentList as $key => $p){
			$checked = "";
			if(array_search($key, $this->getPaymentMethod()) !== false){
				$checked = " checked=\"checked\"";
			}

			$html[] = "<label><input type=\"checkbox\" name=\"search[paymentMethod][]\" value=\"" . $key . "\" " . $checked . ">" . $p . "</label>";
		}

		return implode("\n", $html);
	}

	function getUserArea(){
		return $this->userArea;
	}
	function setUserArea($userArea){
		$this->userArea = $userArea;
	}

	function getSOYShop_OrderStatus() {
		return $this->orderStatus;
	}
	function setSOYShop_OrderStatus($orderStatus) {
		$this->orderStatus = $orderStatus;
	}
	function getPaymentStatus() {
		return $this->paymentStatus;
	}
	function setPaymentStatus($paymentStatus) {
		$this->paymentStatus = $paymentStatus;
	}

	function getNoDelivery(){
		return $this->noDelivery;
	}
	function setNoDelivery($noDelivery){
		$this->noDelivery = $noDelivery;
	}

	function getNoPayment(){
		return $this->noPayment;
	}
	function setNoPayment($noPayment){
		$this->noPayment = $noPayment;
	}

	function getItemList() {
		return $this->itemList;
	}
	function setItemList($itemList) {
		$this->itemList = $itemList;
	}

	function getTotalPriceMin(){
		return $this->totalPriceMin;
	}
	function setTotalPriceMin($totalPriceMin){
		$this->totalPriceMin = $totalPriceMin;
	}
	function getTotalPriceMax(){
		return $this->totalPriceMax;
	}
	function setTotalPriceMax($totalPriceMax){
		$this->totalPriceMax = $totalPriceMax;
	}

	function getOrderDateStart() {
		return $this->orderDateStart;
	}
	function setSOYShop_OrderDateStart($orderDateStart) {
		$this->orderDateStart = $orderDateStart;
	}
	function getOrderDateEnd() {
		return $this->orderDateEnd;
	}
	function setSOYShop_OrderDateEnd($orderDateEnd) {
		$this->orderDateEnd = $orderDateEnd;
	}

	function getUpdateDateStart() {
		return $this->updateDateStart;
	}
	function setSOYShop_UpdateDateStart($updateDateStart) {
		$this->updateDateStart = $updateDateStart;
	}
	function getUpdateDateEnd() {
		return $this->updateDateEnd;
	}
	function setSOYShop_UpdateDateEnd($updateDateEnd) {
		$this->updateDateEnd = $updateDateEnd;
	}

	/**
	 * 	商品の配列を取得
	 *
	 *  @return arrray Item
	 */
	function getItemObjectList(){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$items = $dao->get();

		return $items;
	}

	/**
	 * ?tracking_number=99-9999-9999 のようにして単発の検索ができるようにしてある
	 */
	function getTrackingNumber() {
		if(empty($this->trackingNumber) && isset($_GET["tracking_number"])){
			return $_GET["tracking_number"];
		}

		return $this->trackingNumber;
	}
	function setTrackingNumber($trackingNumber) {
		$this->trackingNumber = mb_convert_kana($trackingNumber, "a");
	}

	function getOrderId(){
		return $this->orderId;
	}
	function setOrderId($orderId){
		$this->orderId = $orderId;
	}

	function getOrderIdStart(){
		return $this->orderIdStart;
	}
	function setOrderIdStart($orderIdStart){
		$this->orderIdStart = $orderIdStart;
	}

	function getOrderIdEnd(){
		return $this->orderIdEnd;
	}
	function setOrderIdEnd($orderIdEnd){
		$this->orderIdEnd = $orderIdEnd;
	}

	function getOrderStatus() {
		return $this->orderStatus;
	}
	function setOrderStatus($orderStatus) {
		$this->orderStatus = $orderStatus;
	}
	function setOrderDateStart($orderDateStart) {
		$this->orderDateStart = $orderDateStart;
	}
	function setOrderDateEnd($orderDateEnd) {
		$this->orderDateEnd = $orderDateEnd;
	}
	function setUpdateDateStart($updateDateStart) {
		$this->updateDateStart = $updateDateStart;
	}
	function setUpdateDateEnd($updateDateEnd) {
		$this->updateDateEnd = $updateDateEnd;
	}
	function getUserName() {
		return $this->userName;
	}
	function setUserName($userName) {
		$this->userName = $userName;
	}
	function getUserReading(){
		return $this->userReading;
	}
	function setUserReading($userReading){
		$this->userReading = $userReading;
	}
	function getUserMailAddress(){
		return $this->userMailAddress;
	}
	function setUserMailAddress($userMailAddress){
		$this->userMailAddress = $userMailAddress;
	}
	function getUserGender(){
		return $this->userGender;
	}
	function setUserGender($userGender){
		$this->userGender = $userGender;
	}
	function getUserBirthday(){
		return $this->userBirthday;
	}
	function setUserBirthday($userBirthday){
		$this->userBirthday = $userBirthday;
	}

	function getItemName() {
		if(empty($this->itemName) && isset($_GET["itemName"])){
			return $_GET["itemName"];
		}
		return $this->itemName;
	}
	function setItemName($itemName) {
		$this->itemName = $itemName;
	}

	function getItemCode() {
		if(empty($this->itemCode) && isset($_GET["itemCode"])){
			return $_GET["itemCode"];
		}
		return $this->itemCode;
	}
	function setItemCode($itemCode) {
		$this->itemCode = $itemCode;
	}

	function getUserId() {
		if(empty($this->userId) && isset($_GET["userId"])){
			return $_GET["userId"];
		}
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
	}

	function getPaymentMethod(){
		return $this->paymentMethod;
	}
	function setPaymentMethod($paymentMethod){
		$this->paymentMethod = $paymentMethod;
	}
}
