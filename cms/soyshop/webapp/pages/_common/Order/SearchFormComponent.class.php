<?php

/**
 * フォームを受け取るクラス
 */
class SearchFormComponent extends SOYBodyComponentBase{

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

	private $orderMemo;
	private $orderMemoAndOr;
	private $orderComment;
	private $orderCommentAndOr;

	private $userName;
	private $userReading;
	private $userMailAddress;
	private $userGender = array();
	private $userBirthday = array();
	private $userTelephoneNumber = array();

	private $itemName;
	private $itemCode;

	private $paymentMethod = array();
	private $customs = array();	//プラグインから出力される項目用

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
		$list = SOYShop_Order::getOrderStatusList($checkPreOrder);
		$this->addSelect("status_list", array(
			"name" => "search[orderStatus]",
			"options" => $list,
			"selected" => (is_null($this->getNoDelivery()) || $this->getNoDelivery() != 1) ? $this->getSOYShop_OrderStatus() : false
		));

		//複数選択モードを表示
		$isStatusMode = (int)$config->getMultiSelectStatusMode();
		DisplayPlugin::toggle("status_single_select_mode", $isStatusMode === 0);
		DisplayPlugin::toggle("status_multi_select_mode", $isStatusMode === 1);

		$this->addCheckBox("no_delivery", array(
			"name" => "search[noDelivery]",
			"value" => 1,
			"selected" => ($this->getNoDelivery() == 1),
			"label" => "未発送の注文"
		));

		//複数選択モード
		$html = array();
		foreach($list as $i => $label){
			if(is_array($this->getSOYShop_OrderStatus()) && is_numeric(array_search($i, $this->getSOYShop_OrderStatus()))){
				$html[] = "<label><input type=\"checkbox\" name=\"search[orderStatus][]\" value=\"" . $i ."\" checked=\"checked\">" . $label . "</label>";
			}else{
				$html[] = "<label><input type=\"checkbox\" name=\"search[orderStatus][]\" value=\"" . $i ."\">" . $label . "</label>";
			}
		}

		$this->addLabel("status_multi", array(
			"html" => implode("&nbsp;", $html)
		));

		$list = SOYShop_Order::getPaymentStatusList();
		$this->addSelect("payment_status_list", array(
			"name" => "search[paymentStatus]",
			"options" => $list,
			"selected" => (is_null($this->getNoPayment()) || $this->getNoPayment() != 1) ? $this->getPaymentStatus() : false
		));

		$this->addCheckBox("no_payment", array(
			"name" => "search[noPayment]",
			"value" => 1,
			"selected" => ($this->getNoPayment() == 1),
			"label" => "未支払の注文"
		));

		//複数選択モード
		$html = array();
		foreach($list as $i => $label){
			if(is_array($this->getPaymentStatus()) && is_numeric(array_search($i, $this->getPaymentStatus()))){
				$html[] = "<label><input type=\"checkbox\" name=\"search[paymentStatus][]\" value=\"" . $i ."\" checked=\"checked\">" . $label . "</label>";
			}else{
				$html[] = "<label><input type=\"checkbox\" name=\"search[paymentStatus][]\" value=\"" . $i ."\">" . $label . "</label>";
			}
		}

		$this->addLabel("payment_multi", array(
			"html" => implode("&nbsp;", $html)
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
			"style" => "width:35%;ime-mode:inactive;",
		));
		$this->addInput("order_user_birth_date_month", array(
			"name" => "search[userBirthday][]",
			"value" => (isset($birthArray[1])) ? $birthArray[1] : "",
			"style" => "width:25%;ime-mode:inactive;",
		));
		$this->addInput("order_user_birth_date_day", array(
			"name" => "search[userBirthday][]",
			"value" => (isset($birthArray[2])) ? $birthArray[2] : "",
			"style" => "width:25%;ime-mode:inactive;",
		));

		//電話番号検索	携帯やFAX番号も同時検索
		$tellArray = $this->getUserTelephoneNumber();
		$this->addInput("order_user_telephone_number_area_code", array(
			"name" => "search[userTelephoneNumber][]",
			"value" => (isset($tellArray[0])) ? $tellArray[0] : "",
			"style" => "width:15%;ime-mode:inactive;",
		));

