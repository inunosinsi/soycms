<?php
if(!class_exists("SOYShopPluginUtil")) SOY2::import("util.SOYShopPluginUtil");

/**
 * @table soyshop_item
 */
class SOYShop_Item {

    const TYPE_SINGLE = "single";
    const TYPE_GROUP = "group";
    const TYPE_CHILD = "child";
    const TYPE_DOWNLOAD = "download";
	const TYPE_DOWNLOAD_GROUP = "dlgroup";	//ダウンロードグループ
	const TYPE_DOWNLOAD_CHILD = "dlchild";	//ダウンロードグループ

	const UNIT = "個";	//単位


    static public function getItemTypes(){
        return array(
            self::TYPE_SINGLE,
            self::TYPE_GROUP,
            self::TYPE_DOWNLOAD,
			self::TYPE_DOWNLOAD_GROUP
        );
    }

    const NO_OPEN = 0;
    const IS_OPEN = 1;

    const NO_SALE = 0;
    const IS_SALE = 1;

    const PERIOD_START = 0;
    const PERIOD_END = 2147483647;

    const NO_DISABLED = 0;
    const IS_DISABLED = 1;    //削除

    public static function getTableName(){
        return "soyshop_item";
    }

    /**
     * @id
     */
    private $id;

    /**
     * @column item_name
     */
    private $name;

	/**
	 * @column item_subtitle
	 */
	private $subtitle;

    /**
     * @column item_alias
     */
    private $alias;

    /**
     * @column item_code
     */
    private $code;

    /**
     * @column item_sale_flag
     */
    private $saleFlag = false;

    /**
     * 通常価格
     * @column item_price
     */
    private $price;

	/**
	 * 仕入値
	 * @column item_purchase_price
	 */
	private $purchasePrice = 0;

    /**
     * セール価格
     * @column item_sale_price
     */
    private $salePrice;

    /**
     * 販売価格
     * @column item_selling_price
     */
    private $sellingPrice;

    /**
     * @column item_stock
     */
    private $stock = 100;

	/**
	 * @column item_unit
	 */
	private $unit = self::UNIT;

    /**
     * @column item_config
     */
    private $config;

    /**
     * @no_persistent
     */
    private $_config;

    /**
     * @column item_category
     */
    private $category;

    /**
     * @column item_type
     */
    private $type = "single";

    /**
     * @column create_date
     */
    private $createDate;

    /**
     * @column update_date
     */
    private $updateDate;

    /**
     * @column order_period_start
     */
    private $orderPeriodStart;

    /**
     * @column order_period_end
     */
    private $orderPeriodEnd;

    /**
     * @column open_period_start
     */
    private $openPeriodStart;

    /**
     * @column open_period_end
     */
    private $openPeriodEnd;

    /**
     * @column detail_page_id
     */
    private $detailPageId;

    /**
     * @column item_is_open
     */
    private $isOpen;

    /**
     * @column is_disabled
     */
    private $isDisabled = 0;

    function getId() {
        return $this->id;
    }
    function setId($id) {
        $this->id = $id;
    }
    function getName() {
        return $this->name;
    }
    function setName($name) {
        $this->name = $name;
    }
	function getSubtitle(){
		return $this->subtitle;
	}
	function setSubtitle($subtitle){
		$this->subtitle = $subtitle;
	}
    function getCode() {
		if(is_null($this->code)) $this->code = soyshop_dummy_item_code();
		return $this->code;
    }
    function setCode($code) {
        $this->code = $code;
    }
    function getPrice() {
        return (int)$this->price;
    }
    function setPrice($price) {
        $this->price = $price;
    }
	function getPurchasePrice(){
		return (int)$this->purchasePrice;
	}
	function setPurchasePrice($purchasePrice){
		$this->purchasePrice = $purchasePrice;
	}
    function getStock() {
		if(!SOYShopPluginUtil::checkIsActive("reserve_calendar")) return (int)$this->stock;

		//予約カレンダーモード
		$unseat = self::_scheduleDao()->getScheduleUnseatCountByItemId($this->getId()) - $this->getOrderCount();
		return ($unseat >= 0) ? $unseat : 0;
    }
    function setStock($stock) {
        $this->stock = $stock;
    }
	function getUnit(){
		return $this->unit;
	}
	function setUnit($unit){
		$this->unit = $unit;
	}
    function getConfig() {
        return $this->config;
    }
    function setConfig($config) {
        if(is_array($config)){
            $this->_config = $config;
            $config = soy2_serialize($config);
        }
        $this->config = $config;
    }

