<?php

class CouponListComponent extends HTMLList{

	private $categoryList;

	function populateItem($entity, $key, $index){

		$this->addLabel("id", array(
			"text" => $entity->getId()
		));

		$this->addLabel("coupon_code", array(
			"text" => $entity->getCouponCode()
		));

		$this->addLabel("name", array(
			"text" => $entity->getName()
		));

		$cnt = (is_numeric($entity->getCount())) ? (int)$entity->getCount() : 0;
		$this->addLabel("count", array(
			"text" => ($cnt > 900000) ? "無制限" : number_format($cnt) . " 回"
		));

		//使用された回数を表示する
		$this->addLabel("used_count", array(
			"text" => number_format(self::_getUsedCount($entity->getId()))
		));

		//クーポンタイプが値引き額の場合は値引きの金額を表示する
		$this->addLabel("discount", array(
			"text" => ($entity instanceof SOYShop_Coupon) ? self::_getDiscountAmount($entity) : 0
		));

		$this->addLabel("price_limit", array(
			"text" => self::_convertPrice($entity->getPriceLimitMin()) . " ～ " . self::_convertPrice($entity->getPriceLimitMax(), true)
		));

		$this->addLabel("time_limit", array(
			"text" => self::_convertDate($entity->getTimeLimitStart(), true) . " ～ " . self::_convertDate($entity->getTimeLimitEnd(), true)
		));

		$this->addLink("detail_link", array(
			"link" => "javascript:void(0)",
			"onclick" => "$(\".coupon_detail_" . $entity->getId() . "\").toggle();"
		));

		$this->addLabel("memo", array(
			"text" => $entity->getMemo()
		));

		$this->addModel("has_category", array(
			"visible" => count($this->categoryList)
		));

		$this->addSelect("select_category", array(
			"name" => "Edit[categoryId]",
			"options" => $this->categoryList,
			"selected" => $entity->getCategoryId()
		));

		$this->addInput("input_id", array(
			"name" => "Edit[id]",
			"value" => $entity->getId()
		));

		$this->addInput("input_name", array(
			"name" => "Edit[name]",
			"value" => $entity->getName()
		));

		$this->addCheckBox("radio_coupon_type_price", array(
			"name" => "Edit[couponType]",
			"value" => SOYShop_Coupon::TYPE_PRICE,
			"selected" => ($entity->getCouponType() != SOYShop_Coupon::TYPE_PERCENT),
			"label" => "値引き額"
		));

		$this->addCheckBox("radio_coupon_type_percent", array(
			"name" => "Edit[couponType]",
			"value" => SOYShop_Coupon::TYPE_PERCENT,
			"selected" => ($entity->getCouponType() == SOYShop_Coupon::TYPE_PERCENT),
			"label" => "値引き率"
		));

		$this->addCheckBox("radio_coupon_type_delivery", array(
			"name" => "Edit[couponType]",
			"value" => SOYShop_Coupon::TYPE_DELIVERY,
			"selected" => ($entity->getCouponType() == SOYShop_Coupon::TYPE_DELIVERY),
			"label" => "送料無料"
		));

		$this->addInput("input_count", array(
			"name" => "Edit[count]",
			"value" => ($entity->getCount() < 990000) ? $entity->getCount() : ""
		));

		$this->addInput("input_discount", array(
			"name" => "Edit[discount]",
			"value" => $entity->getDiscount()
		));

		$this->addInput("input_discout_percent", array(
			"name" => "Edit[discountPercent]",
			"value" => $entity->getDiscountPercent()
		));

		$this->addInput("input_memo", array(
			"name" => "Edit[memo]",
			"value" => $entity->getMemo()
		));

		$this->addInput("input_price_limit_min", array(
			"name" => "Edit[priceLimitMin]",
			"value" => $entity->getPriceLimitMin()
		));

		$this->addInput("input_price_limit_max", array(
			"name" => "Edit[priceLimitMax]",
			"value" => $entity->getPriceLimitMax()
		));

		$this->addInput("input_time_limit_start", array(
			"name" => "Edit[timeLimitStart]",
			"value" => self::_convertDate($entity->getTimeLimitStart())
		));

		$this->addInput("input_time_limit_end", array(
			"name" => "Edit[timeLimitEnd]",
			"value" => self::_convertDate($entity->getTimeLimitEnd())
		));

		// 下記のコードはSearchCouponLogicを追加したので不要になった
		//if(is_numeric($entity->getTimeLimitEnd()) && $entity->getTimeLimitEnd() > 0 && $entity->getTimeLimitEnd() < time()) return false;
	}

	private function _getUsedCount($id){
		if(!is_numeric($id)) return 0;
		try{
			return self::_dao()->countByCouponId($id);
		}catch(Exception $e){
			return 0;
		}
	}

	private function _getDiscountAmount(SOYShop_Coupon $coupon){
		if($coupon->getCouponType() == SOYShop_Coupon::TYPE_PRICE){
			return soy2_number_format($coupon->getDiscount()) . " 円";
		}else{
			return (is_numeric($coupon->getDiscountPercent())) ? $coupon->getDiscountPercent() . " ％" : "";
		}
	}

	private function _convertPrice($value, $max = false){
		if(is_numeric($value) && (int)$value > 0){
			return soy2_number_format($value) . "　円";
		}else{
			return ($max === true) ? "無制限" : 0 . "　円";
		}
	}

	private function _convertDate($value, $flag = false){
		if($value == 2147483647){
			return ($flag) ? "無制限" : "";
		}else{
			return (is_numeric($value)) ? date("Y-m-d", $value) : "";
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponHistoryDAO");
			$dao = SOY2DAOFactory::create("SOYShop_CouponHistoryDAO");
		}
		return $dao;
	}

	function setCategoryList($categoryList){
		$this->categoryList = $categoryList;
	}
}
