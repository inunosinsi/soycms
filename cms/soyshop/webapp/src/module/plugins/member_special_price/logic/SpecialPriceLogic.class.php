<?php

class SpecialPriceLogic extends SOY2LogicBase{
	
	private $attrDao;
	
	function __construct(){
		$this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
	}
	
	function getSellingPrice($item){
		$price = self::getSpecialPrice($item);
		
		if(isset($price) && is_numeric($price)) return $price;
		
		return $item->getSellingPrice();
	}
	
	function getSpecialPrice(SOYShop_Item $item){
		//ログインしている顧客の情報を取得する
		$mypage = MyPageLogic::getMyPage();
		$userId = $mypage->getUserId();
		if(is_null($userId)) return null;
		
		//顧客
		$user = $mypage->getUser();
		
		//顧客属性から今回使用するhash値を調べる
		$hash = null;
		
		SOY2::import("module.plugins.member_special_price.util.MemberSpecialPriceUtil");
		$configs = MemberSpecialPriceUtil::getConfig();
		foreach($configs as $conf){
			switch((int)$conf["attribute"]){
				case 1:
					$attrVal = $user->getAttribute1();
					break;
				case 2:
					$attrVal = $user->getAttribute2();
					break;
				case 3:
					$attrVal = $user->getAttribute3();
			}
			
			//属性値との完全一致が条件
			if($attrVal === $conf["label"]) {
				$hash = $conf["hash"];
				break;
			}
		}
			
		//価格の設定状況を調べる
		$isSale = SOY2Logic::createInstance("module.plugins.common_sale_period.logic.PriceLogic")->checkOnSale($item);
		return self::getPriceByItemIdAndHash($item->getId(), $hash, $isSale);
	}
	
	function getPriceByItemIdAndHash($itemId, $hash, $isSale = false){
		$fieldId = "np_" . $hash;
		if($isSale) $fieldId .= "_sale";
		
		try{
			return (int)$this->attrDao->get($itemId, $fieldId)->getValue();
		}catch(Exception $e){
			return null;
		}
	}
}
?>