    /**
     * unserilze config object
     */
    function getConfigObject(){
        if(!$this->_config){
            $obj = soy2_unserialize($this->getConfig());
            $this->_config = $obj;
        }

        return $this->_config;
    }

    function getAttribute($key){
        $array = $this->getConfigObject();
        return (isset($array[$key])) ? $array[$key] : null;
    }

    function setAttribute($key,$value){
        $array = $this->getConfigObject();
        $array[$key] = $value;

        $this->setConfig($array);
    }

    function getCategory() {
        return $this->category;
    }
    function setCategory($category) {
        $this->category = $category;
    }
    function getType() {
        return $this->type;
    }
    function setType($type) {
        $this->type = $type;
    }
    function getCreateDate() {
        return $this->createDate;
    }
    function setCreateDate($createDate) {
        $this->createDate = $createDate;
    }
    function getUpdateDate() {
        return $this->updateDate;
    }
    function setUpdateDate($updateDate) {
        $this->updateDate = $updateDate;
    }

    function getOrderPeriodStart() {
        return (!is_null($this->orderPeriodStart)) ? $this->orderPeriodStart : self::PERIOD_START;
    }
    function setOrderPeriodStart($orderPeriodStart) {
        $this->orderPeriodStart = $orderPeriodStart;
    }
    function getOrderPeriodEnd() {
        return (!is_null($this->orderPeriodEnd)) ? $this->orderPeriodEnd : self::PERIOD_END;
    }
    function setOrderPeriodEnd($orderPeriodEnd) {
        $this->orderPeriodEnd = $orderPeriodEnd;
    }

    function getOpenPeriodStart() {
        return (!is_null($this->openPeriodStart)) ? $this->openPeriodStart : self::PERIOD_START;
    }
    function setOpenPeriodStart($openPeriodStart) {
        $this->openPeriodStart = $openPeriodStart;
    }
    function getOpenPeriodEnd() {
        return (!is_null($this->openPeriodEnd)) ? $this->openPeriodEnd : self::PERIOD_END;
    }
    function setOpenPeriodEnd($openPeriodEnd) {
        $this->openPeriodEnd = $openPeriodEnd;
    }

    function getIsDisabled() {
        return $this->isDisabled;
    }
    function setIsDisabled($isDisabled) {
        $this->isDisabled = $isDisabled;
    }

    function getDetailPageId() {
        return $this->detailPageId;
    }
    function setDetailPageId($detailPageId) {
        $this->detailPageId = $detailPageId;
    }

    function getAlias() {
        return $this->alias;
    }
    function setAlias($alias) {
        $this->alias = $alias;
    }

    /* 以下 便利メソッド */

    //多言語化プラグインを考慮した商品名の取得
    function getOpenItemName(){
        static $attrDao;
        if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");
        if(!defined("SOYSHOP_MAIL_LANGUAGE")) define("SOYSHOP_MAIL_LANGUAGE", SOYSHOP_PUBLISH_LANGUAGE);

        if(SOYSHOP_MAIL_LANGUAGE != "jp"){
            if(is_null($attrDao)) $attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
            try{
                $name = $attrDao->get($this->id, "item_name_" . SOYSHOP_MAIL_LANGUAGE)->getValue();
                if(strlen($name)) return $name;
            }catch(Exception $e){
                //
            }
        }
        return $this->name;
    }

	//注文数
	function getOrderCount(){
		//予約カレンダーの場合
		if(SOYShopPluginUtil::checkIsActive("reserve_calendar")){
			return self::_reserveDao()->getReservedCountByItemId($this->getId());
		//通常
		}else{
			try{
				return self::_itemOrderDao()->countByItemId($this->getId());
			}catch(Exception $e){
				return 0;
			}
		}
	}

	function getCodeOnAdmin(){
		if(!self::_isConvertParentNameConfig()) return $this->code;

		$parentId = soyshop_get_item_object($this->id)->getType();
		if(!is_numeric($parentId)) return $this->code;

		return soyshop_get_item_object($parentId)->getCode();
	}