		$this->addInput("order_user_telephone_number_1", array(
			"name" => "search[userTelephoneNumber][]",
			"value" => (isset($tellArray[1])) ? $tellArray[1] : "",
			"style" => "width:15%;ime-mode:inactive;",
		));

		$this->addInput("order_user_telephone_number_2", array(
			"name" => "search[userTelephoneNumber][]",
			"value" => (isset($tellArray[2])) ? $tellArray[2] : "",
			"style" => "width:15%;ime-mode:inactive;",
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

		$this->addInput("order_memo", array(
			"name" => "search[orderMemo]",
			"value" => $this->getOrderMemo(),
			"style" => "width:80%;",
			"attr:placeholder" => "スペース区切りで複数ワードで検索できます。"
		));

		$this->addCheckBox("order_memo_and", array(
			"name" => "search[orderMemoAndOr]",
			"value" => 0,
			"selected" => (is_null($this->getOrderMemoAndOr()) || (int)$this->getOrderMemoAndOr() === 0),
			"label" => "AND"
		));

		$this->addCheckBox("order_memo_or", array(
			"name" => "search[orderMemoAndOr]",
			"value" => 1,
			"selected" => ((int)$this->getOrderMemoAndOr() === 1),
			"label" => "OR"
		));

		$this->addInput("order_comment", array(
			"name" => "search[orderComment]",
			"value" => $this->getOrderComment(),
			"style" => "width:80%;",
			"attr:placeholder" => "スペース区切りで複数ワードで検索できます。"
		));

		$this->addCheckBox("order_comment_and", array(
			"name" => "search[orderCommentAndOr]",
			"value" => 0,
			"selected" => (is_null($this->getOrderCommentAndOr()) || (int)$this->getOrderCommentAndOr() === 0),
			"label" => "AND"
		));

		$this->addCheckBox("order_comment_or", array(
			"name" => "search[orderCommentAndOr]",
			"value" => 1,
			"selected" => ((int)$this->getOrderCommentAndOr() === 1),
			"label" => "OR"
		));

		$this->createAdd("custom_search_item_list", "_common.Order.CustomSearchItemListComponent", array(
			"list" => self::getCustomSearchItems()
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

	private function getCustomSearchItems(){
		//検索フォームの拡張ポイント
		SOYShopPlugin::load("soyshop.order.search");
		$items = SOYShopPlugin::invoke("soyshop.order.search", array(
			"mode" => "form",
			"params" => $this->getCustoms()
		))->getSearchItems();

		//再配列
		$list = array();
		foreach($items as $item){
			if(is_null($item)) continue;
			$key = key($item);
			if($key == "label"){
				$list[] = $item;
			//複数の項目が入っている
			}else{
				foreach($item as $v){
					$list[] = $v;
				}
			}
		}

		return $list;
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
		return SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->get();
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
	function getUserTelephoneNumber(){
		return $this->userTelephoneNumber;
	}
	function setUserTelephoneNumber($userTelephoneNumber){
		$this->userTelephoneNumber = $userTelephoneNumber;
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

	function getOrderMemo(){
		return $this->orderMemo;
	}
	function setOrderMemo($orderMemo){
		$this->orderMemo = $orderMemo;
	}

	function getOrderMemoAndOr(){
		return $this->orderMemoAndOr;
	}
	function setOrderMemoAnd($orderMemoAndOr){
		$this->orderMemoAndOr = $orderMemoAndOr;
	}

	function getOrderComment(){
		return $this->orderComment;
	}
	function setOrderComment($orderComment){
		$this->orderComment = $orderComment;
	}

	function getOrderCommentAndOr(){
		return $this->orderCommentAndOr;
	}
	function setOrderCommentAndOr($orderCommentAndOr){
		$this->orderCommentAndOr = $orderCommentAndOr;
	}

	function getCustoms(){
		return $this->customs;
	}
	function setCustoms($customs){
		$this->customs = $customs;
	}
}
