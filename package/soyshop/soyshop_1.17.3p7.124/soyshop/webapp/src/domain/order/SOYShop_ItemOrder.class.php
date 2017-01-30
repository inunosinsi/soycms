<?php
/**
 * @table soyshop_orders
 */
class SOYShop_ItemOrder {

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
	 * @no_persistent
	 */
	private $itemAttributeDao;

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
    
    function getIsAddition(){
    	return $this->isAddition;
    }
    function setIsAddition($isAddition){
    	$this->isAddition = $isAddition;
    }
    
    /** 便利なメソッド **/
    //多言語化プラグインを考慮した商品名の取得
	function getOpenItemName(){
		if(!defined("SOYSHOP_MAIL_LANGUAGE"))　define("SOYSHOP_MAIL_LANGUAGE", SOYSHOP_PUBLISH_LANGUAGE);
		
		if(SOYSHOP_MAIL_LANGUAGE != "jp"){
			if(!$this->itemAttributeDao) $this->itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			try{
				return $this->itemAttributeDao->get($this->itemId, "item_name_" . SOYSHOP_MAIL_LANGUAGE)->getValue();
			}catch(Exception $e){
				return null;
			}
		}else{
			return $this->itemName;
		}
	}

    /**
     * テーブル名を取得
     */
    public static function getTableName(){
    	return "soyshop_orders";
    }
}
?>