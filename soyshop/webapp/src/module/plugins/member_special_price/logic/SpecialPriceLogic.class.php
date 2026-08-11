<?php

class SpecialPriceLogic extends SOY2LogicBase{
	
	function __construct(){}

	/**
	 * @param SOYShop_Item
	 * @return int
	 */
	function getSellingPrice(SOYShop_Item $item){
		$price = self::getSpecialPrice($item);
		return (isset($price) && is_numeric($price)) ? (int)$price : $item->getSellingPrice();
	}

	/**
	 * @param SOYShop_Item
	 * @return int|null
	 */
	function getSpecialPrice(SOYShop_Item $item){
		//ログインしている顧客の情報を取得する
		SOY2::import("logic.mypage.MyPageLogic");
		$mypage = MyPageLogic::getMyPage();
		$userId = $mypage->getUserId();
		if(is_null($userId)) return null;
		
		//顧客
		$user = $mypage->getUser();
		
		//顧客属性から今回使用するhash値を調べる
		$hash = "";
		
		SOY2::import("module.plugins.member_special_price.util.MemberSpecialPriceUtil");
		$cfgs = MemberSpecialPriceUtil::getConfig();
		foreach($cfgs as $cfg){
			switch((int)$cfg["attribute"]){
				case 1:
					$attrVal = $user->getAttribute1();
					break;
				case 2:
					$attrVal = $user->getAttribute2();
					break;
				case 3:
					$attrVal = $user->getAttribute3();
					break;
				case 4:	//ユーザーカスタムフィールド
					$fieldId = (isset($cfg["field_id"])) ? $cfg["field_id"] : "";
					if(isset($_POST["user_customfield"])){
						$attrVal = "";
						if(isset($_POST["user_customfield"][$fieldId])){
							$attrVal = $_POST["user_customfield"][$fieldId];
						}
					}else{
						$attrVal = soyshop_get_user_attribute_value($userId, $fieldId, "string");
					}
					break;
			}
			
			//属性値との完全一致が条件
			if($attrVal === $cfg["label"]) {
				$hash = $cfg["hash"];
				break;
			}
		}
			
		//価格の設定状況を調べる
		$isSale = SOY2Logic::createInstance("module.plugins.common_sale_period.logic.PriceLogic")->checkOnSale($item);
		return self::getPriceByItemIdAndHash((int)$item->getId(), $hash, $isSale);
	}

	/**
	 * @param int
	 * @param string
	 * @param string
	 * @return int|null
	 */
	function getPriceByItemIdAndHash(int $itemId, string $hash, bool $isSale=false){
		$fieldId = "np_" . $hash;
		if($isSale) $fieldId .= "_sale";
		$v = soyshop_get_item_attribute_object($itemId, $fieldId)->getValue();
		return (is_numeric($v)) ? (int)$v : null;
	}
}
