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
		WebPage::WebPage();

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
		if($end > 0 && $start == 0)$start = 1;

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


		//注文結果を出力
		$this->createAdd("order_list", "_common.Order.OrderListComponent", array(
			"list" => $orders
		));

		$this->addModel("order_exists", array(
			"visible" => (count($orders) > 0)
		));

		$this->addModel("no_result", array(
			"visible" => (count($orders) < 1 && !empty($search))
		));

		$this->addLink("reset_link", array(
			"link" => SOY2PageController::createLink("Order") . "?reset",
			"visible" => (!empty($search))
		));

		/* 出力用 */
		$moduleList = $this->getExportModuleList();
		$this->createAdd("module_list", "ExportModuleList", array(
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

	private $orderDateStart;
	private $orderDateEnd;

	private $trackingNumber;
	private $userName;
	private $userReading;
	private $itemCode;

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
			"selected" => $this->getSOYShop_OrderStatus()
		));

		$this->addSelect("payment_status_list", array(
			"name" => "search[paymentStatus]",
			"options" => SOYShop_Order::getPaymentStatusList(),
			"selected" => $this->getPaymentStatus()
		));

		$this->addInput("order_date_start", array(
			"name" => "search[orderDateStart]",
			"value" => $this->getOrderDateStart(),
		));

		$this->addInput("order_date_end", array(
			"name" => "search[orderDateEnd]",
			"value" => $this->getOrderDateEnd(),
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

		$this->addInput("order_item_code", array(
			"name" => "search[itemCode]",
			"value" => $this->getItemCode()
		));

		parent::execute();
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
	function getItemList() {
		return $this->itemList;
	}
	function setItemList($itemList) {
		$this->itemList = $itemList;
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
}

class ExportModuleList extends HTMLList{

	private $exportPageLink;

	protected function populateItem($entity,$key){
		$this->addInput("module_id", array(
			/*"label" => "選択する",*/
			"name" => "plugin",
			"value" => $key,
		));

		$this->addLabel("export_title", array(
			"text" => $entity["title"],
		));

		$this->addLabel("export_description", array(
			"html" => $entity["description"],
			"visible" => (strlen($entity["description"]) > 0)
		));
	}

	function getExportPageLink() {
		return $this->exportPageLink;
	}
	function setExportPageLink($exportPageLink) {
		$this->exportPageLink = $exportPageLink;
	}
}
?>