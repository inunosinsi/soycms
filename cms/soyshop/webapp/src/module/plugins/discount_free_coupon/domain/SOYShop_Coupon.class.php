<?php
/**
 * @table soyshop_coupon
 */
class SOYShop_Coupon {

	const TYPE_PRICE = 0;		//値引き額
	const TYPE_PERCENT = 1;		//値引き率
	const TYPE_DELIVERY = 2;	//送料無料

	const NOT_DELETED = 0;
	const DELETED = 1;

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column coupon_code
	 */
	private $couponCode;

	/**
	 * @column coupon_type
	 */
	private $couponType;

	/**
	 * @column name
	 */
	private $name;

	/**
	 * @column category_id
	 */
	private $categoryId;

	/**
	 * @column discount
	 */
	private $discount;

	/**
	 * @column discount_percent
	 */
	private $discountPercent;

	/**
     * @column is_free_delivery
	 */
	private $isFreeDelivery = 0;

	/**
	 * @column count
	 */
	private $count;

	/**
	 * @column memo
	 */
	private $memo;

	/**
	 * @column price_limit_min
	 */
	private $priceLimitMin;

	/**
	 * @column price_limit_max
	 */
	private $priceLimitMax;

	/**
	 * @column time_limit_start
	 */
	private $timeLimitStart;

	/**
	 * @column time_limit_end
	 */
	private $timeLimitEnd;

	/**
	 * @column is_delete
	 */
	private $isDelete;

	/**
	 * @column create_date
	 */
	private $createDate;

	/**
	 * @column update_date
	 */
	private $updateDate;

	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id = $id;
	}

	function getCouponCode(){
		return $this->couponCode;
	}
	function setCouponCode($couponCode){
		$this->couponCode = $couponCode;
	}

	function getCouponType(){
		return $this->couponType;
	}
	function setCouponType($couponType){
		$this->couponType = $couponType;
	}

	function getName(){
		return $this->name;
	}
	function setName($name){
		$this->name = $name;
	}

	function getCategoryId(){
		return $this->categoryId;
	}
	function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
	}

	function getDiscount(){
		return $this->discount;
	}
	function setDiscount($discount){
		$this->discount = $discount;
	}

	function getDiscountPercent(){
		return $this->discountPercent;
	}
	function setDiscountPercent($discountPercent){
		$this->discountPercent = $discountPercent;
	}

	function getIsFreeDelivery(){
		return $this->isFreeDelivery;
	}
	function setIsFreeDelivery($isFreeDelivery){
		$this->isFreeDelivery = $isFreeDelivery;
	}

	function getCount(){
		return $this->count;
	}
	function setCount($count){
		$this->count = $count;
	}

	function getMemo(){
		return $this->memo;
	}
	function setMemo($memo){
		$this->memo = $memo;
	}

	function getPriceLimitMin(){
		return $this->priceLimitMin;
	}
	function setPriceLimitMin($priceLimitMin){
		$this->priceLimitMin = $priceLimitMin;
	}

	function getPriceLimitMax(){
		return $this->priceLimitMax;
	}
	function setPriceLimitMax($priceLimitMax){
		$this->priceLimitMax = $priceLimitMax;
	}

	function getTimeLimitStart(){
		return $this->timeLimitStart;
	}
	function setTimeLimitStart($timeLimitStart){
		$this->timeLimitStart = $timeLimitStart;
	}

	function getTimeLimitEnd(){
		return $this->timeLimitEnd;
	}
	function setTimeLimitEnd($timeLimitEnd){
		$this->timeLimitEnd = $timeLimitEnd;
	}

	function getIsDelete(){
		return $this->isDelete;
	}
	function setIsDelete($isDelete){
		$this->isDelete = $isDelete;
	}

	function getCreateDate(){
		return $this->createDate;
	}
	function setCreateDate($createDate){
		$this->createDate = $createDate;
	}

	function getUpdateDate(){
		return $this->updateDate;
	}
	function setUpdateDate($UpdateDate){
		$this->updateDate = $UpdateDate;
	}

	public static function getCouponTypeList(){
		return array(
			self::TYPE_PRICE => "値引き額",
			self::TYPE_PERCENT => "値引き率",
			self::TYPE_DELIVERY => "送料無料"
		);
	}
}