	private function _isConvertParentNameConfig(){
		static $cnf;
		if(is_null($cnf)) {
			SOY2::import("domain.config.SOYShop_ShopConfig");
			$cnf = ((int)SOYShop_ShopConfig::load()->getChangeParentItemNameOnAdmin() === 1);
		}
		return $cnf;
	}

    function getAttachmentsPath(){
        $dir = SOYSHOP_SITE_DIRECTORY . "files/" . $this->getCode() . "/";
        if(!file_exists($dir)){
            mkdir($dir);
        }

        return $dir;
    }

    function getAttachmentsUrl(){
		$dir = soyshop_get_site_path() . "files/" . $this->getCode() . "/";
		//@ToDo ドメインをhttp://***.***.***.***/サイトID/に当てた時の対処を考える
		//if(strpos($dir, "/" . SOYSHOP_ID . "/") !== false) $dir = str_replace("/" . SOYSHOP_ID . "/", "/", $dir);
        return $dir;
    }

    /**
     * 添付ファイルを取得
     */
    function getAttachments(){
        $dir = $this->getAttachmentsPath();
        $url = $this->getAttachmentsUrl();
        $files = scandir($dir);
        $res = array();

        foreach($files as $file){
            if($file[0] == ".") continue;
            $res[] = $url . $file;
        }

        return $res;
    }

    function getIsOpen() {
        return $this->isOpen;
    }
    function setIsOpen($isOpen) {
        $this->isOpen = $isOpen;
    }

    /**
     * 公開しているかどうか
     *
     * @return boolean
     */
    function isPublished(){
        if($this->isOpen > 0 &&
            $this->getOpenPeriodStart() <= SOY2_NOW &&
            $this->getOpenPeriodEnd() >= SOY2_NOW
        ){
        	return true;
        }

        return false;
    }

    /**
     * 公開状態の文言取得
     */
    function getPublishText(){
        if($this->isOpen < 1){
            return MessageManager::get("STATUS_CLOSED");
        }

        if($this->getOpenPeriodStart() > SOY2_NOW ||
            $this->getOpenPeriodEnd() < SOY2_NOW){
        return MessageManager::get("STATUS_OUT_OF_DATE");
        }

        return MessageManager::get("STATUS_OPEN");
    }

    /* 在庫 */

    /**
     * 公開側での在庫数
     */
    function getOpenStock(){
        if($this->isPublished()){
            return $this->getStock();
        }

        return 0;
    }

    /* セール周り */

    function isOnSale(){
        return (boolean)$this->saleFlag;
    }

    function getSaleFlag() {
        return (int)$this->saleFlag;
    }
    function setSaleFlag($saleFlag) {
        $this->saleFlag = $saleFlag;
    }
    function getSalePrice() {
        if(empty($this->salePrice))return $this->getPrice();
        return $this->salePrice;
    }
    function setSalePrice($salePrice) {
        $this->salePrice = $salePrice;
    }
    function getSellingPrice() {
        if($this->isOnSale()){
            return $this->getSalePrice();
        }else{
            return $this->getPrice();
        }
    }
    function setSellingPrice($sellingPrice) {
        $this->sellingPrice = $sellingPrice;
    }

    /**
     * 子商品かどうか
     * @return boolean
     */
    function isChild(){
        return (is_numeric($this->getType()));
    }

    /**
     * 注文可能か調べる
     */
    function checkAcceptOrder(){
        return ($this->orderPeriodStart < time() && $this->orderPeriodEnd > time());
    }

    /**
     * 追加可能か
     */
    function isOrderable(){
        if($this->getType() == self::TYPE_GROUP || $this->getType() == self::TYPE_DOWNLOAD_GROUP){
            return false;
        }

        return true;
    }

	/** DAO **/

	private function _itemOrderDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		return $dao;
	}

	private function _reserveDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ReserveDAO");
			$dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ReserveDAO");
		}
		return $dao;
	}

	private function _scheduleDao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.reserve_calendar.domain.SOYShopReserveCalendar_ScheduleDAO");
			$dao = SOY2DAOFactory::create("SOYShopReserveCalendar_ScheduleDAO");
		}
		return $dao;
	}
}
