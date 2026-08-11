<?php

class DiscountBulkBuyingLogic extends SOY2LogicBase {

	private $items;	//カートの中に入っている商品

	function __construct(){
		SOY2::import("module.plugins.discount_bulk_buying_each_category.util.DiscountBulkBuyingUtil");
	}

	function checkIsApplyDiscount(){
		$cnds = self::_meetedConditions();
		return (is_array($cnds) && count($cnds));
	}

	//カテゴリ毎に割引額を計算する
	function getDiscountPrices(){
		$cnds = self::_meetedConditions();
		if(!count($cnds)) return array();

		$candidates = self::_candidates();
		if(!count($candidates)) return array();

		$categoryConditions = self::_getCategoryConditions();

		$list = array();	//割引の適用の格納
		foreach($cnds as $categoryId => $on){
			if(!isset($candidates[$categoryId]) || !isset($categoryConditions[$categoryId])) continue;

			$categoryCondition = $categoryConditions[$categoryId];

			//該当商品の中でどれ程の個数を割引対象にするか？
			$isApply = (isset($categoryCondition["apply"]) && is_numeric($categoryCondition["apply"]) && (int)$categoryCondition["apply"] > 0);
			if($isApply && $categoryCondition["type"] == DiscountBulkBuyingUtil::TYPE_PERCENT){
				$discount = 0;
				//価格の安い方から順に割引計算を行う
				$candidate = $candidates[$categoryId];
				if(!count($candidate)) break;

				$priceList = array();
				foreach($candidate as $v){
					$priceList[] = $v["price"];
				}
				sort($priceList);

				//上からapply分だけ取得
				$total = 0;
				for($i = 0; $i < $categoryCondition["apply"]; $i++){
					if(!isset($priceList[$i])) continue;
					$total += $priceList[$i];
				}

				$discount = (int)($total * $categoryCondition["discount"]["percent"] / 100);
			}else{	//通常
				switch($categoryCondition["type"]){
					case DiscountBulkBuyingUtil::TYPE_AMOUNT:	//割引額
						$discount = $categoryCondition["discount"]["amount"];
						break;
					case DiscountBulkBuyingUtil::TYPE_PERCENT:	//割引率
						$candidate = $candidates[$categoryId];
						$total = 0;
						foreach($candidate as $v){
							$total += $v["price"];
						}
						$discount = (int)($total * $categoryCondition["discount"]["percent"] / 100);
						break;
				}
			}

			$list[$categoryId] = $discount;
		}

		return $list;
	}

	//どのカテゴリが条件を満たしたか？
	private function _meetedConditions(){
		static $cnds;
		if(isset($cnds) && is_array($cnds)) return $cnds;
		$cnds = array();

		$candidates = self::_candidates();
		if(!count($candidates)) return false;

		$categoryConditions = self::_getCategoryConditions();

		//indexに割引設定のカテゴリIDが含まれている
		$categoryIds = array_keys($categoryConditions);

		foreach($categoryIds as $categoryId){
			if(!isset($candidates[$categoryId])) continue;
			$candidate = $candidates[$categoryId];

			//条件を満たした商品が何個あるか？
			if(count($candidate)){
				$categoryCondition = $categoryConditions[$categoryId];	//設定がある

				$totalPrice = 0;
				$totalCount = 0;
				foreach($candidate as $cand){
					$totalCount++;
					$totalPrice += $cand["price"];
				}

				//条件を満たしたか？
				if($categoryCondition["combination"] == DiscountBulkBuyingUtil::COMBINATION_ALL){	//合計金額と合計個数の両方で判定
					if($totalPrice >= $categoryCondition["total"] && $totalCount >= $categoryCondition["amount"]){
						$cnds[$categoryId] = 1;
					}
				}else{	//合計金額と合計個数のどちらかで判定
					if($totalPrice >= $categoryCondition["total"] || $totalCount >= $categoryCondition["amount"]){
						$cnds[$categoryId] = 1;
					}
				}
			}
		}

		return $cnds;
	}

	//まとめ買い割引の対象となる商品
	private function _candidates(){
		static $candidates;
		if(isset($candidates) && is_array($candidates)) return $candidates;

		$candidates = array();
		if(!count($this->items)) return $candidates;

		$categoryConditions = self::_getCategoryConditions();
		if(!count($categoryConditions)) return $candidates;	//条件設定が無い場合も調べない

		//indexに割引設定のカテゴリIDが含まれている
		$categoryIds = array_keys($categoryConditions);

		foreach($this->items as $itemOrder){
			$categoryId = soyshop_get_item_object($itemOrder->getItemId())->getCategory();
			if(in_array($categoryId, $categoryIds)){
				$categoryCondition = $categoryConditions[$categoryId];	//設定がある

				//カテゴリ毎に設けた商品の最低価格以上
				if($itemOrder->getItemPrice() >= $categoryCondition["price"]){
					for($i = 0; $i < $itemOrder->getItemCount(); $i++){
						$candidates[$categoryId][] = array("id" => (int)$itemOrder->getItemId(), "price" => $itemOrder->getItemPrice());
					}
				}
			}
		}

		//親カテゴリがあれば統合
		$parents = array();
		foreach($categoryConditions as $categoryCondition){
			if(isset($categoryCondition["parent"]) && $categoryCondition["parent"] == 0){
				$parents[] = $categoryCondition["category"];
			}
		}

		if(count($parents)){
			$map = SOYShop_DataSets::get("category.mapping", array());
			foreach($parents as $parent){
				$children = $map[$parent];
				foreach($children as $child){
					if($parent == $child || !isset($candidates[$child])) continue;
					$childCandidates = $candidates[$child];

					foreach($childCandidates as $childCandidate){
						$candidates[$parent][] = $childCandidate;
					}
					unset($candidates[$child]);
				}
			}
		}

		return $candidates;
	}

	private function _getCategoryConditions(){
		$cnds = DiscountBulkBuyingUtil::getCategoryCondition();

		// 親カテゴリを加味した場合、親カテゴリを元に子カテゴリでクローンを作る
		if(count($cnds)){
			$map = SOYShop_DataSets::get("category.mapping", array());
			$categoryIds = array_keys($cnds);
			foreach($categoryIds as $categoryId){
				$children = $map[$categoryId];
				if(count($children) > 1){	//2以上の場合
					$doAdd = false;
					foreach($children as $child){
						if($child == $categoryId) continue;
						$clone = $cnds[$categoryId];
						$clone["category"] = $child;
						$clone["parent"] = $categoryId;
						$cnds[$child] = $clone;
						$doAdd = true;
					}

					// 親カテゴリである記録
					if($doAdd){
						$cnds[$categoryId]["parent"] = 0;
					}
				}
			}
		}

		return $cnds;
	}

	function setItems($items){
		$this->items = $items;
	}
}
