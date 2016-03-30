<?php

class CouponListComponent extends HTMLList{
	
	private $dao;

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
		
		$this->addLabel("count", array(
			"text" => ($entity->getCount() > 900000) ? "無制限" : number_format($entity->getCount()) . " 回"
		));
		
		//使用された回数を表示する
		try{
			$usedCount = $this->dao->countByCouponId($entity->getId());
		}catch(Exception $e){
			$usedCount = 0;
		}
		
		$this->addLabel("used_count", array(
			"text" => number_format($usedCount)
		));
		
		//クーポンタイプが値引き額の場合は値引きの金額を表示する
		if($entity->getCouponType() == SOYShop_Coupon::TYPE_PRICE){
			$couponValue = number_format($entity->getDiscount()) . " 円";
		}else{
			$couponValue = $entity->getDiscountPercent() . " ％";
		}
		
		$this->addLabel("discount", array(
			"text" => $couponValue
		));
		
		$this->addLabel("price_limit", array(
			"text" => $this->convertPrice($entity->getPriceLimitMin()) . " ～ " . $this->convertPrice($entity->getPriceLimitMax(), true)
		));
		
		$this->addLabel("time_limit", array(
			"text" => $this->convertDate($entity->getTimeLimitStart(), true) . " ～ " . $this->convertDate($entity->getTimeLimitEnd(), true)
		));
		
		$this->addLink("detail_link", array(
			"link" => "javascript:void(0)",
			"onclick" => "$(\".coupon_detail_" . $entity->getId() . "\").toggle();"
		));
		
		$this->addLabel("memo", array(
			"text" => $entity->getMemo()
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
			"value" => $this->convertDate($entity->getTimeLimitStart())
		));
		
		$this->addInput("input_time_limit_end", array(
			"name" => "Edit[timeLimitEnd]",
			"value" => $this->convertDate($entity->getTimeLimitEnd())
		));
	}
	
	function convertPrice($value, $max = false){
		if(isset($value) && (int)$value > 0){
			return number_format($value) . "　円";
		}else{
			return ($max === true) ? "無制限" : 0 . "　円";
		}
	}
	
	function convertDate($value, $flag = false){
		if($value == 2147483647){
			return ($flag) ? "無制限" : "";
		}else{
			return date("Y-m-d", $value);
		}
	}
	
	function setDao($dao){
		$this->dao = $dao;
	}
}
?>