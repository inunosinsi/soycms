<?php
SOY2::import("domain.user.SOYShop_User");
SOY2::import("domain.order.SOYShop_ItemModule");
SOYShopPlugin::load("soyshop.order.module");
SOYShopPlugin::load("soyshop.order.status");
SOYShopPlugin::load("soyshop.order.status.sort");

/**
 * @table soyshop_order
 */
class SOYShop_Order {

	//注文ステータス
	const ORDER_STATUS_INVALID = 0;		//仮登録した注文をそのまま破棄(クレジットカード周辺)
	const ORDER_STATUS_INTERIM = 1;		//仮登録
	const ORDER_STATUS_REGISTERED = 2; 	//新規受付
	const ORDER_STATUS_RECEIVED = 3; 	//受付完了
	const ORDER_STATUS_SENDED = 4; 		//発送済み
	const ORDER_STATUS_CANCELED = 5; 	//キャンセル

	//支払ステータス
	const PAYMENT_STATUS_WAIT = 1; //支払待ち
	const PAYMENT_STATUS_CONFIRMED = 2;	//支払確認済み
	const PAYMENT_STATUS_ERROR = 3;	//入金エラー
	const PAYMENT_STATUS_DIRECT = 4; //直接支払
	const PAYMENT_STATUS_REFUNDED = 5;	//返金済み

	//メール送信のタイプ
	const SENDMAIL_TYPE_ORDER = "order";		//注文受付メール
	const SENDMAIL_TYPE_CONFIRM = "confirm"; 	//注文確定メール
	const SENDMAIL_TYPE_PAYMENT = "payment";	//支払確認メール
	const SENDMAIL_TYPE_DELIVERY = "delivery";	//配送確認メール
	const SENDMAIL_TYPE_OTHER = "other";		//その他のメール

	/**
	 * @id
	 */
    private $id;

    /**
     * @column order_date
     */
    private $orderDate;

    /**
     * @column tracking_number
     */
   	private $trackingNumber;

    /**
     * @column price
     */
    private $price = 0;

    /**
     * @column order_status
     */
    private $status = SOYShop_Order::ORDER_STATUS_REGISTERED;

    /**
     * @column payment_status
     */
    private $paymentStatus = SOYShop_Order::PAYMENT_STATUS_WAIT;

    /**
     * @column user_id
     */
    private $userId;

    /**
     * 送付先
     */
    private $address;

    /**
     * 請求先
     * @column claimed_address
     */
    private $claimedAddress;

    /**
     * メール送信状況
     * @column mail_status
     */
    private $mailStatus;

    /**
     * @no_persistent
     */
    private $items = array();

	/**
	 * 注文に適用されたモジュール SOYShop_ItemModule
	 */
    private $modules = array();

	/**
	 * 注文に関する属性値
	 * array(
	 *     キー => array("name" => 名称, "value" => 値),
	 *     memo => array("name" => "備考", "value" => "注文時の備考"),
	 * )
	 */
    private $attributes = array();

    /**
     * 注文の状態のテキストを取得
     */
    function getOrderStatusText(){
    	$texts = $this->getOrderStatusList(true);
    	$status = $this->getStatus() + 0;
    	if(isset($texts[$status])) {
	    	return $texts[$status];
    	} else {
    		return false;
    	}
    }

    /**
     * 支払い状況のテキストを取得
     */
    function getPaymentStatusText(){
    	$texts = $this->getPaymentStatusList();
		$status = $this->getPaymentStatus() + 0;
    	if(isset($texts[$status])){
	    	return $texts[$status];
    	} else {
    		return false;
    	}
    }

    /**
     * テーブル名を取得
     */
    public static function getTableName(){
    	return "soyshop_order";
    }

