<?php
/**
 * @table soyshop_orders
 */
class SOYShop_ItemOrder {

	const IS_CONFIRM = 1;
	const NO_CONFIRM = 0;

	const STATUS_NONE = 0;	//何もない
	const FLAG_NONE = 0;	//何もない

	/**
	 * @id
	 */
    private $id;

    /**
     * @column order_id
     */
    private $orderId;

    /**
     * @column item_id
     */
    private $itemId;

    /**
     * @column item_count
     */
    private $itemCount;

    /**
     * @column item_price
     */
    private $itemPrice;

    /**
     * @column total_price
     */
    private $totalPrice;

    /**
     * @column item_name
     */
    private $itemName;
	private $status = 0;	//商品毎に何らかの状態を保持する
	private $flag = 0;		//使いみちはstatusと同じ

    private $cdate;

    /**
     * @column is_sended
     */
    private $isSended = 0;

    private $attributes;

    /**
     * @column is_addition
     */
    private $isAddition;

	/**
	 * @column is_confirm
	 */
	private $isConfirm = 0;

	/**
	 * @column display_order
	 */
	private $displayOrder = 0;

    function getId() {
    	return $this->id;
    }
    function setId($id) {
    	$this->id = $id;
    }
    function getOrderId() {
    	return $this->orderId;
    }
    function setOrderId($orderId) {
    	$this->orderId = $orderId;
    }
    function getItemId() {
    	return $this->itemId;
    }
    function setItemId($itemId) {
    	$this->itemId = $itemId;
    }
    function getItemCount() {
    	return $this->itemCount;
    }
    function setItemCount($itemCount) {
    	$this->itemCount = $itemCount;
    }
    function getItemPrice() {
    	return $this->itemPrice;
    }
    function setItemPrice($itemPrice) {
    	$this->itemPrice = $itemPrice;
    }
    function getTotalPrice() {
    	return $this->totalPrice;
    }
    function setTotalPrice($totalPrice) {
    	$this->totalPrice = $totalPrice;
    }
    function getItemName() {
    	return $this->itemName;
    }
    function setItemName($itemName) {
    	$this->itemName = $itemName;
    }
	function getStatus(){
		return $this->status;
	}
	function setStatus($status){
		$this->status = $status;
	}
	function getFlag(){
		return $this->flag;
	}
	function setFlag($flag){
		$this->flag = $flag;
	}
    function getCdate() {
    	if(!$this->cdate) $this->cdate = time();
    	return $this->cdate;
    }
    function setCdate($cdate) {
    	$this->cdate = $cdate;
    }

    function getIsSended() {
    	return $this->isSended;
    }
    function setIsSended($isSended) {
    	$this->isSended = $isSended;
    }

    function isSended(){
    	return (boolean)$this->isSended;
    }

    function getAttributes() {
    	return $this->attributes;
    }
    function setAttributes($attributes) {
    	if(is_array($attributes)) $attributes = soy2_serialize($attributes);
    	$this->attributes = $attributes;
    }
    function getAttributeList(){
		$res = soy2_unserialize($this->attributes);
    	return (is_array($res)) ? $res : array();
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

	function getDisplayOrder(){
		return $this->displayOrder;
	}
	function setDisplayOrder($displayOrder){
		$this->displayOrder = $displayOrder;
	}

    function getIsAddition(){
    	return $this->isAddition;
    }
    function setIsAddition($isAddition){
    	$this->isAddition = $isAddition;
    }

	function getIsConfirm(){
		return $this->isConfirm;
	}
	function setIsConfirm($isConfirm){
		$this->isConfirm = $isConfirm;
	}

    /** 便利なメソッド **/
    //多言語化プラグインを考慮した商品名の取得
	function getOpenItemName(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		if(!defined("SOYSHOP_MAIL_LANGUAGE")) define("SOYSHOP_MAIL_LANGUAGE", SOYSHOP_PUBLISH_LANGUAGE);

		if(SOYSHOP_MAIL_LANGUAGE != "jp"){
			try{
				return $dao->get($this->itemId, "item_name_" . SOYSHOP_MAIL_LANGUAGE)->getValue();
			}catch(Exception $e){
				return null;
			}
		}else{
			return $this->itemName;
		}
	}

	//管理画面で商品名を出力する時に便利な関数
	function getItemNameOnAdmin(){
		if(!self::_isConvertParentNameConfig()) return $this->itemName;

		$parentId = soyshop_get_item_object($this->itemId)->getType();
		if(!is_numeric($parentId)) return $this->itemName;

		return soyshop_get_item_object($parentId)->getName();
	}

	private function _isConvertParentNameConfig(){
		static $cnf;
		if(is_null($cnf)) {
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$cnf = ((int)SOYShop_ShopConfig::load()->getChangeParentItemNameOnAdmin() === 1);
		}
		return $cnf;
	}

	public static function getStatusList(){
		static $list;
		if(is_null($list)){
			$list[self::STATUS_NONE] = "";

			//拡張ポイント
			SOYShopPlugin::load("soyshop.itemorder.status");
			$adds = SOYShopPlugin::invoke("soyshop.itemorder.status")->getList();

			if(is_array($adds) && count($adds)){
				foreach($adds as $add){
					if(!is_array($add) || !count($add)) continue;
					foreach($add as $key => $label){
						if(isset($add[$key])) $list[$key] = $label;
					}
				}
			}

			ksort($list);
		}
		return $list;
	}

	public static function getStatusText($status){
		$statusList = self::getStatusList();
		if(!isset($statusList[$status])) $status = self::STATUS_NONE;
		return $statusList[$status];
	}

	public static function getFlagList(){
		static $list;
		if(is_null($list)){
			$list[self::FLAG_NONE] = "";

			//拡張ポイント
			SOYShopPlugin::load("soyshop.itemorder.flag");
			$adds = SOYShopPlugin::invoke("soyshop.itemorder.flag")->getList();

			if(is_array($adds) && count($adds)){
				foreach($adds as $add){
					if(!is_array($add) || !count($add)) continue;
					foreach($add as $key => $label){
						if(isset($add[$key])) $list[$key] = $label;
					}
				}
			}

			ksort($list);
		}
		return $list;
	}

	public static function getFlagText($flag){
		$flagList = self::getFlagList();
		if(!isset($flagList[$flag])) $flagList = self::FLAG_NONE;
		return $flagList[$flag];
	}

    /**
     * テーブル名を取得
     */
    public static function getTableName(){
    	return "soyshop_orders";
    }
}