    /**
     * 注文ステータスの配列を取得
     */
    public static function getOrderStatusList($all = false){
		static $list;
		if(is_null($list)){
			$list = array(
	    		//注文ステータス
				SOYShop_Order::ORDER_STATUS_INTERIM => "仮登録",		//仮登録は管理画面からは見えない
				SOYShop_Order::ORDER_STATUS_REGISTERED => "新規受付",
				SOYShop_Order::ORDER_STATUS_RECEIVED => "受付完了",
				SOYShop_Order::ORDER_STATUS_SENDED => "発送済み",
			);

			//拡張ポイント
			$adds = SOYShopPlugin::invoke("soyshop.order.status", array(
				"mode" => "status",
			))->getList();

			if(is_array($adds) && count($adds)){
				foreach($adds as $add){
					if(!is_array($add) || !count($add)) continue;
					foreach($add as $key => $values){
						if(isset($add[$key]["label"])) $list[$key] = $add[$key]["label"];
					}
				}
			}

			ksort($list);

			//プラグインで状態の並び替え
			$sort = SOYShopPlugin::invoke("soyshop.order.status.sort", array(
				"mode" => "status",
			))->getSort();

			if(is_array($sort) && count($sort)){
				$tmps = array();	//並び順に合わせて格納

				//仮登録がある場合は必ず先頭にする
				if(isset($list[self::ORDER_STATUS_INTERIM])){
					$tmps[self::ORDER_STATUS_INTERIM] = $list[self::ORDER_STATUS_INTERIM];
					unset($list[self::ORDER_STATUS_INTERIM]);
				}

				foreach($sort as $s){
					if(isset($list[$s])){
						$tmps[$s] = $list[$s];
						unset($list[$s]);
					}
				}

				//プラグインで設定していないステータスコードがあった場合は末尾に追加する
				if(isset($list) && count($list)){
					foreach($list as $key => $v){
						$tmps[$key] = $v;
					}
				}
				$list = $tmps;
			}

			//キャンセルは最後
			$list[SOYShop_Order::ORDER_STATUS_CANCELED] = "キャンセル";
		}

		//allがtrueの場合はそのまま出力
		if($all) return $list;

    	//allがfalseの場合は場合は仮登録分のみ削除して出力
		$tmps = $list;
		unset($tmps[SOYShop_Order::ORDER_STATUS_INTERIM]);
		return $tmps;
    }

    /**
     * 支払ステータスのリストを取得
     */
    public static function getPaymentStatusList(){
		static $list;	//2度読み込むことがないのでstatic
		if(is_null($list)){
			$list = array(
				//支払ステータス
				SOYShop_Order::PAYMENT_STATUS_WAIT => "支払待ち",
				SOYShop_Order::PAYMENT_STATUS_CONFIRMED => "支払確認済み",
				SOYShop_Order::PAYMENT_STATUS_ERROR => "入金エラー",
				SOYShop_Order::PAYMENT_STATUS_DIRECT => "直接支払",
				SOYShop_Order::PAYMENT_STATUS_REFUNDED => "返金済み",
	    	);

			//拡張ポイント
			$adds = SOYShopPlugin::invoke("soyshop.order.status", array(
				"mode" => "payment",
			))->getList();

			if(is_array($adds) && count($adds)){
				foreach($adds as $add){
					if(!is_array($add) || !count($add)) continue;
					foreach($add as $key => $values){
						if(isset($add[$key]["label"])) $list[$key] = $add[$key]["label"];
					}
				}
			}

			ksort($list);

			//プラグインで状態の並び替え
			$sort = SOYShopPlugin::invoke("soyshop.order.status.sort", array(
				"mode" => "payment",
			))->getSort();

			if(is_array($sort) && count($sort)){
				$tmps = array();	//並び順に合わせて格納

				foreach($sort as $s){
					if(isset($list[$s])){
						$tmps[$s] = $list[$s];
						unset($list[$s]);
					}
				}

				//プラグインで設定していないステータスコードがあった場合は末尾に追加する
				if(isset($list) && count($list)){
					foreach($list as $key => $v){
						$tmps[$key] = $v;
					}
				}
				$list = $tmps;
			}
		}

		return $list;
    }

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getOrderDate() {
    	return $this->orderDate;
    }
    function setOrderDate($orderDate) {
    	$this->orderDate = $orderDate;
    }
    function getPrice() {
    	return $this->price;
    }
    function setPrice($price) {
    	$this->price = $price;
    }
    function getStatus() {
    	return $this->status;
    }
    function setStatus($status) {
    	$this->status = $status;
    }
    function getPaymentStatus() {
    	return $this->paymentStatus;
    }
    function setPaymentStatus($paymentStatus) {
    	$this->paymentStatus = $paymentStatus;
    }
    function getUserId() {
    	return $this->userId;
    }
    function setUserId($userId) {
    	$this->userId = $userId;
    }
    function getItems() {
    	return $this->items;
    }
    function setItems($items) {
    	$this->items = $items;
    }
    function getModules() {
    	return $this->modules;
    }
    function setModules($modules) {
    	if(is_array($modules)) $modules = soy2_serialize($modules);
		if(is_string($modules) && $modules == "Array") $modules = null;
    	$this->modules = $modules;
    }
    function getModuleList(){
    	$res = soy2_unserialize($this->modules);
    	return (is_array($res)) ? $res : array();
    }
    function getAddress() {
    	return $this->address;
    }
    function setAddress($address) {
    	if(is_array($address)) $address = soy2_serialize($address);
    	$this->address = $address;
    }
    function getClaimedAddress(){
    	return $this->claimedAddress;
    }
    function setClaimedAddress($claimedAddress){
    	if(is_array($claimedAddress)) $claimedAddress = soy2_serialize($claimedAddress);
    	$this->claimedAddress = $claimedAddress;
    }
    function getAddressArray(){
		return self::_address($this->address);
    }
    function getClaimedAddressArray(){
		return self::_address($this->claimedAddress);
    }
	private function _address($str){
		$addr = (is_string($str) && strlen($str)) ? soy2_unserialize($str) : array();
		if(!is_array($addr)) $addr = array();
		foreach(array("name", "zipCode", "area", "address1", "address2", "address3") as $l){
			if(!isset($addr[$l])) $addr[$l] = "";
		}
		return $addr;
	}

    function getAttributes() {
    	return $this->attributes;
    }
    function setAttributes($attributes) {
    	if(is_array($attributes)) $attributes = soy2_serialize($attributes);
		if(is_string($attributes) && $attributes == "Array") $attributes = null;
    	$this->attributes = $attributes;
    }
    function getAttributeList(){
    	if(is_array($this->attributes) && count($this->attributes) === 0) return array();
    	$res = soy2_unserialize($this->attributes);
		if(!is_array($res)) return array();

		//表記名を変更する
		$replacements = SOYShopPlugin::invoke("soyshop.order.module", array(
			"mode" => "replace",
			"moduleIds" => array_keys($res)
		))->getReplacements();

		if(count($replacements)){
			foreach($replacements as $moduleId => $new){
				if(!isset($res[$moduleId]) || !isset($res[$moduleId]["value"])) continue;
				$res[$moduleId]["value"] = $new;
			}
		}

		return $res;
    }
    function getAttribute($key) {
    	$attributes = $this->getAttributeList();
    	if(array_key_exists($key, $attributes)){
	    	return $attributes[$key];
    	}else{
    		return null;
    	}
    }
    function setAttribute($key,$value){
    	$attributes = $this->getAttributeList();
    	$attributes[$key] = $value;
    	$this->setAttributes($attributes);
    }
    function setMailStatus($status){
		if(is_array($status)) $status = soy2_serialize($status);
    	$this->mailStatus = $status;
    }
    function getMailStatus(){
    	return $this->mailStatus;
    }
    function getMailStatusList(){
    	if(empty($this->mailStatus)) return array();
    	$status = @soy2_unserialize($this->mailStatus);
    	return (is_array($status)) ? $status : array();
    }
    function getMailStatusByType($type){
    	$status = soy2_unserialize($this->mailStatus);
    	return (isset($status[$type])) ? $status[$type] : null;
    }
    function setMailStatusByType($type, $value){
    	$array = $this->getMailStatusList();
    	$array[$type] = $value;
    	$this->setMailStatus($array);
    }
    function getTrackingNumber() {
    	return $this->trackingNumber;
    }
    function setTrackingNumber($trackingNumber) {
    	$this->trackingNumber = $trackingNumber;
    }

    /* util */
    /**
     * マイページで表示するかどうか
     * @return boolean
     */
    function isOrderDisplay(){

		/*
		 * 仮登録以外は見せる
		 */
		switch( $this->getStatus() ){
			case self::ORDER_STATUS_REGISTERED :
			case self::ORDER_STATUS_RECEIVED :
			case self::ORDER_STATUS_SENDED :
			case self::ORDER_STATUS_CANCELED :
				$order = true;
				break;
			case self::ORDER_STATUS_INTERIM :
			default:
				$order = false;
		}

		/*
		 * 入金エラーや返金済みも見せる
		 */
		switch( $this->getPaymentStatus() ){
			case self::PAYMENT_STATUS_WAIT :
			case self::PAYMENT_STATUS_CONFIRMED :
			case self::PAYMENT_STATUS_DIRECT :
			case self::PAYMENT_STATUS_ERROR :
			case self::PAYMENT_STATUS_REFUNDED :
				$payment = true;
				break;
			default:
				$payment = false;
		}

		return ( $order && $payment ) ;
    }

    public static function getMailTypes(){
    	return array(
    		self::SENDMAIL_TYPE_ORDER,
    		self::SENDMAIL_TYPE_CONFIRM,
    		self::SENDMAIL_TYPE_PAYMENT,
    		self::SENDMAIL_TYPE_DELIVERY,
    		self::SENDMAIL_TYPE_OTHER
    	);
    }
}
